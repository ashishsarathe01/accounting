<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboradController extends Controller{
   public function index(){
       return view("admin-module.dashboard.dashboard");
   }
}
