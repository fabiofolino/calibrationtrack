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
    public function index()
    {
        $this->authorize('viewAny', Gage::class);

        $gages = auth()->user()->company->gages()->with(['department', 'calibrationRecords' => function ($query) {
            $query->latest()->limit(1);
        }])->get();
        
        return Inertia::render('Gages/Index', [
            'gages' => $gages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Gage::class);

        $departments = auth()->user()->company->departments()->get();

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

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:gages',
            'frequency_days' => 'required|integer|min:1',
        ]);

        // Verify department belongs to user's company
        $department = auth()->user()->company->departments()->findOrFail($request->department_id);

        Gage::create($request->only([
            'department_id', 'name', 'serial_number', 'frequency_days'
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
        
        $gage->load(['department', 'calibrationRecords' => function ($query) {
            $query->with('user')->latest();
        }]);

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
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:gages,serial_number,' . $gage->id,
            'frequency_days' => 'required|integer|min:1',
        ]);

        // Verify department belongs to user's company
        $department = auth()->user()->company->departments()->findOrFail($request->department_id);

        $gage->update($request->only([
            'department_id', 'name', 'serial_number', 'frequency_days'
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
