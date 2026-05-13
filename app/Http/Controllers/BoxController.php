<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BoxController extends Controller
{
     public function index()
    {
        return view('box_calculator.index');
    }

    public function advanceindex()
    {
        return view('box_calculator.advanceindex');
    }
}
