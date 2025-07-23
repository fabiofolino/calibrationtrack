<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    /**
     * Display the audit logs (admin only)
     */
    public function index(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can view audit logs.');
        }

        $perPage = $request->get('per_page', 25);
        $modelFilter = $request->get('model');
        $userFilter = $request->get('user');

        // Get activities for the user's company only
        $query = Activity::query()
            ->whereHasMorph('subject', [
                \App\Models\Gage::class,
                \App\Models\CalibrationRecord::class,
                \App\Models\GageCheckout::class,
            ], function ($query, $type) {
                if ($type === \App\Models\Gage::class) {
                    $query->whereHas('department', function ($q) {
                        $q->where('company_id', auth()->user()->company_id);
                    });
                } elseif ($type === \App\Models\CalibrationRecord::class) {
                    $query->whereHas('gage.department', function ($q) {
                        $q->where('company_id', auth()->user()->company_id);
                    });
                } elseif ($type === \App\Models\GageCheckout::class) {
                    $query->whereHas('gage.department', function ($q) {
                        $q->where('company_id', auth()->user()->company_id);
                    });
                }
            })
            ->with(['causer', 'subject'])
            ->latest();

        // Apply filters
        if ($modelFilter) {
            $query->where('subject_type', 'like', '%' . $modelFilter . '%');
        }

        if ($userFilter) {
            $query->where('causer_id', $userFilter);
        }

        $activities = $query->paginate($perPage);

        // Get available models for filter
        $availableModels = [
            'Gage' => 'App\Models\Gage',
            'CalibrationRecord' => 'App\Models\CalibrationRecord',
            'GageCheckout' => 'App\Models\GageCheckout',
        ];

        // Get users in company for filter
        $companyUsers = auth()->user()->company->users()->select('id', 'name')->get();

        return Inertia::render('Admin/AuditLog', [
            'activities' => $activities,
            'filters' => [
                'model' => $modelFilter,
                'user' => $userFilter,
                'per_page' => $perPage,
            ],
            'availableModels' => $availableModels,
            'companyUsers' => $companyUsers,
        ]);
    }

    /**
     * Show detailed view of a specific audit log entry
     */
    public function show(Activity $activity)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can view audit logs.');
        }

        // Verify the activity belongs to the user's company
        $belongsToCompany = false;
        
        if ($activity->subject) {
            if ($activity->subject instanceof \App\Models\Gage) {
                $belongsToCompany = $activity->subject->department->company_id === auth()->user()->company_id;
            } elseif ($activity->subject instanceof \App\Models\CalibrationRecord) {
                $belongsToCompany = $activity->subject->gage->department->company_id === auth()->user()->company_id;
            } elseif ($activity->subject instanceof \App\Models\GageCheckout) {
                $belongsToCompany = $activity->subject->gage->department->company_id === auth()->user()->company_id;
            }
        }

        if (!$belongsToCompany) {
            abort(404, 'Audit log not found.');
        }

        $activity->load(['causer', 'subject']);

        return Inertia::render('Admin/AuditLogDetail', [
            'activity' => $activity,
        ]);
    }
}