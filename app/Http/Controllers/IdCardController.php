<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Course;
use App\Models\IdCardTemplate;
use App\Models\Institute;
use App\Models\Student;
use App\Models\User;
use App\Support\IdCardToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IdCardController extends Controller
{
    public function index(Request $request)
    {
        $institute = $this->institute($request);
        $type = $this->cardType($request);
        $q = trim((string) $request->query('q', ''));

        $courses = collect();
        $batches = collect();

        if ($type === 'student') {
            $courses = Course::where('institute_id', $institute->id)->orderBy('name')->get();
            $batches = Batch::where('institute_id', $institute->id)->orderBy('name')->get();

            $students = Student::with(['course', 'batch'])
                ->where('institute_id', $institute->id)
                ->when($q !== '', function ($query) use ($q) {
                    $term = '%'.$q.'%';
                    $query->where(function ($sub) use ($term) {
                        $sub->where('name_en', 'ilike', $term)
                            ->orWhere('name_bn', 'ilike', $term)
                            ->orWhere('reference_no', 'ilike', $term)
                            ->orWhere('contact', 'ilike', $term);
                    });
                })
                ->when($request->filled('course_id'), fn ($query) => $query->where('course_id', $request->integer('course_id')))
                ->when($request->filled('batch_id'), fn ($query) => $query->where('batch_id', $request->integer('batch_id')))
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
                ->orderBy('name_en')
                ->limit(1000)
                ->get();

            $people = $students->map(fn (Student $student) => $this->studentCardData($student, $institute));
        } else {
            $users = User::where('institute_id', $institute->id)
                ->when($q !== '', function ($query) use ($q) {
                    $term = '%'.$q.'%';
                    $query->where(function ($sub) use ($term) {
                        $sub->where('name', 'ilike', $term)
                            ->orWhere('designation', 'ilike', $term)
                            ->orWhere('email', 'ilike', $term)
                            ->orWhere('phone', 'ilike', $term);
                    });
                })
                ->when($request->filled('role'), fn ($query) => $query->where('role', $request->query('role')))
                ->orderBy('name')
                ->limit(1000)
                ->get();

            $people = $users->map(fn (User $user) => $this->employeeCardData($user, $institute));
        }

        $template = IdCardTemplate::for($institute->id, $type);
        $counts = [
            'student' => Student::where('institute_id', $institute->id)->count(),
            'employee' => User::where('institute_id', $institute->id)->count(),
            'student_photo' => Student::where('institute_id', $institute->id)->whereNotNull('photo_path')->count(),
            'employee_photo' => User::where('institute_id', $institute->id)->whereNotNull('photo_path')->count(),
        ];

        return view('id-cards.index', compact(
            'type', 'people', 'template', 'institute', 'courses', 'batches', 'counts', 'q'
        ));
    }

    public function editor(Request $request)
    {
        $institute = $this->institute($request);
        $type = $this->cardType($request);
        $template = IdCardTemplate::for($institute->id, $type);
        $design = $template->resolvedDesign();

        if ($type === 'student') {
            $student = Student::with(['course', 'batch'])
                ->where('institute_id', $institute->id)
                ->orderBy('name_en')
                ->first();
            $sample = $student
                ? $this->studentCardData($student, $institute)
                : $this->samplePerson($type);
        } else {
            $user = User::where('institute_id', $institute->id)->orderBy('name')->first();
            $sample = $user
                ? $this->employeeCardData($user, $institute)
                : $this->samplePerson($type);
        }

        return view('id-cards.editor', compact('institute', 'type', 'template', 'design', 'sample'));
    }

    public function updateDesign(Request $request)
    {
        $institute = $this->institute($request);
        $type = $this->cardType($request);

        $template = IdCardTemplate::for($institute->id, $type);
        $template->update(['design' => $this->normalizeDesign($request, $type)]);

        return redirect()
            ->route('id-cards.editor', ['type' => $type])
            ->with('success', 'ID card design saved.');
    }

    public function printCards(Request $request)
    {
        $institute = $this->institute($request);
        $type = $this->cardType($request);

        $rawIds = $request->query('ids', []);
        $rawIds = is_array($rawIds) ? $rawIds : [$rawIds];
        $ids = collect($rawIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return redirect()
                ->route('id-cards.index', ['type' => $type])
                ->with('error', 'Select at least one person before printing.');
        }

        if ($type === 'student') {
            $people = Student::with(['course', 'batch'])
                ->where('institute_id', $institute->id)
                ->whereIn('id', $ids)
                ->orderBy('name_en')
                ->get()
                ->map(fn (Student $student) => $this->studentCardData($student, $institute));
        } else {
            $people = User::where('institute_id', $institute->id)
                ->whereIn('id', $ids)
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => $this->employeeCardData($user, $institute));
        }

        if ($people->isEmpty()) {
            return redirect()
                ->route('id-cards.index', ['type' => $type])
                ->with('error', 'No matching records were found for printing.');
        }

        $template = IdCardTemplate::for($institute->id, $type);
        $design = $template->resolvedDesign();

        $contact = [
            'address' => $design['back']['address'] ?: $institute->address,
            'phone' => $design['back']['phone'] ?: $institute->mobile,
            'email' => $design['back']['email'] ?: $institute->email,
            'website' => $design['back']['website'] ?: '',
        ];

        return view('id-cards.print', compact('institute', 'type', 'people', 'design', 'contact'));
    }

    public function profile(string $type, int $id, string $token)
    {
        abort_unless(in_array($type, ['student', 'employee'], true), 404);
        abort_unless(IdCardToken::matches($type, $id, $token), 404);

        if ($type === 'student') {
            $student = Student::with(['institute', 'course', 'batch'])->findOrFail($id);
            $institute = $student->institute;
            abort_unless($institute, 404);
            $person = $this->studentCardData($student, $institute);
        } else {
            $user = User::with('institute')->findOrFail($id);
            $institute = $user->institute;
            abort_unless($institute, 404);
            $person = $this->employeeCardData($user, $institute);
        }

        $template = IdCardTemplate::for($institute->id, $type);
        $design = $template->resolvedDesign();

        return view('id-cards.profile', compact('institute', 'type', 'person', 'design'));
    }

    public function uploadPhoto(Request $request, string $type, int $person)
    {
        abort_unless(in_array($type, ['student', 'employee'], true), 404);

        $institute = $this->institute($request);

        $request->validate([
            'photo' => ['required', 'image', 'max:4096'],
        ]);

        if ($type === 'student') {
            $student = Student::where('institute_id', $institute->id)->findOrFail($person);

            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }

            $student->photo_path = $request->file('photo')->store('student-photos', 'public');
            $student->save();
        } else {
            $user = User::where('institute_id', $institute->id)->findOrFail($person);

            if ($user->photo_path) {
                Storage::disk('public')->delete($user->photo_path);
            }

            $user->photo_path = $request->file('photo')->store('employee-photos', 'public');
            $user->save();
        }

        // Redirect explicitly instead of using back(): the previous URL can be a
        // print preview, which would bounce the user to an empty print sheet.
        $base = route('id-cards.index');
        $fallback = route('id-cards.index', ['type' => $type]);
        $returnTo = (string) $request->input('return_to', '');

        $target = ($returnTo === $base || str_starts_with($returnTo, $base.'?'))
            ? $returnTo
            : $fallback;

        return redirect()->to($target)->with('success', 'ID card photo updated.');
    }

    private function studentCardData(Student $student, Institute $institute): array
    {
        $idNo = $student->reference_no
            ?: 'STU-'.str_pad((string) $student->id, 5, '0', STR_PAD_LEFT);

        $qrUrl = IdCardToken::url('student', $student->id);

        return [
            'id' => $student->id,
            'type' => 'student',
            'name' => $student->name_en,
            'name_bn' => $student->name_bn,
            'initials' => $this->initials($student->name_en),
            'photo' => $student->photo_path ? asset('storage/'.$student->photo_path) : null,
            'id_no' => $idNo,
            'line1' => $student->course?->name,
            'line2' => $student->batch?->name,
            'blood' => $student->blood_group,
            'phone' => $student->contact,
            'valid' => $student->batch?->end_date?->format('M Y'),
            'status' => ucfirst((string) $student->status),
            'qr_url' => $qrUrl,
            'qr_image' => $this->qrImage($qrUrl),
        ];
    }

    private function employeeCardData(User $user, Institute $institute): array
    {
        $qrUrl = IdCardToken::url('employee', $user->id);

        return [
            'id' => $user->id,
            'type' => 'employee',
            'name' => $user->name,
            'name_bn' => null,
            'initials' => $this->initials($user->name),
            'photo' => $user->photo_path ? asset('storage/'.$user->photo_path) : null,
            'id_no' => 'EMP-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
            'line1' => $user->designation ?: ucfirst((string) ($user->role ?? 'Staff')),
            'line2' => $institute->name,
            'blood' => null,
            'phone' => $user->phone,
            'valid' => null,
            'status' => ucfirst((string) ($user->role ?? 'Staff')),
            'qr_url' => $qrUrl,
            'qr_image' => $this->qrImage($qrUrl),
        ];
    }

    private function samplePerson(string $type): array
    {
        $isStudent = $type === 'student';
        $qrUrl = route('id-cards.index');
        $name = $isStudent ? 'Md. Rakib Hasan' : 'Nusrat Jahan';

        return [
            'id' => 0,
            'type' => $type,
            'name' => $name,
            'name_bn' => $isStudent ? 'মোঃ রাকিব হাসান' : null,
            'initials' => $this->initials($name),
            'photo' => null,
            'id_no' => $isStudent ? 'STU-00001' : 'EMP-0001',
            'line1' => $isStudent ? 'Electrical Installation & Maintenance' : 'Senior Instructor',
            'line2' => $isStudent ? 'Batch EIM-12 (Morning)' : 'Demo Institute',
            'blood' => $isStudent ? 'B+' : null,
            'phone' => '01712-345678',
            'valid' => $isStudent ? 'Dec 2026' : null,
            'status' => $isStudent ? 'Admitted' : 'Trainer',
            'qr_url' => $qrUrl,
            'qr_image' => $this->qrImage($qrUrl),
        ];
    }

    private function qrImage(string $url): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=0&data='.urlencode($url);
    }

    private function initials(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return 'ID';
        }

        $parts = preg_split('/\s+/u', $name) ?: [];
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $letters ?: mb_strtoupper(mb_substr($name, 0, 1));
    }

    private function cardType(Request $request): string
    {
        $type = $request->input('type', $request->query('type'));

        return $type === 'employee' ? 'employee' : 'student';
    }

    private function institute(Request $request): Institute
    {
        $institute = $request->user()?->institute;
        abort_unless($institute, 403, 'No institute is assigned to this account.');

        return $institute;
    }

    private function normalizeDesign(Request $request, string $type): array
    {
        $defaults = IdCardTemplate::defaults($type);
        $design = [];

        $colorKeys = ['accent', 'accent2', 'background', 'text'];
        $limits = [
            'title' => 80,
            'subtitle' => 120,
            'footer' => 180,
            'instructions' => 600,
            'address' => 200,
            'phone' => 80,
            'email' => 120,
            'website' => 160,
        ];

        foreach (['front', 'back'] as $side) {
            foreach ($defaults[$side] as $key => $default) {
                $value = $request->input($side.'.'.$key);

                if (is_bool($default)) {
                    $design[$side][$key] = $request->boolean($side.'.'.$key);

                    continue;
                }

                if (in_array($key, $colorKeys, true)) {
                    $design[$side][$key] = is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)
                        ? $value
                        : $default;

                    continue;
                }

                if (! is_string($value)) {
                    $design[$side][$key] = $default;

                    continue;
                }

                $clean = trim(strip_tags($value));

                // Only the card title is required. Every other text field may be
                // intentionally cleared to hide a line from the printed card.
                if ($clean === '' && $key === 'title') {
                    $design[$side][$key] = $default;

                    continue;
                }

                $design[$side][$key] = mb_substr($clean, 0, $limits[$key] ?? 255);
            }
        }

        return $design;
    }
}
