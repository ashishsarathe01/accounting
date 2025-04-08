<?php

namespace App\Http\Controllers\display;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfitLossController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return view('display/profitLoss');
    }
}
