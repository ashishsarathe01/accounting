<?php

namespace App\Http\Controllers\LandingPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function about()
    {
        return view('about');
    }

    public function features() // ✅ plural to match route
    {
        return view('feature'); // make sure file = features.blade.php
    }

    public function ContactUs()
    {
        return view('contactUs');
    }

    public function pricing()
    {
        return view('pricing'); // ✅ FIXED
    }

    public function welcome()
    {
        return view('welcome');
    }
}