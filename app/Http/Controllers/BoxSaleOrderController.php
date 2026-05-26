<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\Models\Accounts;
use App\Helpers\CommonHelper;
class BoxSaleOrderController extends Controller
{

    public function index()
    {
        $companyId = Session::get('user_company_id');
        $saleOrders = DB::table('box_sale_orders')
            ->leftJoin(
                'accounts',
                'accounts.id',
                '=',
                'box_sale_orders.party_id'
            )
            ->where(
                'box_sale_orders.company_id',
                $companyId
            )
            ->where(
                'box_sale_orders.delete',
                '0'
            )
            ->select(
                'box_sale_orders.*',
                'accounts.account_name as party_name',
                DB::raw('
                    (
                        SELECT
                            COALESCE(SUM(qty),0)

                        FROM
                            box_sale_order_items
                        WHERE
                            box_sale_order_items.box_sale_order_id
                            =
                            box_sale_orders.id

                        AND

                            box_sale_order_items.delete = "0"

                    ) as total_qty
                '),

                DB::raw('
                    (
                        SELECT
                            COALESCE(SUM(sale_descriptions.qty),0)

                        FROM
                            sale_descriptions

                        INNER JOIN
                            box_sale_order_items

                            ON
                            box_sale_order_items.id
                            =
                            sale_descriptions.box_sale_order_item_id

                        WHERE

                            box_sale_order_items.box_sale_order_id
                            =
                            box_sale_orders.id

                        AND

                            sale_descriptions.delete = "0"

                    ) as dispatched_qty
                '),

                DB::raw('
                    (

                        (
                            SELECT
                                COALESCE(SUM(qty),0)

                            FROM
                                box_sale_order_items

                            WHERE

                                box_sale_order_items.box_sale_order_id
                                =
                                box_sale_orders.id

                            AND

                                box_sale_order_items.delete = "0"

                        )

                        -

                        (

                            SELECT
                                COALESCE(SUM(sale_descriptions.qty),0)

                            FROM
                                sale_descriptions

                            INNER JOIN
                                box_sale_order_items

                                ON
                                box_sale_order_items.id
                                =
                                sale_descriptions.box_sale_order_item_id

                            WHERE

                                box_sale_order_items.box_sale_order_id
                                =
                                box_sale_orders.id

                            AND

                                sale_descriptions.delete = "0"

                        )

                    ) as pending_qty
                '),

                DB::raw('
                    (
                        SELECT
                            COUNT(*)

                        FROM
                            sale_descriptions

                        INNER JOIN
                            box_sale_order_items

                            ON
                            box_sale_order_items.id
                            =
                            sale_descriptions.box_sale_order_item_id

                        WHERE

                            box_sale_order_items.box_sale_order_id
                            =
                            box_sale_orders.id

                        AND

                            sale_descriptions.delete = "0"

                    ) as used_in_sale
                ')

            )

            ->orderBy(
                'box_sale_orders.id',
                'DESC'
            )
            ->get();
        return view(
            'box_calculator.BoxSaleOrderList',
            compact('saleOrders')
        );
    }
    public function create()
    {
        $companyId = Session::get('user_company_id');
        $party_group_ids = [11];
        $all_party_group_ids = [];
        foreach ($party_group_ids as $gid) {
            $all_party_group_ids[] = $gid;
            $all_party_group_ids = array_merge(
                $all_party_group_ids,
                CommonHelper::getAllChildGroupIds(
                    $gid,
                    $companyId
                )
            );
        }
        $all_party_group_ids = array_unique(
            $all_party_group_ids
        );
        $parties = Accounts::whereIn(
                'company_id',
                [$companyId, 0]
            )
            ->where('delete', '0')
            ->where('status', '1')
            ->whereIn(
                'under_group',
                $all_party_group_ids
            )
            ->select(
                'id',
                'account_name as name'
            )
            ->orderBy('name')
            ->get();
        $items = DB::table('manage_items')
            ->where('company_id', $companyId)
            ->where('delete', '0')
            ->where('status', '1')
            ->orderBy('name')
            ->get();

        $lastOrder = DB::table('box_sale_orders')
            ->where('company_id', $companyId)
            ->orderBy('id', 'DESC')
            ->first();
        if ($lastOrder) {
            $saleOrderNo = 'SO-' . ($lastOrder->id + 1);
        } else {
            $saleOrderNo = 'SO-1';
        }
        return view(
            'box_calculator.BoxAddSaleOrder',
            compact(
                'parties',
                'items',
                'saleOrderNo'
            )
        );
    }

    public function getItemDetails($id)
    {
        $companyId =
            Session::get('user_company_id');
        $item = DB::table('manage_items')
            ->where(
                'id',
                $id
            )
            ->first();
        $box = DB::table('box_calculations')
            ->where(
                'manage_item_id',
                $id
            )
            ->where(
                'company_id',
                $companyId
            )
            ->orderBy(
                'id',
                'DESC'
            )
            ->first();
        $layers = [];
        if($box)
        {
            $layers = DB::table(
                'box_calculation_layers'
            )
                ->where(
                    'box_calculation_id',
                    $box->id
                )
                ->get();
        }
        return response()->json([
            'item' => $item,
            'box' => $box,
            'layers' => $layers
        ]);
    }

    public function store(Request $request)
    {
        $companyId =
            Session::get('user_company_id');
        DB::beginTransaction();
        try {
            $saleOrderId = DB::table('box_sale_orders')
                ->insertGetId([
                    'company_id' => $companyId,
                    'party_id' => $request->party_id,
                    'sale_order_no' => $request->sale_order_no,
                    'po_number' => $request->po_number,
                    'order_date' => $request->order_date,
                    'total_amount' => $request->total_amount,
                    'status' => 1,
                    'delete' => 0,
                    'created_by' => Session::get('user_id'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            if($request->item_id)
            {
                foreach($request->item_id as $key => $itemId)
                {
                    $qty =
                        $request->qty[$key] ?? 0;
                    $price =
                        $request->price[$key] ?? 0;
                    $amount =
                        (float)$qty
                        *
                        (float)$price;
                    DB::table('box_sale_order_items')
                        ->insert([
                            'box_sale_order_id' => $saleOrderId,
                            'company_id' => $companyId,
                            'item_id' => $itemId,
                            'description' =>
                                $request->description[$key] ?? '',
                            'qty' => $qty,
                            'price' => $price,
                            'amount' => $amount,
                            'status' => 1,
                            'delete' => 0,
                            'created_by' =>
                                Session::get('user_id'),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                }
            }
            DB::commit();
            return redirect()
                ->route('box.sale.order.index')
                ->with(
                    'success',
                    'Box Sale Order Added Successfully'
                );
        }
        catch (\Exception $e) {
            DB::rollback();
            return back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function edit($id)
    {
        $companyId =
            Session::get('user_company_id');
        $usedInSale = DB::table('sale_descriptions')

            ->whereIn(

                'box_sale_order_item_id',

                DB::table('box_sale_order_items')

                    ->where(
                        'box_sale_order_id',
                        $id
                    )

                    ->pluck('id')

            )

            ->where(
                'delete',
                '0'
            )

            ->exists();


        if($usedInSale)
        {

            return redirect()

                ->route('box.sale.order.index')

                ->with(

                    'error',

                    'Box Sale Order already used in Sale. Cannot edit.'

                );

        }
        $saleOrder = DB::table('box_sale_orders')
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'id',
                $id
            )
            ->where(
                'delete',
                '0'
            )
            ->first();
        $saleOrderItems = DB::table('box_sale_order_items')
            ->where(
                'box_sale_order_id',
                $id
            )
            ->where(
                'delete',
                '0'
            )
            ->get();
        $party_group_ids = [11];
        $all_party_group_ids = [];
        foreach ($party_group_ids as $gid)
        {
            $all_party_group_ids[] = $gid;
            $all_party_group_ids = array_merge(
                $all_party_group_ids,
                CommonHelper::getAllChildGroupIds(
                    $gid,
                    $companyId
                )
            );
        }
        $all_party_group_ids =
            array_unique($all_party_group_ids);
        $parties = Accounts::whereIn(
                'company_id',
                [$companyId,0]
            )
            ->where('delete','0')
            ->where('status','1')
            ->whereIn(
                'under_group',
                $all_party_group_ids
            )
            ->select(
                'id',
                'account_name as name'
            )
            ->orderBy('name')
            ->get();
        $items = DB::table('manage_items')
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'delete',
                '0'
            )
            ->where(
                'status',
                '1'
            )
            ->orderBy(
                'name'
            )
            ->get();
        return view(
            'box_calculator.BoxEditSaleOrder',
            compact(
                'saleOrder',
                'saleOrderItems',
                'parties',
                'items'
            )
        );
    }

    public function update(Request $request, $id)
    {
        $companyId =
            Session::get('user_company_id');

        DB::beginTransaction();
        try {
            DB::table('box_sale_orders')
                ->where(
                    'id',
                    $id
                )
                ->where(
                    'company_id',
                    $companyId
                )
                ->update([
                    'party_id' => $request->party_id,
                    'po_number' => $request->po_number,
                    'order_date' => $request->order_date,
                    'total_amount' => $request->total_amount,
                    'updated_by' =>
                        Session::get('user_id'),
                    'updated_at' => now()
                ]);
            DB::table('box_sale_order_items')
                ->where(
                    'box_sale_order_id',
                    $id
                )
                ->delete();
            if($request->item_id)
            {
                foreach($request->item_id as $key => $itemId)
                {
                    $qty =
                        $request->qty[$key] ?? 0;
                    $price =
                        $request->price[$key] ?? 0;
                    $amount =
                        (float)$qty
                        *
                        (float)$price;
                    DB::table('box_sale_order_items')
                        ->insert([
                            'box_sale_order_id' => $id,
                            'company_id' => $companyId,
                            'item_id' => $itemId,
                            'description' =>
                                $request->description[$key] ?? '',
                            'qty' => $qty,
                            'price' => $price,
                            'amount' => $amount,
                            'status' => 1,
                            'delete' => 0,
                            'created_by' =>
                                Session::get('user_id'),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                }
            }
            DB::commit();
            return redirect()
                ->route('box.sale.order.index')
                ->with(
                    'success',
                    'Box Sale Order Updated Successfully'
                );
        }
        catch (\Exception $e) {
            DB::rollback();
            return back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function delete($id)
    {
        $companyId =
            Session::get('user_company_id');
        $usedInSale = DB::table('sale_descriptions')

            ->whereIn(

                'box_sale_order_item_id',

                DB::table('box_sale_order_items')

                    ->where(
                        'box_sale_order_id',
                        $id
                    )

                    ->pluck('id')

            )

            ->where(
                'delete',
                '0'
            )

            ->exists();


        if($usedInSale)
        {

            return redirect()

                ->route('box.sale.order.index')

                ->with(

                    'error',

                    'Box Sale Order already used in Sale. Cannot delete.'

                );

        }
        DB::beginTransaction();
        try {
            DB::table('box_sale_orders')
                ->where(
                    'id',
                    $id
                )
                ->where(
                    'company_id',
                    $companyId
                )
                ->update([
                    'status' => 0,
                    'delete' => 1,
                    'deleted_by' =>
                        Session::get('user_id'),
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);
            DB::table('box_sale_order_items')
                ->where(
                    'box_sale_order_id',
                    $id
                )
                ->update([
                    'status' => 0,
                    'delete' => 1,
                    'deleted_by' =>
                        Session::get('user_id'),
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);
            DB::commit();
            return redirect()
                ->route('box.sale.order.index')
                ->with(
                    'success',
                    'Box Sale Order Deleted Successfully'
                );
        }
        catch (\Exception $e) {
            DB::rollback();
            return back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

}