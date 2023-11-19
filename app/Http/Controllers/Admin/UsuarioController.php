<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;

class UsuarioController extends Controller
{
    public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => UsuarioResource::collection(Usuario::latest()->simplePaginate(10))
        ]);
    }
}
