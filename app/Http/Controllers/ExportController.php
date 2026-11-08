<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    /**
     * Export Transactions to CSV.
     */
    public function exportTransactions(Request $request)
    {
        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $query = Transaction::where('institute_id', $instituteId);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        $filename = "transactions_export_" . date('Y-m-d_H-i') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel Bengali character compatibility
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['ID', 'Date', 'Type', 'Category', 'Amount (BDT)', 'Payment Method', 'Reference No', 'Description']);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->id,
                    $t->transaction_date,
                    strtoupper($t->type),
                    $t->category,
                    $t->amount,
                    $t->payment_method ?? 'Cash',
                    $t->reference_no ?? '-',
                    $t->description ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Students to CSV.
     */
    public function exportStudents(Request $request)
    {
        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $query = Student::with(['course', 'batch'])
            ->where('institute_id', $instituteId);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $students = $query->orderBy('created_at', 'desc')->get();

        $filename = "students_export_" . date('Y-m-d_H-i') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($students) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['Reference No', 'Full Name', 'Contact Phone', 'Email', 'Course', 'Batch', 'Status', 'Applied Date']);

            foreach ($students as $s) {
                fputcsv($file, [
                    $s->reference_no,
                    $s->name,
                    $s->contact,
                    $s->email ?? '-',
                    $s->course?->name ?? '-',
                    $s->batch?->name ?? '-',
                    strtoupper($s->status),
                    $s->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Attendance Report to CSV.
     */
    public function exportAttendance(Request $request)
    {
        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $query = Attendance::with(['student', 'batch'])
            ->where('institute_id', $instituteId)
            ->whereHas('batch', fn($q) => $q->where('project_id', $projectId));

        if ($request->batch_id) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $attendances = $query->orderBy('created_at', 'desc')->get();

        $filename = "attendance_export_" . date('Y-m-d_H-i') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['Date', 'Student Name', 'Reference No', 'Batch', 'Status', 'Remarks']);

            foreach ($attendances as $a) {
                fputcsv($file, [
                    $a->created_at->format('Y-m-d H:i'),
                    $a->student?->name ?? '-',
                    $a->student?->reference_no ?? '-',
                    $a->batch?->name ?? '-',
                    $a->is_present ? 'PRESENT' : 'ABSENT',
                    $a->remarks ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
