<?php

namespace App\Http\Controllers\display;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AccountHeading;
use App\Models\AccountGroups;
use App\Models\Accounts;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\ClosingStock;
use App\Models\Journal;
use App\Models\AccountProduction;
use App\Models\AccountProductionDetail;
use App\Models\ItemAverageDetail;
use App\Models\DeckleProcess;
use App\Helpers\CommonHelper;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Session;
use DB;
class TestController extends Controller{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
    public function index(){
        //CommonHelper::RewriteItemAverageByItem('2025-04-01',139,'Main'); 
        // echo "Einvoice Client Id";
        // echo "<br>";
        // $client_id_einvoice = "";
        // echo $client_id_einvoice_encrypt = Crypt::encryptString($client_id_einvoice);
        // echo "<br>";
        // echo $client_id_einvoice_decrypt = Crypt::decryptString($client_id_einvoice_encrypt);
        // echo "<br>";

        // echo "Einvoice Client secret";
        // echo "<br>";
        // $client_secret_einvoice = "";
        // echo $client_secret_einvoice_encrypt = Crypt::encryptString($client_secret_einvoice);
        // echo "<br>";
        // echo $client_secret_einvoice_decrypt = Crypt::decryptString($client_secret_einvoice_encrypt);
        // echo "<br>";

        // echo "Eway Bill Client Id";
        // echo "<br>";
        // $client_id_eway_bill = "";
        // echo $client_id_eway_bill_encrypt = Crypt::encryptString($client_id_eway_bill);
        // echo "<br>";
        // echo $client_id_eway_bill_decrypt = Crypt::decryptString($client_id_eway_bill_encrypt);
        // echo "<br>";

        //  echo "Eway Bill Client secret";
        // echo "<br>";
        // $client_secret_eway_bill = "";
        // echo $client_secret_eway_bill_encrypt = Crypt::encryptString($client_secret_eway_bill);
        // echo "<br>";
        // echo $client_secret_eway_bill_decrypt = Crypt::decryptString($client_secret_eway_bill_encrypt);
        // echo "<br>";

        // echo "Gst Client Id";
        // echo "<br>";
        // $client_id_gst = "";
        // echo $client_id_gst_encrypt = Crypt::encryptString($client_id_gst);
        // echo "<br>";
        // echo $client_id_gst_decrypt = Crypt::decryptString($client_id_gst_encrypt);
        // echo "<br>";

        // echo "Gst Client secret";
        // echo "<br>";
        // $client_secret_gst = "";
        // echo $client_secret_gst_encrypt = Crypt::encryptString($client_secret_gst);
        // echo "<br>";
        // echo $client_secret_gst_decrypt = Crypt::decryptString($client_secret_gst_encrypt);
        // echo "<br>";
    }
    private function getAllChildGroups1($parentIds, $companyId)
    {
        $rows = AccountGroups::where('heading_type', 'group')
            ->where('delete', '0')
            ->whereIn('company_id', [$companyId, 0])
            ->get(['id', 'heading']);
    
        // Build parent => children map
        $map = [];
    
        foreach ($rows as $row) {
            $map[(int)$row->heading][] = (int)$row->id;
        }
    
        $result = [];
        $queue = array_map('intval', (array)$parentIds);
        $visited = [];
    
        while (!empty($queue)) {
            $parent = array_shift($queue);
    
            if (isset($visited[$parent])) {
                continue;
            }
    
            $visited[$parent] = true;
    
            foreach ($map[$parent] ?? [] as $childId) {
                $result[] = $childId;
                $queue[] = $childId;
            }
        }
    
        return array_values(array_unique($result));
    }
    
}