<?php

namespace App\Http\Controllers;
use App\Models\Client;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         // Fetch all clients from the database, ordered by the newest first
        $clients = Client::latest()->get();

        // Return the 'index' view and pass the clients data to it
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Simply return the view that contains the form to create a client
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'date_of_birth' => 'required|date',
            'care_plan' => 'nullable|string',
        ]);

        // 2. Create a new client record
        Client::create($validated);

        // 3. Redirect back to the client list with a success message
        return redirect()->route('clients.index')
                         ->with('success', 'Client added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
