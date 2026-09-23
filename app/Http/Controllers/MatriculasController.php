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
        ],[
            'responsible_name.required' => 'O campo "Nome do Responsável" é obrigatório.',
            'mobile_phone.required' => 'O campo "Telefone" é obrigatório.',
            'interest.required' => 'O campo "Série de interesse" é obrigatório.',
        ]);

        $matricula = ColItaquaMatriculas::create($validatedData);

        return response()->json([
            'success' => true,
            'data' => $matricula,
            'message' => 'Dados salvos com sucesso, em breve entraremos em contato!'
        ], 201);
    }
}
