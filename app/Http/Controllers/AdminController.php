<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can access the admin panel.');
        }

        $company = auth()->user()->company;

        // Get some basic stats for the admin dashboard
        $stats = [
            'total_users' => $company->users()->count(),
            'active_users' => $company->users()->where('is_suspended', false)->count(),
            'suspended_users' => $company->users()->where('is_suspended', true)->count(),
            'admin_users' => $company->users()->where('role', 'admin')->count(),
            'total_gages' => $company->gages()->count(),
            'total_departments' => $company->departments()->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}