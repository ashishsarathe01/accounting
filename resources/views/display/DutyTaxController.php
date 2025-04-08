<?php

namespace App\Http\Controllers\display;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DutyTaxController extends Controller
{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */

     public function index()
     {
         return view('display/dutytax');
     }
 }
 