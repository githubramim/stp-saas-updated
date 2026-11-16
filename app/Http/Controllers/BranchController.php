<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use App\Support\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    /**
     * Branch Management (BRD module #21). Institutes can create unlimited
     * branches, switch between them from the top bar, assign staff and
     * students, and see a branch-wise P&L.
     */
    public function index(Request $request)
    {
        $this->guard();

        $instituteId = $this->instituteId();

        // Branch management must see every branch's people, so the branch
        // global scope is bypassed here on purpose.
        $branches = Branch::where('institute_id', $instituteId)
            ->withCount(['users'])
            ->orderBy('id')
            ->get();

        $studentCounts = Student::withoutGlobalScope('branch')
            ->where('institute_id', $instituteId)
            ->selectRaw('branch_id, count(*) as total')
            ->groupBy('branch_id')
            ->pluck('total', 'branch_id');

        $branches->each(function (Branch $branch) use ($studentCounts) {
            $branch->students_count = (int) ($studentCounts[$branch->id] ?? 0);
        });

        [$from, $to, $period] = $this->resolvePeriod($request);

        // Branch-wise P&L: income/expense/net per branch for the period.
        $plRows = Transaction::withoutGlobalScope('branch')
            ->where('institute_id', $instituteId)
            ->when($period !== 'all', fn ($q) => $q->whereBetween('transaction_date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]))
            ->selectRaw('branch_id, type, SUM(amount) as total')
            ->groupBy('branch_id', 'type')
            ->get();

        $pl = $branches->mapWithKeys(fn (Branch $branch) => [$branch->id => [
            'income' => 0.0,
            'expense' => 0.0,
            'net' => 0.0,
        ]]);

        $unassigned = ['income' => 0.0, 'expense' => 0.0, 'net' => 0.0];

        foreach ($plRows as $row) {
            $target = $row->branch_id && isset($pl[$row->branch_id]) ? $row->branch_id : null;
            $bucket = $target ? $pl[$target] : $unassigned;
            $bucket[$row->type === 'income' ? 'income' : 'expense'] = (float) $row->total;
            $bucket['net'] = $bucket['income'] - $bucket['expense'];

            if ($target) {
                $pl[$target] = $bucket;
            }
        }

        $totals = [
            'income' => $pl->sum('income') + $unassigned['income'],
            'expense' => $pl->sum('expense') + $unassigned['expense'],
        ];
        $totals['net'] = $totals['income'] - $totals['expense'];

        // Staff & students for the assignment modals.
        $staff = User::query()
            ->where('institute_id', $instituteId)
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'branch_id']);

        $students = Student::withoutGlobalScope('branch')
            ->where('institute_id', $instituteId)
            ->orderBy('name_en')
            ->get(['id', 'name_en', 'reference_no', 'branch_id']);

        return view('branches.index', compact(
            'branches', 'pl', 'unassigned', 'totals', 'period', 'staff', 'students'
        ));
    }

    public function store(Request $request)
    {
        $this->guard();

        $data = $request->validate($this->validationRules());

        Branch::create(array_merge($data, [
            'institute_id' => $this->instituteId(),
        ]));

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function update(Request $request, Branch $branch)
    {
        $this->guard();
        abort_if($branch->institute_id !== $this->instituteId(), 403);

        $branch->update($request->validate($this->validationRules()));

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $this->guard();
        abort_if($branch->institute_id !== $this->instituteId(), 403);

        $count = Branch::where('institute_id', $this->instituteId())->count();

        if ($count <= 1) {
            return back()->with('error', 'At least one branch must remain. Every institute needs a branch.');
        }

        if (BranchContext::current(Auth::user())?->id === $branch->id) {
            $fallback = Branch::where('institute_id', $this->instituteId())
                ->where('id', '!=', $branch->id)
                ->orderBy('id')
                ->first();

            if ($fallback) {
                BranchContext::set(Auth::user(), $fallback->id);
            }
        }

        // Assigned staff, students and transactions keep their records; the
        // FK is nullOnDelete so they simply become branch-less.
        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted. Its people and transactions are now unassigned.');
    }

    /**
     * Top-bar branch switcher.
     */
    public function switchBranch(Request $request)
    {
        $data = $request->validate(['branch_id' => ['required', 'integer']]);

        if (! BranchContext::set(Auth::user(), (int) $data['branch_id'])) {
            return back()->with('error', 'Selected branch could not be activated.');
        }

        $branch = Branch::find($data['branch_id']);

        return back()->with('success', 'Switched to '.$branch?->name.'.');
    }

    /**
     * Move selected staff or students into a branch.
     */
    public function assign(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'branch_id' => ['required', 'integer'],
            'type' => ['required', 'in:staff,students'],
            'people' => ['nullable', 'array'],
            'people.*' => ['integer'],
        ]);

        $branch = Branch::where('institute_id', $this->instituteId())
            ->findOrFail($data['branch_id']);

        $people = collect($data['people'] ?? [])->map(fn ($id) => (int) $id);

        $moved = 0;

        if ($data['type'] === 'staff') {
            $moved = User::query()
                ->where('institute_id', $this->instituteId())
                ->whereIn('id', $people)
                ->update(['branch_id' => $branch->id]);
        } else {
            $moved = Student::withoutGlobalScope('branch')
                ->where('institute_id', $this->instituteId())
                ->whereIn('id', $people)
                ->update(['branch_id' => $branch->id]);
        }

        return redirect()->route('branches.index')
            ->with('success', $moved.' '.($data['type'] === 'staff' ? 'staff member(s)' : 'student(s)').' assigned to '.$branch->name.'.');
    }

    private function guard(): void
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Branch Management.');
        abort_if(! Auth::user()->institute_id, 403, 'No institute context.');
    }

    private function instituteId(): int
    {
        return (int) Auth::user()->institute_id;
    }

    private function validationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolvePeriod(Request $request): array
    {
        $period = $request->input('period', 'this_month');

        if (! in_array($period, ['this_month', 'this_year', 'all'], true)) {
            $period = 'this_month';
        }

        $now = now();

        return match ($period) {
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear(), $period],
            'all' => [now()->startOfDay(), now()->endOfDay(), $period],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), $period],
        };
    }
}
