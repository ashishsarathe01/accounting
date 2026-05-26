<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\Helpers\CommonHelper;
use App\Models\Accounts;
use App\Models\Units;
use App\Models\ItemGroups;
use App\Models\ManageItems;
class BoxController extends Controller
{
 public function index()
    {
        return view('box_calculator.index');
    }


    public function list()
    {
        $companyId =
        Session::get('user_company_id');

        $boxes = DB::table('box_calculations')

        ->where(
            'company_id',
            $companyId
        )

        ->orderBy('id','desc')

        ->get();

        return view(
            'box_calculator.list',
            compact('boxes')
        );
    }
    public function advanceindex()
    {
        $companyId =
            Session::get('user_company_id');

        $config = DB::table(
            'box_calculator_configurations'
        )

        ->where(
            'company_id',
            $companyId
        )

        ->first();

        $itemGroups = ItemGroups::where(
                            'delete',
                            '=',
                            '0'
                        )

                        ->where(
                            'company_id',
                            $companyId
                        )

                        ->get();

        return view(
            'box_calculator.advanceindex',
            compact(
                'config',
                'itemGroups'
            )
        );
    }


    public function saveAdvanceCalculation(Request $request)
    {
        DB::beginTransaction();

        try {
                $companyId =
                    Session::get('user_company_id');
                    $manageItemId = null;
                            $id = DB::table('box_calculations')
                            ->insertGetId([
                'company_id' => $companyId,
                'box_name' => $request->box_name,
                'manage_item_id' => null,
                'input_unit' => $request->input_unit,
                'result_unit' => $request->result_unit,

                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,

                'ply' => $request->ply,

                'conversion_type' => $request->conversion_type,
                'conversion_cost' => $request->conversion_cost,

                'profit_margin' => $request->profit_margin,
                'gst_percent' => $request->gst_percent,

                'boxes_per_sheet' => $request->boxes_per_sheet,

                'joint_allowance' => $request->joint_allowance,
                'cutting_margin' => $request->cutting_margin,
                'deckle_margin' => $request->deckle_margin,

                'available_width' => $request->available_width,

                'qty' => $request->qty,

                'cutting_length_result' => $request->cutting_length_result,
                'deckle_result' => $request->deckle_result,

                'sheet_weight' => $request->sheet_weight,
                'weight_per_box' => $request->weight_per_box,

                'paper_cost_per_box' => $request->paper_cost_per_box,

                'conversion_cost_result' => $request->conversion_cost_result,

                'total_cost_per_box' => $request->total_cost_per_box,

                'sale_without_gst' => $request->sale_without_gst,
                'sale_with_gst' => $request->sale_with_gst,

                'total_sheet_required' => $request->total_sheet_required,

                'total_paper_required' => $request->total_paper_required,

                'total_paper_cost' => $request->total_paper_cost,

                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if($request->create_item == 1)
            {
                $alreadyExists = DB::table('manage_items')

                ->where(
                    'company_id',
                    $companyId
                )

                ->where(
                    'name',
                    $request->box_name
                )

                ->first();

                if(!$alreadyExists)
                {
                    $config = DB::table(
                        'box_calculator_configurations'
                    )

                    ->where(
                        'company_id',
                        $companyId
                    )

                    ->first();

                    $manageItemId = DB::table('manage_items')
                        ->insertGetId([
                            'company_id' => $companyId,
                            'name' => $request->box_name,
                            'p_name' => $request->box_name,
                            'u_name' => $config->unit_id ?? null,
                            'hsn_code' => $config->hsn_code ?? null,
                            'gst_rate' => $request->gst_percent,
                            'item_type' => $config->item_type ?? 'taxable',
                            'g_name' => $request->g_name,
                            'status' => '1',
                            'delete' => '0',
                            'created_by' => Session::get('user_id'),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        DB::table('item_gst_rate')
                        ->insert([
                            'item_id' => $manageItemId,
                            'gst_rate' => $request->gst_percent,
                            'item_type' => $config->item_type ?? 'taxable',
                            'comp_id' => $companyId,
                            'effective_from' => '2025-09-22',
                            'created_by' => Session::get('user_id'),
                            'created_at' => now(),
                            'updated_at' => now(),
                            'updated_by' => Session::get('user_id'),
                            'updated_by_type' => null
                        ]);
                        DB::table('box_calculations')
                        ->where('id',$id)
                        ->update([
                            'manage_item_id' => $manageItemId
                        ]);
                    }
            }
            if(!empty($request->layers))
            {
                foreach($request->layers as $layer)
                {

                    DB::table('box_calculation_layers')
                    ->insert([

                        'box_calculation_id' => $id,

                        'layer_name' => $layer['layer_name'],

                        'gsm' => $layer['gsm'],

                        'bf' => $layer['bf'],

                        'flute_factor' =>
                            $layer['flute_factor'] ?? null,

                        'rate' => $layer['rate'],

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Saved Successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        $companyId =
        Session::get('user_company_id');

        DB::table('box_calculations')

        ->where('id',$id)

        ->where(
            'company_id',
            $companyId
        )

        ->delete();

        return redirect()
        ->back()
        ->with('success','Deleted Successfully');
    }
public function edit($id)
{
    $companyId =
    Session::get('user_company_id');

    $box = DB::table('box_calculations')

    ->where('id',$id)

    ->where(
        'company_id',
        $companyId
    )

    ->first();

    $layers = DB::table('box_calculation_layers')
    ->where('box_calculation_id',$id)
    ->get();
    $selectedGroup = null;

    if(
        isset($box->manage_item_id)
        && !empty($box->manage_item_id)
    )
    {
        $manageItem = DB::table('manage_items')

        ->where(
            'id',
            $box->manage_item_id
        )

        ->first();

        if($manageItem)
        {
            $selectedGroup =
                $manageItem->g_name;
        }
    }
        $config = DB::table(
        'box_calculator_configurations'
    )

    ->where(
        'company_id',
        $companyId
    )

    ->first();

    $itemGroups = ItemGroups::where(
                        'delete',
                        '=',
                        '0'
                    )

                    ->where(
                        'company_id',
                        $companyId
                    )

                    ->get();

    return view(
        'box_calculator.advanceindex',
        compact(
            'box',
            'layers',
            'config',
            'itemGroups',
            'selectedGroup'
        )
    );
}
public function update(Request $request,$id)
{
    DB::beginTransaction();

    try {
            $companyId =
                Session::get('user_company_id');
                $boxData = DB::table('box_calculations')
                    ->where('id',$id)
                    ->where(
                        'company_id',
                        $companyId
                    )
                    ->first();
                    DB::table('box_calculations')

            ->where('id',$id)

            ->where(
                'company_id',
                $companyId
            )

            ->update([

            'box_name' => $request->box_name,

            'input_unit' => $request->input_unit,

            'result_unit' => $request->result_unit,

            'length' => $request->length,

            'width' => $request->width,

            'height' => $request->height,

            'ply' => $request->ply,

            'conversion_type' => $request->conversion_type,

            'conversion_cost' => $request->conversion_cost,

            'profit_margin' => $request->profit_margin,

            'gst_percent' => $request->gst_percent,

            'boxes_per_sheet' => $request->boxes_per_sheet,

            'joint_allowance' => $request->joint_allowance,

            'cutting_margin' => $request->cutting_margin,

            'deckle_margin' => $request->deckle_margin,

            'available_width' => $request->available_width,

            'qty' => $request->qty,

            'cutting_length_result' => $request->cutting_length_result,

            'deckle_result' => $request->deckle_result,

            'sheet_weight' => $request->sheet_weight,

            'weight_per_box' => $request->weight_per_box,

            'paper_cost_per_box' => $request->paper_cost_per_box,

            'conversion_cost_result' => $request->conversion_cost_result,

            'total_cost_per_box' => $request->total_cost_per_box,

            'sale_without_gst' => $request->sale_without_gst,

            'sale_with_gst' => $request->sale_with_gst,

            'total_sheet_required' => $request->total_sheet_required,

            'total_paper_required' => $request->total_paper_required,

            'total_paper_cost' => $request->total_paper_cost,

            'updated_at' => now()
        ]);

        if(
            $request->create_item == 1
            && empty($boxData->manage_item_id)
        )
        {
            $alreadyExists = DB::table('manage_items')

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'name',
                $request->box_name
            )

            ->first();

            if(!$alreadyExists)
            {
                $config = DB::table(
                    'box_calculator_configurations'
                )

                ->where(
                    'company_id',
                    $companyId
                )

                ->first();

                $manageItemId = DB::table('manage_items')

                ->insertGetId([

                    'company_id' => $companyId,

                    'name' => $request->box_name,

                    'p_name' => $request->box_name,

                    'u_name' => $config->unit_id ?? null,

                    'hsn_code' => $config->hsn_code ?? null,

                    'gst_rate' => $request->gst_percent,

                    'item_type' => $config->item_type ?? 'taxable',

                    'g_name' => $request->g_name,

                    'status' => '1',

                    'delete' => '0',

                    'created_by' => Session::get('user_id'),

                    'created_at' => now(),

                    'updated_at' => now()
                ]);

                DB::table('box_calculations')

                ->where('id',$id)

                ->update([

                    'manage_item_id' => $manageItemId
                ]);
            }
        }
        DB::table('box_calculation_layers')
        ->where('box_calculation_id',$id)
        ->delete();


        if(!empty($request->layers))
        {
            foreach($request->layers as $layer)
            {
                DB::table('box_calculation_layers')
                ->insert([

                    'box_calculation_id' => $id,

                    'layer_name' => $layer['layer_name'],

                    'gsm' => $layer['gsm'],

                    'bf' => $layer['bf'],

                    'flute_factor' =>
                        $layer['flute_factor'] ?? null,

                    'rate' => $layer['rate'],

                    'created_at' => now(),

                    'updated_at' => now()
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'status' => 1,
            'message' => 'Updated Successfully'
        ]);

    }
    catch(\Exception $e)
    {
        DB::rollback();

        return response()->json([
            'status' => 0,
            'message' => $e->getMessage()
        ]);
    }
}
public function configuration()
{
    $companyId =
        Session::get('user_company_id');

    $config = DB::table(
        'box_calculator_configurations'
    )

    ->where(
        'company_id',
        $companyId
    )

    ->first();

    $accountunit = Units::where(
                        'delete',
                        '=',
                        '0'
                    )
                    ->where(
                        'company_id',
                        $companyId
                    )
                    ->get();
    return view(
        'box_calculator.configuration',
        compact(
            'config',
            'accountunit'
        )
    );
}
public function saveConfiguration(Request $request)
{
    $companyId =
        Session::get('user_company_id');


    $exists = DB::table(
        'box_calculator_configurations'
    )

    ->where(
        'company_id',
        $companyId
    )

    ->first();


    $data = [

        'company_id' => $companyId,

        'conversion_type' =>
            $request->conversion_type,

        'conversion_cost' =>
            $request->conversion_cost,

        'flute_factor' =>
            $request->flute_factor,

        'gst_percent' =>
            $request->gst_percent,

        'item_type' =>
            $request->item_type,

        'unit_id' =>
            $request->unit_id,

        'hsn_code' =>
            $request->hsn_code,
        'joint_allowance' =>
            $request->joint_allowance,

        'cutting_margin' =>
            $request->cutting_margin,

        'updated_at' => now()
    ];


    if($exists)
    {
        DB::table(
            'box_calculator_configurations'
        )

        ->where(
            'company_id',
            $companyId
        )

        ->update($data);
    }
    else
    {
        $data['created_at'] = now();

        DB::table(
            'box_calculator_configurations'
        )

        ->insert($data);
    }


    return redirect()
    ->back()
    ->with(
        'success',
        'Configuration Saved Successfully'
    );
}
public function quotationList()
{
    $companyId =
        Session::get('user_company_id');


    $quotations = DB::table(
        'box_calculate_quotations as q'
    )

    ->leftJoin(
        'accounts as a',
        'a.id',
        '=',
        'q.party_id'
    )

    ->leftJoin(
        'box_calculate_quotation_items as qi',
        'qi.quotation_id',
        '=',
        'q.id'
    )

    ->where(
        'q.company_id',
        $companyId
    )

    ->select(

        'q.*',

        'a.account_name as party_name',

        'qi.box_name',

        'qi.rate'
    )

    ->groupBy('q.id')

    ->orderBy(
        'q.id',
        'desc'
    )

    ->get();


    return view(
        'box_calculator.quotationlist',
        compact('quotations')
    );
}
public function quotationCreate()
{
    $companyId =
        Session::get('user_company_id');


    $party_root_groups = [3,11];

    $party_group_ids = [];


    foreach ($party_root_groups as $gid)
    {
        $party_group_ids[] = $gid;

        $party_group_ids = array_merge(

            $party_group_ids,

            CommonHelper::getAllChildGroupIds(
                $gid,
                $companyId
            )
        );
    }


    $party_group_ids =
        array_unique($party_group_ids);


    $party_list = Accounts::whereIn(
            'company_id',
            [$companyId,0]
        )

        ->where('delete','0')

        ->where('status','1')

        ->whereIn(
            'under_group',
            $party_group_ids
        )

        ->select(
            'id',
            'account_name as name'
        )

        ->orderBy('name')

        ->get();


    $boxes = DB::table('box_calculations')

    ->where(
        'company_id',
        $companyId
    )

    ->orderBy(
        'box_name'
    )

    ->get();


    return view(
        'box_calculator.quotationcreate',
        compact(
            'party_list',
            'boxes'
        )
    );
}
public function getBoxDetails($id)
{
    $companyId =
        Session::get('user_company_id');


    $box = DB::table('box_calculations')

    ->where(
        'company_id',
        $companyId
    )

    ->where(
        'id',
        $id
    )

    ->first();


    $layers = DB::table(
        'box_calculation_layers'
    )

    ->where(
        'box_calculation_id',
        $id
    )

    ->get();


    return response()->json([

        'box' => $box,

        'layers' => $layers

    ]);
}
public function quotationSave(Request $request)
{
    DB::beginTransaction();

    try {

        $companyId =
            Session::get('user_company_id');


        $quotationId = DB::table(
            'box_calculate_quotations'
        )

        ->insertGetId([

            'company_id' => $companyId,

            'quotation_date' =>
                $request->quotation_date,

            'party_id' =>
                $request->party_id,

            'created_at' => now(),

            'updated_at' => now()
        ]);


        $totalAmount = 0;

        $gstTotal = 0;

        $grandTotal = 0;


        if(!empty($request->items))
        {
            foreach($request->items as $item)
            {

                $boxData = DB::table(
                    'box_calculations'
                )

                ->where(
                    'id',
                    $item['box_calculation_id']
                )

                ->first();



                $gstPercent =
                    $boxData->gst_percent ?? 0;



                $gstAmount =

                    ($item['rate']
                    * $gstPercent) / 100;



                $itemTotal =

                    $item['rate']
                    + $gstAmount;



                DB::table(
                    'box_calculate_quotation_items'
                )

                ->insert([

                    'quotation_id' =>
                        $quotationId,

                    'box_calculation_id' =>
                        $item['box_calculation_id'],

                    'box_name' =>
                        $item['box_name'],

                    'ply' =>
                        $item['ply'],

                    'dimensions' =>
                        $item['dimensions'],

                    'box_details' =>
                        $item['box_details'] ?? null,

                    'paper_specification' =>
                        $item['paper_specification'],

                    'calculation_details' =>
                        $item['calculation_details']
                        ?? null,

                    'rate' =>
                        $item['rate'],

                    'gst_percent' =>
                        $gstPercent,

                    'gst_amount' =>
                        $gstAmount,

                    'total_amount' =>
                        $itemTotal,

                    'created_at' => now(),

                    'updated_at' => now()
                ]);


                $totalAmount +=
                    $item['rate'];


                $gstTotal +=
                    $gstAmount;


                $grandTotal +=
                    $itemTotal;
            }
        }



        DB::table(
            'box_calculate_quotations'
        )

        ->where(
            'id',
            $quotationId
        )

        ->update([

            'total_amount' =>
                $totalAmount,

            'gst_amount' =>
                $gstTotal,

            'grand_total' =>
                $grandTotal,

            'updated_at' => now()
        ]);


        DB::commit();


        return redirect()

        ->route(
            'box-calculator-quotation.list'
        )

        ->with(
            'success',
            'Quotation Saved Successfully'
        );

    }
    catch(\Exception $e)
    {
        DB::rollback();

        return redirect()

        ->back()

        ->with(
            'error',
            $e->getMessage()
        );
    }
}
public function quotationView($id)
{
    $companyId =
        Session::get('user_company_id');


    $quotation = DB::table(
        'box_calculate_quotations as q'
    )

    ->leftJoin(
        'accounts as a',
        'a.id',
        '=',
        'q.party_id'
    )

    ->where(
        'q.company_id',
        $companyId
    )

    ->where(
        'q.id',
        $id
    )

    ->select(

        'q.*',

        'a.account_name as party_name',

        'a.address',

        'a.mobile'

    )

    ->first();



    $items = DB::table(
        'box_calculate_quotation_items'
    )

    ->where(
        'quotation_id',
        $id
    )

    ->get();



    $company = DB::table('companies')

    ->where(
        'id',
        $companyId
    )

    ->first();



    return view(
        'box_calculator.quotationview',
        compact(
            'quotation',
            'items',
            'company'
        )
    );
}
public function quotationDelete($id)
{
    DB::table(
        'box_calculate_quotation_items'
    )

    ->where(
        'quotation_id',
        $id
    )

    ->delete();


    DB::table(
        'box_calculate_quotations'
    )

    ->where(
        'id',
        $id
    )

    ->delete();


    return redirect()
    ->back()
    ->with(
        'success',
        'Quotation Deleted Successfully'
    );
}
}