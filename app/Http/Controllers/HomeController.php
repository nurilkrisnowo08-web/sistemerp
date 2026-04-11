<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        // Hitung total user
        $totalUser = User::count();

        return view('dashboard', compact('totalUser'));
    }
}
