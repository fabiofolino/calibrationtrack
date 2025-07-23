<?php

namespace App\Http\Controllers;

use App\Models\Gage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        return Inertia::render('Calendar/Index');
    }

    public function events(Request $request)
    {
        $this->authorize('viewAny', Gage::class);

        $gages = Gage::with(['department', 'calibrationRecords'])
            ->whereHas('department', function ($q) {
                $q->where('company_id', auth()->user()->company_id);
            })
            ->whereNotNull('next_due_date')
            ->get();

        $events = $gages->map(function ($gage) {
            $daysUntilDue = now()->diffInDays($gage->next_due_date, false);
            
            return [
                'id' => $gage->id,
                'title' => $gage->name . ' (' . $gage->serial_number . ')',
                'start' => $gage->next_due_date->format('Y-m-d'),
                'backgroundColor' => $this->getEventColor($daysUntilDue),
                'borderColor' => $this->getEventColor($daysUntilDue),
                'extendedProps' => [
                    'gage' => $gage,
                    'department' => $gage->department->name,
                    'status' => $this->getStatus($daysUntilDue),
                    'days_until_due' => $daysUntilDue,
                ]
            ];
        });

        return response()->json($events);
    }

    private function getEventColor($daysUntilDue): string
    {
        if ($daysUntilDue < 0) {
            return '#dc2626'; // red - overdue
        } elseif ($daysUntilDue <= 7) {
            return '#f59e0b'; // amber - due soon
        } else {
            return '#10b981'; // green - on schedule
        }
    }

    private function getStatus($daysUntilDue): string
    {
        if ($daysUntilDue < 0) {
            return 'overdue';
        } elseif ($daysUntilDue <= 7) {
            return 'due_soon';
        } else {
            return 'on_schedule';
        }
    }
}
