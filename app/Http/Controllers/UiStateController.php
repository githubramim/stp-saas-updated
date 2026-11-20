<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Keeps UI state (open create modals, list filters) in the session so page
 * URLs stay clean — no `?key=value` query strings anywhere in the app.
 */
class UiStateController extends Controller
{
    /** Route names that may receive session-backed filter values. */
    private const FILTER_ROUTES = [
        'accounts.expenses', 'accounts.profit-loss', 'accounts.profit-loss.csv', 'accounts.salaries',
        'attendance.index', 'attendance.monthly-report', 'attendance.monthly-report.csv',
        'attendance.project-report', 'attendance.project-report.csv',
        'branches.index', 'certificates.index', 'crm.leads', 'crm.leads.export', 'crm.reminders',
        'evaluations.index', 'evaluations.report', 'exams.index', 'export.transactions',
        'hr.attendance', 'hr.employees', 'hr.leaves', 'hr.payroll',
        'id-cards.editor', 'id-cards.index', 'id-cards.print',
        'notices.index', 'notifications.index',
        'pbtm.assets', 'pbtm.index', 'pbtm.placements', 'pbtm.reports', 'pbtm.reports.csv',
        'pbtm.stipends', 'pbtm.trainees', 'schedules.index', 'schedules.print',
        'study-materials.index', 'transactions.index',
    ];

    /** Area key => route the "New" button's index page lives on. */
    private const CREATE_AREAS = [
        'accounts.expenses' => 'accounts.expenses',
        'attendance' => 'attendance.index',
        'batches' => 'batches.index',
        'certificates' => 'certificates.index',
        'courses' => 'courses.index',
        'crm.leads' => 'crm.leads',
        'evaluations' => 'evaluations.index',
        'exams' => 'exams.index',
        'notices' => 'notices.index',
        'notifications' => 'notifications.index',
        'pbtm.assets' => 'pbtm.assets',
        'pbtm.placements' => 'pbtm.placements',
        'pbtm.stipends' => 'pbtm.stipends',
        'projects' => 'projects.index',
        'resources' => 'resources.index',
        'schedules' => 'schedules.index',
        'study-materials' => 'study-materials.index',
        'transactions' => 'transactions.index',
    ];

    /** Flash a marker, then land on the clean index URL with the modal open. */
    public function openCreate(string $area)
    {
        abort_unless(isset(self::CREATE_AREAS[$area]), 404);

        session()->flash('open_create', $area);

        return redirect()->route(self::CREATE_AREAS[$area]);
    }

    /**
     * Store filter values in the session and return to the clean URL.
     * Params arrive as path segments like `period:this_month/batch_id:6`,
     * never as a `?query=string`.
     */
    public function setParams(Request $request, string $area, ?string $params = null)
    {
        abort_unless(in_array($area, self::FILTER_ROUTES, true), 404);

        // POST filter forms send their fields directly in the body.
        $values = $request->except(['_token', '_method']);

        foreach (explode('/', trim((string) $params, '/')) as $pair) {
            if ($pair === '') {
                continue;
            }

            [$key, $value] = array_pad(explode(':', $pair, 2), 2, '');
            $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);

            if ($key !== '') {
                $values[$key] = rawurldecode($value);
            }
        }

        if ($values === []) {
            session()->forget('clean_params.'.$area);
        } else {
            session()->put('clean_params.'.$area, $values);
        }

        return redirect()->route($area);
    }
}
