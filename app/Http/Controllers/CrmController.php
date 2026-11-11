<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\LeadMessage;
use App\Models\Student;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CrmController extends Controller
{
    public function __construct(private WhatsAppService $whatsapp)
    {
    }

    private function guard(): void
    {
        abort_if(Auth::user()->isTrainer(), 403, 'Trainers do not have access to Lead & CRM Management.');
    }

    private function instituteId(): int
    {
        return (int) Auth::user()->institute_id;
    }

    private function instituteName(): string
    {
        return (string) (Auth::user()->institute?->name ?? 'Our Institute');
    }

    /**
     * All leads for this institute. Every action runs through this scope.
     */
    private function leadQuery()
    {
        return Lead::query()->where('institute_id', $this->instituteId());
    }

    private function staff()
    {
        return User::query()
            ->where('institute_id', $this->instituteId())
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    }

    private function courses()
    {
        return Course::query()
            ->where('institute_id', $this->instituteId())
            ->orderBy('name')
            ->get(['id', 'name', 'project_id']);
    }

    private function batches()
    {
        return Batch::query()
            ->where('institute_id', $this->instituteId())
            ->orderBy('name')
            ->get(['id', 'name', 'course_id', 'project_id', 'shift']);
    }

    private function whatsappTemplates(Lead $lead): array
    {
        $replace = [
            'name' => $lead->name,
            'institute' => $this->instituteName(),
            'date' => optional($lead->next_follow_up_at)->format('d M Y, h:i A') ?? now()->format('d M Y'),
        ];

        $templates = [];

        foreach ((array) config('whatsapp.templates', []) as $key => $template) {
            $templates[$key] = $this->whatsapp->template($key, $replace);
        }

        return $templates;
    }

    private function ensureLead(Lead $lead): void
    {
        abort_if($lead->institute_id !== $this->instituteId(), 403);
    }

    private function ensureFollowUp(LeadFollowUp $followUp): void
    {
        abort_if($followUp->institute_id !== $this->instituteId(), 403);
    }

    private function logMessage(Lead $lead, string $channel, string $body, string $status = 'sent', ?array $response = null, string $direction = 'outbound'): LeadMessage
    {
        return LeadMessage::create([
            'institute_id' => $this->instituteId(),
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'channel' => $channel,
            'direction' => $direction,
            'status' => $status,
            'body' => $body,
            'provider_response' => $response ? json_encode($response) : null,
            'sent_at' => now(),
        ]);
    }

    public function dashboard()
    {
        $this->guard();

        $instituteId = $this->instituteId();
        $base = $this->leadQuery();

        $totalLeads = (clone $base)->count();
        $statusCounts = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $countByStatus = fn (string $status) => (int) ($statusCounts[$status] ?? 0);

        $converted = $countByStatus(Lead::STATUS_CONVERTED);
        $lost = $countByStatus(Lead::STATUS_LOST);
        $open = $totalLeads - $converted - $lost;
        $conversionRate = $totalLeads > 0 ? round(($converted / $totalLeads) * 100, 1) : 0.0;

        $contactedReached = $totalLeads - $countByStatus(Lead::STATUS_NEW) - $lost;
        $qualifiedReached = $countByStatus(Lead::STATUS_QUALIFIED)
            + $countByStatus(Lead::STATUS_FOLLOW_UP)
            + $converted;

        $funnel = [
            ['label' => 'Leads Captured', 'count' => $totalLeads, 'icon' => 'bi-inboxes'],
            ['label' => 'Contacted', 'count' => max(0, $contactedReached), 'icon' => 'bi-telephone-outbound'],
            ['label' => 'Qualified', 'count' => $qualifiedReached, 'icon' => 'bi-patch-check'],
            ['label' => 'Enrolled', 'count' => $converted, 'icon' => 'bi-mortarboard'],
        ];

        foreach ($funnel as $index => $stage) {
            $funnel[$index]['percent'] = $totalLeads > 0
                ? round(($stage['count'] / $totalLeads) * 100, 1)
                : 0.0;
        }

        $sourceRows = (clone $base)
            ->selectRaw("source, COUNT(*) as total, SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted")
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        $sourcePerformance = $sourceRows->map(function ($row) {
            return [
                'source' => $row->source,
                'label' => Lead::SOURCES[$row->source] ?? ucfirst((string) $row->source),
                'icon' => match ($row->source) {
                    Lead::SOURCE_FACEBOOK => 'bi-facebook',
                    Lead::SOURCE_WEBSITE => 'bi-globe2',
                    Lead::SOURCE_WALK_IN => 'bi-person-walking',
                    Lead::SOURCE_REFERRAL => 'bi-people',
                    Lead::SOURCE_PHONE => 'bi-telephone',
                    default => 'bi-share',
                },
                'total' => (int) $row->total,
                'converted' => (int) $row->converted,
                'rate' => (int) $row->total > 0 ? round(((int) $row->converted / (int) $row->total) * 100, 1) : 0.0,
            ];
        });

        $thisMonth = (clone $base)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $lastMonth = (clone $base)->whereBetween('created_at', [
            now()->copy()->subMonthNoOverflow()->startOfMonth(),
            now()->copy()->subMonthNoOverflow()->endOfMonth(),
        ])->count();

        $monthGrowth = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : ($thisMonth > 0 ? 100.0 : 0.0);

        $pendingFollowUps = LeadFollowUp::query()
            ->where('institute_id', $instituteId)
            ->where('status', LeadFollowUp::STATUS_PENDING);

        $overdueCount = (clone $pendingFollowUps)->where('scheduled_at', '<', now()->startOfDay())->count();
        $todayCount = (clone $pendingFollowUps)->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()])->count();
        $upcomingCount = (clone $pendingFollowUps)
            ->whereBetween('scheduled_at', [now()->endOfDay(), now()->copy()->addDays(7)->endOfDay()])
            ->count();

        $todayFollowUps = LeadFollowUp::with(['lead', 'user'])
            ->where('institute_id', $instituteId)
            ->where('status', LeadFollowUp::STATUS_PENDING)
            ->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()])
            ->orderBy('scheduled_at')
            ->limit(8)
            ->get();

        $recentLeads = (clone $base)
            ->with(['course', 'assignedTo'])
            ->latest()
            ->limit(8)
            ->get();

        $convertedThisMonth = (clone $base)
            ->where('status', Lead::STATUS_CONVERTED)
            ->whereBetween('converted_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $whatsappThisMonth = LeadMessage::query()
            ->where('institute_id', $instituteId)
            ->where('channel', LeadMessage::CHANNEL_WHATSAPP)
            ->whereBetween('sent_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $trend = [];

        for ($i = 5; $i >= 0; $i--) {
            $cursor = now()->copy()->subMonthsNoOverflow($i);

            $created = (clone $base)->whereBetween('created_at', [
                $cursor->copy()->startOfMonth(),
                $cursor->copy()->endOfMonth(),
            ])->count();

            $enrolled = (clone $base)->where('status', Lead::STATUS_CONVERTED)
                ->whereBetween('converted_at', [
                    $cursor->copy()->startOfMonth(),
                    $cursor->copy()->endOfMonth(),
                ])->count();

            $trend[] = [
                'label' => $cursor->format('M Y'),
                'leads' => $created,
                'converted' => $enrolled,
            ];
        }

        return view('crm.dashboard', compact(
            'totalLeads', 'converted', 'lost', 'open', 'conversionRate',
            'funnel', 'sourcePerformance', 'thisMonth', 'lastMonth', 'monthGrowth',
            'overdueCount', 'todayCount', 'upcomingCount', 'todayFollowUps',
            'recentLeads', 'convertedThisMonth', 'whatsappThisMonth', 'trend'
        ));
    }

    public function leads(Request $request)
    {
        $this->guard();

        $query = $this->leadQuery()->with(['course', 'assignedTo']);

        $query->when($request->filled('q'), function ($q) use ($request) {
            $term = '%' . trim((string) $request->q) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'ilike', $term)
                    ->orWhere('phone', 'ilike', $term)
                    ->orWhere('email', 'ilike', $term)
                    ->orWhere('interest', 'ilike', $term);
            });
        });

        $query->when($request->filled('source'), fn ($q) => $q->where('source', $request->source));
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $query->when($request->filled('assigned'), fn ($q) => $q->where('assigned_to', $request->assigned));

        $query->when($request->filled('followup'), function ($q) use ($request) {
            $bucket = $request->followup;

            if ($bucket === 'overdue') {
                $q->where('next_follow_up_at', '<', now()->startOfDay());
            } elseif ($bucket === 'today') {
                $q->whereBetween('next_follow_up_at', [now()->startOfDay(), now()->endOfDay()]);
            } elseif ($bucket === 'upcoming') {
                $q->where('next_follow_up_at', '>', now()->endOfDay());
            } elseif ($bucket === 'none') {
                $q->whereNull('next_follow_up_at');
            }
        });

        $filteredCount = (clone $query)->count();

        $leads = $query
            ->orderByRaw("CASE WHEN status IN ('converted', 'lost') THEN 1 ELSE 0 END")
            ->orderByRaw('next_follow_up_at IS NULL')
            ->orderBy('next_follow_up_at')
            ->orderByDesc('created_at')
            ->get();

        $statusCounts = (clone $this->leadQuery())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $sourceCounts = (clone $this->leadQuery())
            ->selectRaw('source, COUNT(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        $staff = $this->staff();
        $courses = $this->courses();
        $batches = $this->batches();

        return view('crm.leads', compact(
            'leads', 'filteredCount', 'statusCounts', 'sourceCounts', 'staff', 'courses', 'batches'
        ));
    }

    public function storeLead(Request $request)
    {
        $this->guard();

        $data = $this->validateLead($request);

        $duplicate = $this->leadQuery()
            ->where('phone', $data['phone'])
            ->whereNotIn('status', [Lead::STATUS_CONVERTED, Lead::STATUS_LOST])
            ->first();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with('error', 'An open lead already exists for this phone number: ' . $duplicate->name . '. Open that lead instead of creating a duplicate.');
        }

        $followUpAt = $data['next_follow_up_at'] ?? null;
        unset($data['next_follow_up_at'], $data['follow_up_type'], $data['follow_up_notes']);

        $lead = DB::transaction(function () use ($data, $followUpAt, $request) {
            $lead = Lead::create(array_merge($data, [
                'institute_id' => $this->instituteId(),
                'status' => $data['status'] ?? Lead::STATUS_NEW,
                'next_follow_up_at' => $followUpAt ? Carbon::parse($followUpAt) : null,
            ]));

            if ($followUpAt) {
                LeadFollowUp::create([
                    'institute_id' => $this->instituteId(),
                    'lead_id' => $lead->id,
                    'user_id' => Auth::id(),
                    'type' => $request->input('follow_up_type', 'call'),
                    'status' => LeadFollowUp::STATUS_PENDING,
                    'scheduled_at' => Carbon::parse($followUpAt),
                    'notes' => $request->input('follow_up_notes'),
                ]);
            }

            if (filled($lead->notes)) {
                $this->logMessage($lead, LeadMessage::CHANNEL_NOTE, 'Lead created: ' . $lead->notes, 'logged', null, 'inbound');
            }

            return $lead;
        });

        return redirect()->route('crm.leads')
            ->with('success', 'Lead “' . $lead->name . '” added successfully.');
    }

    public function showLead(Lead $lead)
    {
        $this->guard();
        $this->ensureLead($lead);

        $lead->load([
            'project', 'course', 'batch', 'assignedTo', 'convertedStudent',
            'followUps.user', 'messages.user',
        ]);

        $timeline = $lead->followUps->map(function (LeadFollowUp $followUp) {
            return [
                'kind' => 'follow_up',
                'at' => $followUp->completed_at ?? $followUp->scheduled_at,
                'model' => $followUp,
            ];
        })->concat($lead->messages->map(function (LeadMessage $message) {
            return [
                'kind' => 'message',
                'at' => $message->sent_at ?? $message->created_at,
                'model' => $message,
            ];
        }))->sortByDesc('at')->values();

        $staff = $this->staff();
        $courses = $this->courses();
        $batches = $this->batches();
        $whatsappTemplates = $this->whatsappTemplates($lead);
        $whatsappCloud = $this->whatsapp->usesCloudApi();

        return view('crm.show', compact(
            'lead', 'timeline', 'staff', 'courses', 'batches', 'whatsappTemplates', 'whatsappCloud'
        ));
    }

    public function updateLead(Request $request, Lead $lead)
    {
        $this->guard();
        $this->ensureLead($lead);

        $data = $this->validateLead($request, $lead);
        unset($data['next_follow_up_at'], $data['follow_up_type'], $data['follow_up_notes']);

        $lead->update($data);

        return redirect()->route('crm.leads.show', $lead)
            ->with('success', 'Lead details updated.');
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $this->guard();
        $this->ensureLead($lead);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Lead::MANUAL_STATUSES))],
            'lost_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($lead->isConverted()) {
            return back()->with('error', 'Converted leads are locked. Manage the linked student record from Admissions instead.');
        }

        if ($data['status'] === Lead::STATUS_CONVERTED && ! $lead->converted_student_id) {
            return back()->with('error', 'Use “Convert to Student” to mark this lead as enrolled.');
        }

        $updates = ['status' => $data['status']];

        if ($data['status'] === Lead::STATUS_LOST) {
            $updates['lost_reason'] = $data['lost_reason'] ?? null;
            $updates['next_follow_up_at'] = null;

            $lead->followUps()
                ->where('status', LeadFollowUp::STATUS_PENDING)
                ->update(['status' => LeadFollowUp::STATUS_CANCELLED, 'updated_at' => now()]);
        } else {
            $updates['lost_reason'] = null;

            if ($data['status'] === Lead::STATUS_CONTACTED && ! $lead->last_contacted_at) {
                $updates['last_contacted_at'] = now();
            }
        }

        $lead->update($updates);

        if ($data['status'] !== Lead::STATUS_LOST) {
            $lead->syncNextFollowUp();
        }

        return redirect()->route('crm.leads.show', $lead)
            ->with('success', 'Lead status changed to ' . (Lead::STATUSES[$data['status']] ?? $data['status']) . '.');
    }

    public function destroyLead(Lead $lead)
    {
        $this->guard();
        $this->ensureLead($lead);

        if ($lead->isConverted()) {
            return back()->with('error', 'Converted leads cannot be deleted because they are linked to a student record.');
        }

        $name = $lead->name;
        $lead->delete();

        return redirect()->route('crm.leads')
            ->with('success', 'Lead “' . $name . '” deleted.');
    }

    public function storeFollowUp(Request $request, Lead $lead)
    {
        $this->guard();
        $this->ensureLead($lead);

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(LeadFollowUp::TYPES))],
            'scheduled_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($lead, $data) {
            LeadFollowUp::create([
                'institute_id' => $this->instituteId(),
                'lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'type' => $data['type'],
                'status' => LeadFollowUp::STATUS_PENDING,
                'scheduled_at' => Carbon::parse($data['scheduled_at']),
                'notes' => $data['notes'] ?? null,
            ]);

            $lead->syncNextFollowUp();

            if ($lead->status === Lead::STATUS_NEW) {
                $lead->update(['status' => Lead::STATUS_FOLLOW_UP]);
            }
        });

        return redirect()->route('crm.leads.show', $lead)
            ->with('success', 'Follow-up scheduled for ' . Carbon::parse($data['scheduled_at'])->format('d M Y, h:i A') . '.');
    }

    public function completeFollowUp(Request $request, LeadFollowUp $followUp)
    {
        $this->guard();
        $this->ensureFollowUp($followUp);

        if (! $followUp->isPending()) {
            return back()->with('error', 'This follow-up is already closed.');
        }

        $data = $request->validate([
            'outcome' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($followUp, $data) {
            $notes = trim((string) $followUp->notes);
            if (filled($data['outcome'] ?? null)) {
                $notes = trim($notes . "\nOutcome: " . $data['outcome']);
            }

            $followUp->update([
                'status' => LeadFollowUp::STATUS_DONE,
                'completed_at' => now(),
                'notes' => $notes ?: null,
            ]);

            $lead = $followUp->lead;
            if ($lead) {
                $updates = ['last_contacted_at' => now()];

                if (in_array($lead->status, [Lead::STATUS_NEW], true)) {
                    $updates['status'] = Lead::STATUS_CONTACTED;
                }

                $lead->update($updates);
                $lead->syncNextFollowUp();
            }
        });

        return redirect()->route('crm.leads.show', $followUp->lead_id)
            ->with('success', 'Follow-up marked as completed.');
    }

    public function rescheduleFollowUp(Request $request, LeadFollowUp $followUp)
    {
        $this->guard();
        $this->ensureFollowUp($followUp);

        $data = $request->validate([
            'scheduled_at' => ['required', 'date'],
        ]);

        $followUp->update([
            'scheduled_at' => Carbon::parse($data['scheduled_at']),
            'status' => LeadFollowUp::STATUS_PENDING,
            'completed_at' => null,
        ]);

        $followUp->lead?->syncNextFollowUp();

        return redirect()->route('crm.leads.show', $followUp->lead_id)
            ->with('success', 'Follow-up rescheduled to ' . Carbon::parse($data['scheduled_at'])->format('d M Y, h:i A') . '.');
    }

    public function destroyFollowUp(LeadFollowUp $followUp)
    {
        $this->guard();
        $this->ensureFollowUp($followUp);

        $lead = $followUp->lead;
        $followUp->delete();
        $lead?->syncNextFollowUp();

        return redirect()->route('crm.leads.show', $lead?->id)
            ->with('success', 'Follow-up removed.');
    }

    public function sendMessage(Request $request, Lead $lead)
    {
        $this->guard();
        $this->ensureLead($lead);

        $data = $request->validate([
            'channel' => ['required', Rule::in([LeadMessage::CHANNEL_WHATSAPP, LeadMessage::CHANNEL_SMS, LeadMessage::CHANNEL_NOTE])],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $result = ['ok' => true, 'driver' => 'note', 'link' => null, 'response' => null];
        $status = 'logged';

        if ($data['channel'] === LeadMessage::CHANNEL_WHATSAPP) {
            $result = $this->whatsapp->send($lead->phone, $data['body']);
            $status = $result['ok'] ? 'sent' : 'failed';

            if (! empty($result['link'])) {
                session()->flash('whatsapp_link', $result['link']);
            }
        }

        $providerResponse = null;

        if (! empty($result['response'])) {
            $providerResponse = is_array($result['response']) ? $result['response'] : ['response' => $result['response']];
        } elseif (! empty($result['error'])) {
            $providerResponse = ['error' => $result['error']];
        }

        $this->logMessage($lead, $data['channel'], $data['body'], $status, $providerResponse);

        $lead->update([
            'last_contacted_at' => now(),
            'status' => $lead->status === Lead::STATUS_NEW ? Lead::STATUS_CONTACTED : $lead->status,
        ]);

        if (! $result['ok']) {
            return back()->with('error', 'WhatsApp could not be sent: ' . ($result['error'] ?? 'unknown error') . ' The attempt was logged.');
        }

        $message = $data['channel'] === LeadMessage::CHANNEL_WHATSAPP
            ? ($result['driver'] === 'cloud'
                ? 'WhatsApp message sent through the Cloud API.'
                : 'WhatsApp message logged. Use the button below to open it in WhatsApp.')
            : ucfirst($data['channel']) . ' logged on the lead timeline.';

        return back()->with('success', $message);
    }

    public function convert(Request $request, Lead $lead)
    {
        $this->guard();
        $this->ensureLead($lead);

        if ($lead->converted_student_id) {
            return back()->with('error', 'This lead is already linked to student ' . ($lead->convertedStudent?->reference_no ?? '—') . '.');
        }

        $instituteId = $this->instituteId();

        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'father_name_en' => ['required', 'string', 'max:255'],
            'mother_name_en' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            'religion' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'id_type' => ['required', Rule::in(['NID', 'Birth', 'Passport', 'Student ID'])],
            'id_no' => ['required', 'string', 'max:50'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'guardian_contact' => ['required', 'string', 'max:30'],
            'guardian_relation' => ['required', 'string', 'max:50'],
            'present_village' => ['nullable', 'string', 'max:255'],
            'present_district' => ['nullable', 'string', 'max:255'],
            'present_division' => ['nullable', 'string', 'max:255'],
            'course_id' => ['nullable', Rule::exists('courses', 'id')->where('institute_id', $instituteId)],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('institute_id', $instituteId)],
        ]);

        $courseId = $data['course_id'] ?? $lead->course_id;
        $batchId = $data['batch_id'] ?? $lead->batch_id;

        $batch = $batchId ? Batch::where('institute_id', $instituteId)->find($batchId) : null;
        $course = $courseId ? Course::where('institute_id', $instituteId)->find($courseId) : null;

        if ($batch && $course && $batch->course_id && $batch->course_id !== $course->id) {
            return back()->withInput()->with('error', 'The selected batch does not belong to the selected course.');
        }

        if ($batch && ! $course) {
            $course = $batch->course;
            $courseId = $batch->course_id;
        }

        $student = DB::transaction(function () use ($data, $lead, $instituteId, $courseId, $batchId, $batch, $course) {
            $referenceNo = $this->uniqueReferenceNo();

            $student = Student::create([
                'institute_id' => $instituteId,
                'project_id' => $batch?->project_id ?? $course?->project_id ?? $lead->project_id,
                'course_id' => $courseId,
                'batch_id' => $batchId,
                'name_en' => $data['name_en'],
                'name_bn' => $data['name_bn'] ?? null,
                'contact' => $data['contact'],
                'email' => $data['email'] ?? $lead->email,
                'father_name_en' => $data['father_name_en'],
                'mother_name_en' => $data['mother_name_en'],
                'dob' => $data['dob'],
                'gender' => $data['gender'],
                'religion' => $data['religion'] ?? null,
                'nationality' => $data['nationality'] ?? 'Bangladeshi',
                'blood_group' => $data['blood_group'] ?? null,
                'id_type' => $data['id_type'],
                'id_no' => $data['id_no'],
                'occupation' => $data['occupation'] ?? null,
                'guardian_contact' => $data['guardian_contact'],
                'guardian_relation' => $data['guardian_relation'],
                'present_village' => $data['present_village'] ?? null,
                'present_district' => $data['present_district'] ?? null,
                'present_division' => $data['present_division'] ?? null,
                'status' => $batchId ? 'admitted' : 'applied',
                'reference_no' => $referenceNo,
            ]);

            $lead->followUps()
                ->where('status', LeadFollowUp::STATUS_PENDING)
                ->update([
                    'status' => LeadFollowUp::STATUS_DONE,
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            $lead->update([
                'status' => Lead::STATUS_CONVERTED,
                'converted_student_id' => $student->id,
                'converted_at' => now(),
                'next_follow_up_at' => null,
                'course_id' => $courseId,
                'batch_id' => $batchId,
                'lost_reason' => null,
            ]);

            $this->logMessage(
                $lead,
                LeadMessage::CHANNEL_NOTE,
                'Converted to student ' . $student->reference_no . ' — ' . $student->name_en
                . ' (' . ($batchId ? 'admitted to batch' : 'application pending review') . ')',
                'logged'
            );

            return $student;
        });

        return redirect()->route('crm.leads.show', $lead)
            ->with('success', 'Lead converted to student ' . $student->reference_no . ' (' . $student->name_en . '). '
                . ($student->batch_id ? 'The student is admitted.' : 'The application is awaiting batch assignment.'));
    }

    public function reminders(Request $request)
    {
        $this->guard();

        $instituteId = $this->instituteId();
        $bucket = $request->input('bucket', 'all');

        $pending = LeadFollowUp::query()
            ->where('institute_id', $instituteId)
            ->where('status', LeadFollowUp::STATUS_PENDING);

        $query = (clone $pending)->with(['lead.course', 'user']);

        if ($bucket === 'overdue') {
            $query->where('scheduled_at', '<', now()->startOfDay());
        } elseif ($bucket === 'today') {
            $query->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()]);
        } elseif ($bucket === 'upcoming') {
            $query->where('scheduled_at', '>', now()->endOfDay());
        }

        if ($request->filled('assigned')) {
            $query->where('user_id', $request->assigned);
        }

        $followUps = $query->orderBy('scheduled_at')->get();

        $counts = [
            'all' => (clone $pending)->count(),
            'overdue' => (clone $pending)->where('scheduled_at', '<', now()->startOfDay())->count(),
            'today' => (clone $pending)->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()])->count(),
            'upcoming' => (clone $pending)->where('scheduled_at', '>', now()->endOfDay())->count(),
        ];

        $staff = $this->staff();

        return view('crm.reminders', compact('followUps', 'counts', 'bucket', 'staff'));
    }

    public function exportCsv(Request $request)
    {
        $this->guard();

        $query = $this->leadQuery()->with(['course', 'assignedTo']);

        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $query->when($request->filled('source'), fn ($q) => $q->where('source', $request->source));
        $query->when($request->filled('q'), function ($q) use ($request) {
            $term = '%' . trim((string) $request->q) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'ilike', $term)->orWhere('phone', 'ilike', $term);
            });
        });

        $leads = $query->orderByDesc('created_at')->get();
        $filename = 'leads-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($leads) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Name', 'Phone', 'Email', 'Source', 'Status', 'Interest', 'Course', 'Assigned To', 'Next Follow-up', 'Converted Student', 'Created At']);

            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->name,
                    $lead->phone,
                    $lead->email,
                    $lead->sourceLabel(),
                    $lead->statusLabel(),
                    $lead->interest,
                    $lead->course?->name,
                    $lead->assignedTo?->name,
                    optional($lead->next_follow_up_at)->format('Y-m-d H:i'),
                    $lead->convertedStudent?->reference_no,
                    optional($lead->created_at)->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLead(Request $request, ?Lead $lead = null): array
    {
        $instituteId = $this->instituteId();

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'source' => ['required', Rule::in(array_keys(Lead::SOURCES))],
            'status' => ['nullable', Rule::in(array_keys(Lead::MANUAL_STATUSES))],
            'interest' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('institute_id', $instituteId)],
            'course_id' => ['nullable', Rule::exists('courses', 'id')->where('institute_id', $instituteId)],
            'batch_id' => ['nullable', Rule::exists('batches', 'id')->where('institute_id', $instituteId)],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('institute_id', $instituteId)],
            'next_follow_up_at' => ['nullable', 'date'],
            'follow_up_type' => ['nullable', Rule::in(array_keys(LeadFollowUp::TYPES))],
            'follow_up_notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function uniqueReferenceNo(): string
    {
        do {
            $reference = 'STP-' . strtoupper(Str::random(8));
        } while (Student::where('reference_no', $reference)->exists());

        return $reference;
    }
}
