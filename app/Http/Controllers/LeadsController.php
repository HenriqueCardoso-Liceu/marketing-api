<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\leads;
use Illuminate\Http\Request;

class LeadsController extends Controller
{
    //
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'mobile_phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:leads,email',
            'city' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date_format:d/m/Y',
            'utm_source' => 'nullable|string|max:255',
            'utm_medium' => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_term' => 'nullable|string|max:255',
            'utm_content' => 'nullable|string|max:255',
            'referrer' => 'nullable|string|max:255',
            'landing_page' => 'nullable|string|max:255',
        ]);

        // Converte data de DD/MM/YYYY para YYYY-MM-DD
        if (!empty($validatedData['date_of_birth'])) {
            $validatedData['date_of_birth'] = \DateTime::createFromFormat('d/m/Y', $validatedData['date_of_birth'])->format('Y-m-d');
        }

        $lead = leads::create($validatedData);

        return response()->json(['message' => 'Lead created successfully', 'lead' => $lead], 201);
    }

    public function index()
    {
        $leads = leads::all();
        return response()->json($leads);
    }
}
