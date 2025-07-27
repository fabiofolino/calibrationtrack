<?php

namespace App\Http\Controllers;

use App\Models\Gage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GageController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Gage::class);

        $company = auth()->user()->company;
        
        // Start with base query
        $query = $company->gages()->with(['department', 'calibrationRecords' => function ($q) {
            $q->latest()->limit(1);
        }]);

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('gage_id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('custodian', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by calibration status
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'overdue') {
                $query->whereRaw('next_due_date < CURDATE()');
            } elseif ($status === 'due_soon') {
                $query->whereRaw('next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)');
            } elseif ($status === 'on_schedule') {
                $query->whereRaw('next_due_date > DATE_ADD(CURDATE(), INTERVAL 30 DAY)');
            } elseif ($status === 'never_calibrated') {
                $query->whereDoesntHave('calibrationRecords');
            }
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'gage_id');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $validSortFields = ['gage_id', 'description', 'location', 'custodian', 'next_due_date'];
        if (in_array($sortBy, $validSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $gages = $query->get();

        // Add status information to each gage
        $gages->each(function ($gage) {
            $gage->status = $gage->getCalibrationStatus();
            $gage->status_color = $gage->getCalibrationStatusColor();
            $gage->days_until_due = $gage->getDaysUntilDue();
        });

        // Get departments for filter dropdown
        $departments = $company->departments()->orderBy('name')->get();
        
        // Get unique locations for filter dropdown
        $locations = $company->gages()->distinct()->pluck('location')->filter()->sort()->values();
        
        return Inertia::render('Gages/Index', [
            'gages' => $gages,
            'departments' => $departments,
            'locations' => $locations,
            'filters' => $request->only(['search', 'department_id', 'status', 'location', 'sort_by', 'sort_order']),
            'subscriptionInfo' => [
                'hasActiveSubscription' => $company->hasActiveSubscription(),
                'onTrial' => $company->onTrial(),
                'canAddGages' => $company->canAddGages(),
                'gageCount' => $company->getGageCount(),
                'gageLimit' => $company->getGageLimit(),
                'hasReachedLimit' => $company->hasReachedGageLimit(),
            ],
            'hasDepartments' => $company->departments()->exists(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Gage::class);

        $company = auth()->user()->company;
        
        // Check if company has any departments first
        if (!$company->departments()->exists()) {
            return redirect()->route('departments.create')
                ->with('error', 'You must create at least one department before adding gages.');
        }
        
        // Check if company can add more gages
        if (!$company->canAddGages()) {
            return redirect()->route('subscription.subscribe')
                ->with('error', 'You have reached the limit of 10 gages. Please subscribe to add more gages.');
        }

        $departments = $company->departments()->get();

        return Inertia::render('Gages/Create', [
            'departments' => $departments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Gage::class);

        $company = auth()->user()->company;
        
        // Check if company has any departments first
        if (!$company->departments()->exists()) {
            return redirect()->route('departments.create')
                ->with('error', 'You must create at least one department before adding gages.');
        }
        
        // Check if company can add more gages
        if (!$company->canAddGages()) {
            return redirect()->route('subscription.subscribe')
                ->with('error', 'You have reached the limit of 10 gages. Please subscribe to add more gages.');
        }

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'gage_id' => 'required|string|max:255|unique:gages',
            'description' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'serial_number' => 'required|string|max:255|unique:gages',
            'location' => 'required|string|max:255',
            'custodian' => 'required|string|max:255',
            'frequency_days' => 'required|integer|min:1',
        ]);

        // Verify department belongs to user's company
        $department = $company->departments()->findOrFail($request->department_id);

        Gage::create($request->only([
            'department_id', 'gage_id', 'description', 'model', 'manufacturer', 
            'serial_number', 'location', 'custodian', 'frequency_days'
        ]));

        return redirect()->route('gages.index')
            ->with('success', 'Gage created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Gage $gage)
    {
        $this->authorize('view', $gage);
        
        $gage->load([
            'department', 
            'calibrationRecords' => function ($query) {
                $query->with('user')->latest();
            },
            'checkouts' => function ($query) {
                $query->with('user')->latest()->limit(5);
            },
            'toleranceRecords' => function ($query) {
                $query->with(['user', 'calibrationRecord'])->latest();
            }
        ]);

        // Add status information
        $gage->status = $gage->getCalibrationStatus();
        $gage->status_color = $gage->getCalibrationStatusColor();
        $gage->days_until_due = $gage->getDaysUntilDue();
        $gage->current_checkout = $gage->currentCheckout()?->load('user');
        $gage->is_checked_out = $gage->isCheckedOut();

        return Inertia::render('Gages/Show', [
            'gage' => $gage,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gage $gage)
    {
        $this->authorize('update', $gage);

        $departments = auth()->user()->company->departments()->get();

        return Inertia::render('Gages/Edit', [
            'gage' => $gage,
            'departments' => $departments,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gage $gage)
    {
        $this->authorize('update', $gage);

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'gage_id' => 'required|string|max:255|unique:gages,gage_id,' . $gage->id,
            'description' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'serial_number' => 'required|string|max:255|unique:gages,serial_number,' . $gage->id,
            'location' => 'required|string|max:255',
            'custodian' => 'required|string|max:255',
            'frequency_days' => 'required|integer|min:1',
        ]);

        // Verify department belongs to user's company
        $department = auth()->user()->company->departments()->findOrFail($request->department_id);

        $gage->update($request->only([
            'department_id', 'gage_id', 'description', 'model', 'manufacturer',
            'serial_number', 'location', 'custodian', 'frequency_days'
        ]));

        return redirect()->route('gages.index')
            ->with('success', 'Gage updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gage $gage)
    {
        $this->authorize('delete', $gage);

        $gage->delete();

        return redirect()->route('gages.index')
            ->with('success', 'Gage deleted successfully.');
    }
}
