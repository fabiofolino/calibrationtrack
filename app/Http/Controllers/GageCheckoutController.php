<?php

namespace App\Http\Controllers;

use App\Models\Gage;
use App\Models\GageCheckout;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GageCheckoutController extends Controller
{
    use AuthorizesRequests;

    /**
     * Check out a gage
     */
    public function checkout(Request $request, Gage $gage)
    {
        $this->authorize('view', $gage);

        // Verify the gage isn't already checked out
        if ($gage->isCheckedOut()) {
            return back()->withErrors(['error' => 'This gage is already checked out.']);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        GageCheckout::create([
            'gage_id' => $gage->id,
            'user_id' => auth()->id(),
            'checked_out_at' => now(),
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Gage checked out successfully.');
    }

    /**
     * Check in a gage
     */
    public function checkin(Request $request, Gage $gage)
    {
        $this->authorize('view', $gage);

        $currentCheckout = $gage->currentCheckout();
        
        if (!$currentCheckout) {
            return back()->withErrors(['error' => 'This gage is not currently checked out.']);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $currentCheckout->checkIn($request->notes);

        return back()->with('success', 'Gage checked in successfully.');
    }

    /**
     * Display checkout history for a gage
     */
    public function history(Gage $gage)
    {
        $this->authorize('view', $gage);

        $checkouts = $gage->checkouts()->with('user')->latest()->get();

        return Inertia::render('Gages/CheckoutHistory', [
            'gage' => $gage->load('department'),
            'checkouts' => $checkouts,
        ]);
    }
}
