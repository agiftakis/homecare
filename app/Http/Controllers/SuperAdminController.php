<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index()
    {
        // Eager load the 'owner' relationship to prevent N+1 query issues
        $agencies = Agency::with('owner')->latest()->get();

        return view('superadmin.dashboard', compact('agencies'));
    }
}