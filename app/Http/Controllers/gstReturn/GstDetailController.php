<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GSTR2A;
use App\Models\gstr2aInvoice;
use App\Models\gstr2aInvoiceItem;
use App\Models\GSTR2B;
use App\Models\gstr2bInvoice;
use App\Models\gstr2bInvoiceItem;
use App\Models\gstToken;
use App\Models\Companies;
use App\Models\Accounts;
use App\Models\Purchase;
use Session;
use DB;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
class GstDetailController extends Controller
{
    public function index(){
        
    }
    
}
