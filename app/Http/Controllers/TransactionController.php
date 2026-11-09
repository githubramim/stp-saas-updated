<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Financial Transactions.');

        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $query = Transaction::with(['student', 'batch'])
            ->where('institute_id', $instituteId);

        if ($projectId) {
            $query->where(function ($q) use ($projectId) {
                $q->whereHas('student', fn($sq) => $sq->where('project_id', $projectId))
                  ->orWhereHas('batch', fn($bq) => $bq->where('project_id', $projectId))
                  ->orWhereNull('student_id');
            });
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->date_from) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');

        $batches = Batch::with('course')
            ->where('institute_id', $instituteId)
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->whereIn('status', ['active', 'running', 'upcoming'])
            ->get();

        $students = Student::with(['course', 'batch'])
            ->where('institute_id', $instituteId)
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->whereIn('status', ['admitted', 'active', 'applied'])
            ->get();

        return view('transactions.index', compact('transactions', 'totalIncome', 'totalExpense', 'batches', 'students'));
    }

    public function export(Request $request)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Financial Transactions.');

        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $query = Transaction::with(['student', 'batch'])
            ->where('institute_id', $instituteId);

        if ($projectId) {
            $query->where(function ($q) use ($projectId) {
                $q->whereHas('student', fn ($sq) => $sq->where('project_id', $projectId))
                    ->orWhereHas('batch', fn ($bq) => $bq->where('project_id', $projectId))
                    ->orWhereNull('student_id');
            });
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->date_from) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();
        $filename = 'transactions-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date', 'Type', 'Category', 'Student', 'Batch',
                'Amount', 'Payment Method', 'Receipt Number', 'Description',
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    optional($transaction->transaction_date)->format('Y-m-d'),
                    $transaction->type,
                    $transaction->category,
                    $transaction->student?->name_en,
                    $transaction->batch?->name,
                    $transaction->amount,
                    $transaction->payment_method,
                    $transaction->receipt_number,
                    $transaction->description,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create()
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Financial Transactions.');

        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $studentsQuery = Student::with(['course', 'batch'])
            ->where('institute_id', $instituteId)
            ->whereIn('status', ['admitted', 'active', 'applied']);

        if ($projectId) {
            $studentsQuery->where('project_id', $projectId);
        }

        $students = $studentsQuery->orderBy('name_en')->get();

        $batchesQuery = Batch::with('course')
            ->where('institute_id', $instituteId);

        if ($projectId) {
            $batchesQuery->where('project_id', $projectId);
        }

        $batches = $batchesQuery->orderBy('name')->get();

        return view('transactions.create', compact('students', 'batches'));
    }

    public function store(Request $request)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Financial Transactions.');

        $instituteId = Auth::user()->institute_id;

        $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'student_id' => 'nullable|exists:students,id',
            'batch_id' => 'nullable|exists:batches,id',
            'payment_method' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $receiptNumber = null;
        if ($request->type === 'income') {
            $receiptNumber = 'RCP-' . strtoupper(uniqid());
        }

        $category = $request->category;
        if (in_array($category, ['Other Income', 'Other Expense', 'Others']) && !empty($request->custom_category)) {
            $category = trim($request->custom_category);
        }

        Transaction::create([
            'institute_id' => $instituteId,
            'student_id' => $request->student_id,
            'batch_id' => $request->batch_id,
            'type' => $request->type,
            'category' => $category,
            'amount' => $request->amount,
            'transaction_date' => $request->transaction_date,
            'receipt_number' => $receiptNumber,
            'payment_method' => $request->payment_method,
            'description' => $request->description,
        ]);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction recorded successfully.');
    }

    public function show(Transaction $transaction)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Financial Transactions.');
        abort_if($transaction->institute_id !== Auth::user()->institute_id, 403);
        $transaction->load('student', 'batch');

        return view('transactions.show', compact('transaction'));
    }

    public function receipt(Transaction $transaction)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Financial Transactions.');
        abort_if($transaction->institute_id !== Auth::user()->institute_id, 403);
        abort_if($transaction->type !== 'income', 404);
        $transaction->load('student', 'batch');

        return view('transactions.receipt', compact('transaction'));
    }

    public function destroy(Transaction $transaction)
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Financial Transactions.');
        abort_if($transaction->institute_id !== Auth::user()->institute_id, 403);
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}
