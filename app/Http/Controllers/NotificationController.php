<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Notification;
use App\Models\Student;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index()
    {
        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $notifications = Notification::with('batch')
            ->where('institute_id', $instituteId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $batchesQuery = Batch::with('course')
            ->where('institute_id', $instituteId)
            ->whereIn('status', ['active', 'running', 'upcoming']);

        if ($projectId) {
            $batchesQuery->where('project_id', $projectId);
        }

        $batches = $batchesQuery->orderBy('name')->get();

        return view('notifications.index', compact('notifications', 'batches'));
    }

    public function create()
    {
        $instituteId = Auth::user()->institute_id;
        $projectId = session('current_project_id');

        $batches = Batch::with('course')
            ->where('institute_id', $instituteId)
            ->whereIn('status', ['active', 'running', 'upcoming']);

        if ($projectId) {
            $batches->where('project_id', $projectId);
        }

        $batches = $batches->get();

        return view('notifications.create', compact('batches'));
    }

    public function sendBulk(Request $request)
    {
        $instituteId = Auth::user()->institute_id;

        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'message_body' => 'required|string|max:500',
            'type' => 'required|in:sms,system_alert',
        ]);

        $batch = Batch::findOrFail($request->batch_id);
        abort_if($batch->institute_id !== $instituteId, 403);

        $students = Student::where('batch_id', $request->batch_id)
            ->whereIn('status', ['admitted', 'active'])
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No active students found in this batch.');
        }

        $sent = 0;
        $failed = 0;

        foreach ($students as $student) {
            if ($student->contact) {
                $result = ['success' => true, 'status' => 'sent', 'response' => 'System alert dispatched'];

                if ($request->type === 'sms') {
                    $result = $this->smsService->send($student->contact, $request->message_body);
                }

                Notification::create([
                    'institute_id' => $instituteId,
                    'user_id' => Auth::id(),
                    'batch_id' => $request->batch_id,
                    'recipient_phone' => $student->contact,
                    'message_body' => $request->message_body,
                    'type' => $request->type,
                    'status' => $result['status'],
                    'response' => is_array($result['response']) ? json_encode($result['response']) : $result['response'],
                ]);

                if ($result['status'] === 'sent') {
                    $sent++;
                } else {
                    $failed++;
                }
            }
        }

        $msg = "{$sent} notifications sent successfully.";
        if ($failed > 0) {
            $msg .= " ({$failed} failed)";
        }

        return redirect()->route('notifications.index')->with('success', $msg);
    }

    public function sendSingle(Request $request)
    {
        $instituteId = Auth::user()->institute_id;

        $request->validate([
            'recipient_phone' => 'required|string|max:20',
            'message_body' => 'required|string|max:500',
            'type' => 'required|in:sms,system_alert',
        ]);

        $result = ['success' => true, 'status' => 'sent', 'response' => 'System alert dispatched'];

        if ($request->type === 'sms') {
            $result = $this->smsService->send($request->recipient_phone, $request->message_body);
        }

        Notification::create([
            'institute_id' => $instituteId,
            'user_id' => Auth::id(),
            'recipient_phone' => $request->recipient_phone,
            'message_body' => $request->message_body,
            'type' => $request->type,
            'status' => $result['status'],
            'response' => is_array($result['response']) ? json_encode($result['response']) : $result['response'],
        ]);

        if ($result['status'] === 'sent') {
            return redirect()->route('notifications.index')->with('success', 'Notification sent successfully.');
        } else {
            return redirect()->route('notifications.index')->with('error', 'Failed to dispatch SMS notification via gateway.');
        }
    }
}
