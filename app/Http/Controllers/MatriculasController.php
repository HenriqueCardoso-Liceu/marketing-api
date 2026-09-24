<?php

namespace App\Http\Controllers;

use App\Models\ColItaquaMatriculas;
use Illuminate\Http\Request;

class MatriculasController extends Controller
{
    //
    public function storeColItaqua(Request $request)
    {
        $validatedData = $request->validate([
            'responsible_name' => 'required|string|max:150',
            'mobile_phone' => 'required|string|max:25',
            'interest' => 'required|string|max:150',
            'utm_source' => 'nullable|string|max:255',
            'utm_medium' => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_term' => 'nullable|string|max:255',
            'utm_content' => 'nullable|string|max:255',
            'referrer' => 'nullable|string|max:255',
            'landing_page' => 'nullable|string|max:255',
        ],[
            'responsible_name.required' => 'O campo "Nome do Responsável" é obrigatório.',
            'mobile_phone.required' => 'O campo "Telefone" é obrigatório.',
            'interest.required' => 'O campo "Série de interesse" é obrigatório.',
        ]);

        $matricula = ColItaquaMatriculas::create($validatedData);

        return response()->json(['message' => 'Cadastro registrado com sucesso!', 'lead' => $matricula], 201);
    }
}
