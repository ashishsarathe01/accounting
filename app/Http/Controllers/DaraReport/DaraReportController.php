<?php

namespace App\Http\Controllers\DaraReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

use App\Models\SaleOrderSetting;
use App\Models\SupplierPurchaseVehicleDetail;

class DaraReportController extends Controller
{
    public function index(Request $request)
    {

        $from_date = $request->from_date 
            ? date('Y-m-d', strtotime($request->from_date)) 
            : date('Y-m-01');

        $to_date = $request->to_date 
            ? date('Y-m-d', strtotime($request->to_date)) 
            : date('Y-m-t');

        $waste_group_id = SaleOrderSetting::where('group_type','WASTE KRAFT')
            ->where('company_id', Session::get('user_company_id'))
            ->where('setting_type', 'PURCHASE GROUP')
            ->where('setting_for', 'PURCHASE ORDER')
            ->where('group_type', 'WASTE KRAFT')
            ->value('item_id');

        $journalSub = DB::table('journal_details')
            ->selectRaw('journal_id, MIN(id) as id, credit')
            ->where('credit', '!=', '')
            ->groupBy('journal_id');
        
        $salesData = DB::table('sales as s')
            ->leftJoinSub($journalSub, 'jd', function ($join) {
                $join->on('jd.journal_id', '=', 's.transporter_journal_id');
            })
            ->whereBetween('s.date', [$from_date, $to_date])
            ->where('s.company_id', Session::get('user_company_id'))
            ->where('s.status', '1')
            ->where('s.delete', '0')
            ->selectRaw("
                COALESCE(SUM(CAST(s.taxable_amt AS DECIMAL(10,2))),0) as total_sales,
                COALESCE(SUM(CAST(jd.credit AS DECIMAL(10,2))),0) as total_freight
            ")
            ->first();

        $salesWeight = DB::table('sale_descriptions as sd')
            ->join('sales as s', 's.id', '=', 'sd.sale_id')
            ->whereBetween('s.date', [$from_date, $to_date])
            ->where('s.status', '1')
            ->where('s.company_id', Session::get('user_company_id'))
            ->where('s.delete', '0')
            ->sum(DB::raw('CAST(sd.qty AS DECIMAL(10,2))'));

        $creditNote = DB::table('sales_returns')
            ->where('company_id', Session::get('user_company_id'))
            ->where('sr_nature', 'WITH GST')
            ->where('status', '1')
            ->where('delete', '0')
            ->whereBetween('date', [$from_date, $to_date])
            ->sum(DB::raw('CAST(taxable_amt AS DECIMAL(10,2))'));

        $creditWeight = DB::table('sale_return_descriptions as srd')
            ->join('sales_returns as sr', 'sr.id', '=', 'srd.sale_return_id')
            ->where('sr.sr_nature', 'WITH GST')
            ->where('sr.sr_type', '!=', 'RATE DIFFERENCE')
            ->whereBetween('sr.date', [$from_date, $to_date])
            ->where('sr.status', '1')
            ->where('sr.company_id', Session::get('user_company_id'))
            ->where('sr.delete', '0')
            ->sum(DB::raw('CAST(srd.qty AS DECIMAL(10,2))'));

        $total_amount = 
            ($salesData->total_sales ?? 0)
            - ($creditNote ?? 0)
            - ($salesData->total_freight ?? 0);

        $total_weight = ($salesWeight ?? 0) - ($creditWeight ?? 0);

        $avg_rate = ($total_weight > 0)
            ? $total_amount / $total_weight
            : 0;

        $purchaseRows = SupplierPurchaseVehicleDetail::with('purchaseReport')
            ->where('company_id', Session::get('user_company_id'))
            ->where('group_id', $waste_group_id)
            ->whereBetween('entry_date', [$from_date, $to_date])
            //->where('status', 3) 
            ->get();

        $purchase_wastekraft_amount = 0;
        $purchase_wastekraft_weight = 0;

        foreach ($purchaseRows as $row) {

            $actual = 0;
            $actual_weight = 0;
            foreach ($row->purchaseReport as $rp) {
                if (is_numeric($rp->head_id) && $rp->head_qty > 0) {
                    $actual += ($rp->head_qty * $rp->head_contract_rate);
                    $actual_weight +=$rp->head_qty;
                }
            }

            $net_weight = ($row->gross_weight ?? 0) - ($row->tare_weight ?? 0);

            $purchase_wastekraft_amount += $actual;
            $purchase_wastekraft_weight += $actual_weight;
                    }
            $purchaseDetails = [];

            foreach ($purchaseRows as $row) {

                $actual = 0;
                $actual_weight = 0;
                foreach ($row->purchaseReport as $rp) {
                    if (is_numeric($rp->head_id) && $rp->head_qty > 0) {
                        $actual += ($rp->head_qty * $rp->head_contract_rate);
                        $actual_weight += $rp->head_qty;
                    }
                }

                $net_weight = ($row->gross_weight ?? 0) - ($row->tare_weight ?? 0);

                $purchaseDetails[] = [
                    'date' => $row->entry_date,
                    'account_name' => $row->accountInfo->account_name ?? '',
                    'voucher_no' => $row->voucher_no ?? '',
                    'amount' => $actual,
                    'weight' => $actual_weight
                ];
            }
        $waste_purchase_price = ($purchase_wastekraft_weight > 0)
            ? $purchase_wastekraft_amount / $purchase_wastekraft_weight
            : 0;
        $dara_price = round($avg_rate,2) - round($waste_purchase_price,2);

        //  DATE-WISE DETAILED DATA
        $detailedData = [];

        if ($request->detailed) {

            $start = strtotime($from_date);
            $end = strtotime($to_date);

            for ($i = $start; $i <= $end; $i += 86400) {

                $date = date('Y-m-d', $i);

                // SALES
                $sales = DB::table('sales')
                    ->where('date', $date)
                    ->where('company_id', Session::get('user_company_id'))
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->sum(DB::raw('CAST(taxable_amt AS DECIMAL(10,2))'));

                // FREIGHT
                $freight = DB::table('sales as s')
                    ->leftJoin('journal_details as j', 'j.journal_id', '=', 's.transporter_journal_id')
                    ->where('s.date', $date)
                    ->where('s.status', '1')
                    ->where('s.delete', '0')
                    ->where('s.company_id', Session::get('user_company_id'))
                    ->sum('j.credit');

                // CREDIT NOTE
                $credit = DB::table('sales_returns')
                    ->where('date', $date)
                    ->where('company_id', Session::get('user_company_id'))
                    ->where('sr_nature', 'WITH GST')
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->sum(DB::raw('CAST(taxable_amt AS DECIMAL(10,2))'));

                // SALE WEIGHT
                $saleWt = DB::table('sale_descriptions as sd')
                    ->join('sales as s', 's.id', '=', 'sd.sale_id')
                    ->where('s.date', $date)
                    ->where('s.delete', '0')
                    ->where('s.status', '1')
                    ->where('s.company_id', Session::get('user_company_id'))
                    ->sum(DB::raw('CAST(sd.qty AS DECIMAL(10,2))'));

                // CN WEIGHT
                $cnWt = DB::table('sale_return_descriptions as srd')
                    ->join('sales_returns as sr', 'sr.id', '=', 'srd.sale_return_id')
                    ->where('sr.date', $date)
                    ->where('sr.status', '1')
                    ->where('sr.delete', '0')
                    ->where('sr.company_id', Session::get('user_company_id'))
                    ->where('sr.sr_nature', 'WITH GST')
                    ->where('sr.sr_type', '!=', 'RATE DIFFERENCE')
                    ->sum(DB::raw('CAST(srd.qty AS DECIMAL(10,2))'));

                // CALCULATIONS
                $totalAmount = $sales - $credit - $freight;
                $totalWeight = $saleWt - $cnWt;
                $avgRate = $totalWeight > 0 ? $totalAmount / $totalWeight : 0;

                $purchaseAmt = 0;
                $purchaseWt = 0;

                foreach ($purchaseRows as $row) {

                    if ($row->entry_date == $date) {

                        $actual = 0;
                        $actual_weight = 0;
                        foreach ($row->purchaseReport as $rp) {
                            if (is_numeric($rp->head_id) && $rp->head_qty > 0) {
                                $actual += ($rp->head_qty * $rp->head_contract_rate);
                                $actual_weight += $rp->head_qty;
                            }
                        }

                        $net_weight = ($row->gross_weight ?? 0) - ($row->tare_weight ?? 0);

                        $purchaseAmt += $actual;
                        $purchaseWt += $actual_weight;
                    }
                }

                $wasteRate = ($purchaseWt > 0) ? $purchaseAmt / $purchaseWt : 0;

                $dara = $avgRate - $wasteRate;

                $detailedData[] = [
                    'date' => $date,

                    'total_amount' => $totalAmount,
                    'total_weight' => $totalWeight,
                    'avg_rate' => $avgRate,

                    'purchase_amount' => $purchaseAmt,
                    'purchase_weight' => $purchaseWt,
                    'waste_rate' => $wasteRate,

                    'dara' => $dara
                ];
            }
        }

        // dd([
        //     'sales' => $salesData,
        //     'creditNote' => $creditNote,
        //     'salesWeight' => $salesWeight,
        //     'creditWeight' => $creditWeight,
        //     'purchaseCount' => $purchaseRows->count(),
        //     'total_actual' => $total_actual,
        //     'total_net_weight' => $total_net_weight
        // ]);

        $salesDetails = DB::table('sales as s')
            ->leftJoin('accounts as a', DB::raw('CAST(s.party AS UNSIGNED)'), '=', 'a.id') 
            ->whereBetween('s.date', [$from_date, $to_date])
            ->where('s.company_id', Session::get('user_company_id'))
            ->where('s.status', '1')
            ->where('s.delete', '0')
            ->select(
                's.date',
                DB::raw("CONCAT(s.voucher_no_prefix) as voucher_no"),
                'a.account_name as party_name',
                DB::raw('CAST(s.taxable_amt AS DECIMAL(10,2)) as amount')
            )
            ->get();
        $creditNoteDetails = DB::table('sales_returns as sr')
            ->leftJoin('accounts as a', DB::raw('CAST(sr.party AS UNSIGNED)'), '=', 'a.id')
            ->whereBetween('sr.date', [$from_date, $to_date])
            ->where('sr.company_id', Session::get('user_company_id'))
            ->where('sr.sr_nature', 'WITH GST')
            ->where('sr.status', '1')
            ->where('sr.delete', '0')
            ->select(
                'sr.date',
                DB::raw("CONCAT(sr.sr_prefix) as voucher_no"),
                'a.account_name as party_name',
                DB::raw('CAST(sr.taxable_amt AS DECIMAL(10,2)) as amount')
            )
            ->get();
        $freightDetails = DB::table('sales as s')
    ->join('journals as j', 'j.id', '=', 's.transporter_journal_id')

    ->join('journal_details as jd', function ($join) {
        $join->on('jd.journal_id', '=', 'j.id')
             ->where('jd.credit', '!=', '');
    })

    ->leftJoin('accounts as a', DB::raw('CAST(j.vendor AS UNSIGNED)'), '=', 'a.id')

    ->whereBetween('s.date', [$from_date, $to_date])
    ->where('s.company_id', Session::get('user_company_id'))
    ->where('s.status', '1')
    ->where('s.delete', '0')

    ->select(
        'j.id',
        'j.date',
        DB::raw("j.voucher_no_prefix as voucher_no"),
        'a.account_name as vendor_name',
        DB::raw('CAST(jd.credit AS DECIMAL(10,2)) as amount')
    )

    ->groupBy(
        'j.id',
        'j.date',
        'j.voucher_no_prefix',
        'a.account_name',
        'jd.credit'
    )

    ->get();
        $saleWeightDetails = DB::table('sale_descriptions as sd')
            ->join('sales as s', 's.id', '=', 'sd.sale_id')
            ->leftJoin('accounts as a', DB::raw('CAST(s.party AS UNSIGNED)'), '=', 'a.id')
            ->whereBetween('s.date', [$from_date, $to_date])
            ->where('s.company_id', Session::get('user_company_id'))
            ->where('s.status', '1')
            ->where('s.delete', '0')
            ->select(
                's.date',
                DB::raw("CONCAT(s.voucher_no_prefix) as voucher_no"),
                'a.account_name as party_name',
                DB::raw('SUM(CAST(sd.qty AS DECIMAL(10,2))) as weight')
            )
            ->groupBy('s.id', 's.date', 'voucher_no', 'a.account_name')
            ->get();
        $cnWeightDetails = DB::table('sale_return_descriptions as srd')
            ->join('sales_returns as sr', 'sr.id', '=', 'srd.sale_return_id')
            ->leftJoin('accounts as a', DB::raw('CAST(sr.party AS UNSIGNED)'), '=', 'a.id')
            ->whereBetween('sr.date', [$from_date, $to_date])
            ->where('sr.company_id', Session::get('user_company_id'))
            ->where('sr.sr_nature', 'WITH GST')
            ->where('sr.sr_type', '!=', 'RATE DIFFERENCE')
            ->where('sr.status', '1')
            ->where('sr.delete', '0')
            ->select(
                'sr.date',
                DB::raw("CONCAT(sr.sr_prefix) as voucher_no"),
                'a.account_name as party_name',
                DB::raw('SUM(CAST(srd.qty AS DECIMAL(10,2))) as weight')
            )
            ->groupBy('sr.id', 'sr.date', 'voucher_no', 'a.account_name')
            ->get();

        return view('DaraReport.index', [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'total_sales' => $salesData->total_sales ?? 0,
            'total_freight' => $salesData->total_freight ?? 0,
            'creditNote' => $creditNote ?? 0,
            'total_amount' => $total_amount,
            'salesWeight' => $salesWeight ?? 0,
            'creditWeight' => $creditWeight ?? 0,
            'total_weight' => $total_weight,
            'detailedData' => $detailedData,
            'salesDetails' => $salesDetails,
            'creditNoteDetails' => $creditNoteDetails,
            'freightDetails' => $freightDetails,
            'saleWeightDetails' => $saleWeightDetails,
            'cnWeightDetails' => $cnWeightDetails,
            'avg_rate' => $avg_rate,
            'purchase_wastekraft_amount' => $purchase_wastekraft_amount,
            'purchase_wastekraft_weight' => $purchase_wastekraft_weight,
            'purchaseDetails' => $purchaseDetails,
            'waste_purchase_price' => $waste_purchase_price,
            'dara_price' => $dara_price
        ]);
    }
}