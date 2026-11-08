<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Project;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    private function getProjectId()
    {
        return session('current_project_id', optional(Project::where('institute_id', Auth::user()->institute_id)->first())->id);
    }

    private function projectBatches()
    {
        return Batch::where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->orderBy('name')
            ->get();
    }

    private function projectTrainers()
    {
        return User::where('institute_id', Auth::user()->institute_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function projectRooms()
    {
        return Schedule::where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->where('status', 'active')
            ->whereNotNull('room')
            ->distinct()
            ->orderBy('room')
            ->get('room')
            ->pluck('room');
    }

    /**
     * Shared scope: schedules of the current institute + project with filters.
     */
    private function scopedSchedules(Request $request): Collection
    {
        return Schedule::with(['batch.course', 'trainer'])
            ->where('institute_id', Auth::user()->institute_id)
            ->where('project_id', $this->getProjectId())
            ->where('status', 'active')
            ->when($request->batch_id, fn ($q) => $q->where('batch_id', $request->batch_id))
            ->when($request->trainer_id, fn ($q) => $q->where('trainer_id', $request->trainer_id))
            ->when($request->room, fn ($q) => $q->where('room', $request->room))
            ->get();
    }

    /**
     * Weekly data structure: day (0-6) => schedules sorted by start time.
     */
    private function weekView(Collection $schedules): array
    {
        $week = [];

        foreach (range(0, 6) as $day) {
            $week[$day] = $schedules
                ->where('day_of_week', $day)
                ->sortBy('start_time')
                ->values();
        }

        return $week;
    }

    /**
     * IDs of every schedule involved in an actual clash
     * (same day + overlapping time + shared room/trainer/batch).
     */
    private function conflictIds(Collection $schedules): array
    {
        $conflicted = [];
        $byDay = $schedules->groupBy('day_of_week');

        foreach ($byDay as $day => $dayList) {
            $items = $dayList->values();
            $count = $items->count();

            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($items[$i]->clashes($items[$j])) {
                        $conflicted[$items[$i]->id] = 1;
                        $conflicted[$items[$j]->id] = 1;
                    }
                }
            }
        }

        return array_keys($conflicted);
    }

    public function index(Request $request)
    {
        $batches = $this->projectBatches();
        $trainers = $this->projectTrainers();
        $rooms = $this->projectRooms();
        $project = Project::find($this->getProjectId());

        $schedules = $this->scopedSchedules($request);
        $week = $this->weekView($schedules);
        $conflicts = $this->conflictIds($schedules);

        return view('schedules.index', compact('batches', 'trainers', 'rooms', 'project', 'schedules', 'week', 'conflicts'));
    }

    public function create()
    {
        $batches = $this->projectBatches();
        $trainers = $this->projectTrainers();
        $project = Project::find($this->getProjectId());
        $schedule = null;

        return view('schedules.create', compact('batches', 'trainers', 'project', 'schedule'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedSchedule($request, null);

        Schedule::create([
            'institute_id' => Auth::user()->institute_id,
            'project_id' => Batch::find($data['batch_id'])->project_id ?? $this->getProjectId(),
            ...$data,
        ]);

        return redirect()->route('schedules.index')->with('success', 'Class schedule added to the routine.');
    }

    public function edit(Schedule $schedule)
    {
        abort_if($schedule->institute_id !== Auth::user()->institute_id, 403);

        $batches = $this->projectBatches();
        $trainers = $this->projectTrainers();
        $project = Project::find($this->getProjectId());

        return view('schedules.edit', compact('schedule', 'batches', 'trainers', 'project'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        abort_if($schedule->institute_id !== Auth::user()->institute_id, 403);

        $data = $this->validatedSchedule($request, $schedule);

        $schedule->update([
            ...$data,
            'project_id' => Batch::find($data['batch_id'])->project_id ?? $this->getProjectId(),
        ]);

        return redirect()->route('schedules.index')->with('success', 'Class schedule updated.');
    }

    public function destroy(Schedule $schedule)
    {
        abort_if($schedule->institute_id !== Auth::user()->institute_id, 403);

        $schedule->delete();

        return redirect()->route('schedules.index')->with('success', 'Class schedule removed.');
    }

    public function print(Request $request)
    {
        $project = Project::find($this->getProjectId());

        $schedules = $this->scopedSchedules($request);
        $week = $this->weekView($schedules);
        $conflicts = $this->conflictIds($schedules);

        return view('schedules.print', compact('project', 'week', 'conflicts'));
    }

    /**
     * Validate + reject time clashes for the candidate slot.
     */
    private function validatedSchedule(Request $request, ?Schedule $ignore): array
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|exists:batches,id',
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:100',
            'trainer_id' => 'nullable|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $batch = Batch::find($request->batch_id);
        abort_if($batch && $batch->institute_id !== Auth::user()->institute_id, 403);

        if ($request->trainer_id) {
            $trainer = User::find($request->trainer_id);
            abort_if($trainer && $trainer->institute_id !== Auth::user()->institute_id, 403);
        }

        // Custom clash rule: runs after the built-in rules, so the error
        // survives $validator->validate() (manual errors() additions are wiped).
        $validator->after(function ($validator) use ($request, $ignore) {
            if ($validator->errors()->isNotEmpty()) {
                return; // base rules already failed; skip the clash query
            }

            $clashes = Schedule::where('institute_id', Auth::user()->institute_id)
                ->where('day_of_week', $request->day_of_week)
                ->where('status', 'active')
                ->where('start_time', '<', $request->end_time)
                ->where('end_time', '>', $request->start_time)
                ->when($ignore, fn ($q) => $q->where('id', '!=', $ignore->id))
                ->where(function ($q) use ($request) {
                    if ($request->room) {
                        $q->orWhere('room', $request->room);
                    }
                    if ($request->trainer_id) {
                        $q->orWhere('trainer_id', $request->trainer_id);
                    }
                    $q->orWhere('batch_id', $request->batch_id);
                })
                ->with(['batch', 'trainer'])
                ->get();

            if ($clashes->isNotEmpty()) {
                $details = $clashes->map(fn ($s) => implode(' • ', array_filter([
                    'Batch: '.$s->batch?->name,
                    $s->room ? 'Room: '.$s->room : null,
                    $s->trainer ? 'Trainer: '.$s->trainer->name : null,
                ])).' ('.Schedule::DAYS[$s->day_of_week].' '.$s->timeRange().')')->implode('; ');

                $validator->errors()->add('day_of_week', 'Time clash detected with: '.$details.'. Please choose a different time slot, room, trainer or batch.');
            }
        });

        return $validator->validate();
    }
}