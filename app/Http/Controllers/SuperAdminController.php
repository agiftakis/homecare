<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Client;
use App\Models\Caregiver;
use App\Models\Concerns\BelongsToAgency;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index()
    {
        $agencies = Agency::with('owner')->latest()->get();
        return view('superadmin.dashboard', compact('agencies'));
    }

    /**
     * Display a listing of all clients from all agencies.
     */
    public function clientsIndex()
    {
        // Use withoutGlobalScope to bypass the BelongsToAgency scope
        $clients = Client::withoutGlobalScope(BelongsToAgency::class)->with('agency')->latest()->get();
        return view('superadmin.clients.index', compact('clients'));
    }

    /**
     * Display the specified client.
     */
    public function clientShow(Client $client)
    {
        // No need to bypass scope here, as route model binding already fetched the client
        return view('superadmin.clients.show', compact('client'));
    }

    /**
     * Display a listing of all caregivers from all agencies.
     */
    public function caregiversIndex()
    {
        // Use withoutGlobalScope to bypass the BelongsToAgency scope
        $caregivers = Caregiver::withoutGlobalScope(BelongsToAgency::class)->with('agency')->latest()->get();
        return view('superadmin.caregivers.index', compact('caregivers'));
    }

    /**
     * Display the specified caregiver.
     */
    public function caregiverShow(Caregiver $caregiver)
    {
        // No need to bypass scope here, as route model binding already fetched the caregiver
        return view('superadmin.caregivers.show', compact('caregiver'));
    }
}
