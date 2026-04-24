<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SaleOrder;
use App\Models\Companies;
use App\Models\SaleInvoiceConfiguration;
use App\Models\SaleOrderItemGsmSize;
use App\Models\SaleOrderItemGsm;
use App\Models\SaleOrderItemWeight;
use App\Helpers\CommonHelper;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleOrderController extends Controller
{
    public function saleOrderList(Request $request)
        {
            $request->validate([
                'company_id' => 'required|integer',
                'status'     => 'required|in:0,1,2,4',
                'from_date'  => 'nullable|date',
                'to_date'    => 'nullable|date',
            ]);
        
            $query = SaleOrder::with([
                'billTo:id,account_name',
                'shippTo:id,account_name',
                'sale:id,sale_order_id,e_invoice_status,voucher_no_prefix,date',
                'createdByUser:id,name',
                'updatedByUser:id,name'
            ])
            ->where('company_id', $request->company_id)
            ->where('status', $request->status);
        
            if ($request->from_date) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
        
            if ($request->to_date) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }
        
            $orders = $query->orderBy('created_at', 'desc')->get();
        
            return response()->json([
                'code' => 200,
                'message' => 'Sale Order List',
                'data' => [
                    'status' => $request->status,
                    'orders' => $orders
                ]
            ]);
        }

  public function viewHtml(Request $request)
{
    $id = $request->sale_order_id;
     $company_id = $request->company_id;

    if (!$id) {
        return response()->json(['error' => 'sale_order_id required'], 422);
    }

    $company = Companies::join('states','companies.state','=','states.id')
                ->where('companies.id', $company_id)
                ->select(['companies.*','states.name as sname'])
                ->first();

    $saleOrder = SaleOrder::with([
        'billTo:id,account_name,gstin,address,pin_code,state,pan',
        'shippTo:id,account_name,gstin,address,pin_code,state,pan',
        'orderCreatedBy:id,name',
        'items.item:id,name,hsn_code',
        'items.unitMaster:id,s_name',
        'items.gsms.details'
    ])->findOrFail($id);

    $configuration = SaleInvoiceConfiguration::with(['terms','banks'])
                    ->where('company_id', $company_id)
                    ->first();

    return view('saleorder.apisale_order_view', compact('saleOrder', 'company', 'configuration'));
}

public function viewPdf(Request $request)
{
    $id = $request->sale_order_id;
    $company_id = $request->company_id;

    if (!$id) {
        return response()->json(['error' => 'sale_order_id required'], 422);
    }

    $company = Companies::join('states','companies.state','=','states.id')
                ->where('companies.id', $company_id)
                ->select(['companies.*','states.name as sname'])
                ->first();

    $saleOrder = SaleOrder::with([
        'billTo:id,account_name,gstin,address,pin_code,state,pan',
        'shippTo:id,account_name,gstin,address,pin_code,state,pan',
        'orderCreatedBy:id,name',
        'items.item:id,name,hsn_code',
        'items.unitMaster:id,s_name',
        'items.gsms.details'
    ])->findOrFail($id);

    $configuration = SaleInvoiceConfiguration::with(['terms','banks'])
                    ->where('company_id', $company_id)
                    ->first();

    $pdf = PDF::loadView('saleorder.apisale_order_view', compact('saleOrder','company','configuration'))
            ->setPaper('A4', 'portrait');

    return $pdf->download('sale_order_'.$id.'.pdf');
}



}
