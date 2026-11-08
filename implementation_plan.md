# Implementation Plan - Admin Dashboard Upgrade

Upgrade the Admin Dashboard in the **STP Management System** (`stp-saas`) with comprehensive financial widgets, damaged resource alerts, quick actions toolbar, low attendance flags, and visual analytics charts.

## User Review Required

> [!IMPORTANT]
> - **Financial Summaries**: The dashboard will calculate current month income & expenses directly from the `transactions` table for the user's institute.
> - **Chart Visualization**: Chart.js will be loaded via CDN to display interactive Financial Trends (Income vs Expense) and Course-wise Enrollment Charts.
> - **Status Handling Fix**: Attendance warning filters will account for both `admitted` and `active` student statuses.

## Proposed Changes

### Routes & Controller Logic

#### [MODIFY] [routes/web.php](file:///d:/php/ai/stp-saas/routes/web.php)
- Upgrade the `/dashboard` route closure to compute:
  - Total Monthly Income & Expenses (for current month).
  - Total Damaged / Maintenance Resources count & list.
  - Total Trainers / Instructors count.
  - Monthly financial breakdown for charts (last 6 months income vs expense).
  - Course-wise student distribution for pie/bar chart.
  - Attendance low-presence warning filter updated to check `status` in `['admitted', 'active']`.

---

### Dashboard View

#### [MODIFY] [dashboard.blade.php](file:///d:/php/ai/stp-saas/resources/views/dashboard.blade.php)
- Add **Financial Metric Cards** (Current Month Income, Monthly Expense, Net Cashflow balance).
- Add **Quick Actions Toolbar** (Quick buttons for Add Batch, Review Applications, Record Income/Expense, Send SMS Notice, Add Resource).
- Add **Damaged / Maintenance Lab Resource Alert Widget** (highlighting hardware needing repairs).
- Add **Visual Analytics Section**:
  - Financial Performance Chart (Income vs Expenses last 6 months).
  - Course-wise Student Distribution Chart.
- Maintain existing Project Cards, Low Attendance Warnings (&lt;80%), and Recent Students Table.

## Verification Plan

### Automated Tests
- Run `php artisan config:clear` and check for any PHP syntax errors using `php artisan route:list`.

### Manual Verification
- Access `/dashboard` as Super Admin (`admin@stp.com`).
- Verify all metric cards, quick action links, financial totals, damaged inventory alerts, and charts render cleanly and responsively.
