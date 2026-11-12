<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $version = env('REACT_APP_VERSION', env('APP_VERSION', 'v1.3.2'));

        return view('home', compact('version'));
    }
}
