<?php

namespace App\Http\Controllers;

use App\Models\Gage;
use App\Models\CalibrationRecord;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with analytics.
     */
    public function index()
    {
        $user = auth()->user();
        $company = $user->company;

        // Get gages by status
        $gages = $company->gages()->with(['department', 'calibrationRecords' => function ($query) {
            $query->latest()->limit(1);
        }])->get();

        $gagesByStatus = [
            'on_schedule' => 0,
            'due_soon' => 0,
            'overdue' => 0,
        ];

        foreach ($gages as $gage) {
            $status = $gage->getCalibrationStatus();
            if (isset($gagesByStatus[$status])) {
                $gagesByStatus[$status]++;
            }
        }

        // Calibration records this month
        $calibrationRecordsThisMonth = CalibrationRecord::whereHas('gage.department', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->whereMonth('calibrated_at', Carbon::now()->month)
        ->whereYear('calibrated_at', Carbon::now()->year)
        ->count();

        // Most recently calibrated gages
        $recentlyCalibrated = CalibrationRecord::whereHas('gage.department', function ($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->with(['gage.department', 'user'])
        ->latest('calibrated_at')
        ->limit(5)
        ->get();

        // Department with most open gages (overdue + due soon)
        $departmentStats = $company->departments()
            ->withCount(['gages as total_gages'])
            ->with(['gages' => function ($query) {
                $query->select('id', 'department_id', 'next_due_date');
            }])
            ->get()
            ->map(function ($department) {
                $openGages = 0;
                foreach ($department->gages as $gage) {
                    $status = $gage->getCalibrationStatus();
                    if (in_array($status, ['overdue', 'due_soon'])) {
                        $openGages++;
                    }
                }
                return [
                    'name' => $department->name,
                    'total_gages' => $department->total_gages,
                    'open_gages' => $openGages,
                ];
            })
            ->sortByDesc('open_gages')
            ->first();

        // Upcoming calibrations (next 30 days)
        $upcomingCalibrations = $gages
            ->filter(function ($gage) {
                if (!$gage->next_due_date) return false;
                $daysUntilDue = Carbon::now()->diffInDays($gage->next_due_date, false);
                return $daysUntilDue >= 0 && $daysUntilDue <= 30;
            })
            ->sortBy('next_due_date')
            ->take(10)
            ->map(function ($gage) {
                return [
                    'id' => $gage->id,
                    'name' => $gage->name,
                    'serial_number' => $gage->serial_number,
                    'department_name' => $gage->department->name,
                    'next_due_date' => $gage->next_due_date,
                    'days_until_due' => $gage->getDaysUntilDue(),
                    'status' => $gage->getCalibrationStatus(),
                    'status_color' => $gage->getCalibrationStatusColor(),
                ];
            })
            ->values();

        return Inertia::render('Dashboard', [
            'analytics' => [
                'gages_by_status' => $gagesByStatus,
                'calibration_records_this_month' => $calibrationRecordsThisMonth,
                'recently_calibrated' => $recentlyCalibrated,
                'department_with_most_open' => $departmentStats,
                'upcoming_calibrations' => $upcomingCalibrations,
                'total_gages' => $gages->count(),
            ]
        ]);
    }
}