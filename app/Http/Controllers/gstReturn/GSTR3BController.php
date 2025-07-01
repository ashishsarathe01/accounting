<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GSTR3BController extends Controller
{
    public function index(Request $request){
        return view('gstReturn.GSTR3B');
    }


}
