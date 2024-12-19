<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\State;
use App\Models\GstSetting;
use App\Models\GstBranch;
use Carbon\Carbon;
use DB;


class GstSettingController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function gstConfigurationList(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id'
        ], [
            'company_id.required' => 'Company id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } 

        $GstSettings = GstSetting::where('company_id',$request->company_id)->get();

        $branchs = array();
        if($GstSettings->count()>0)
        {
            $branchs = GstBranch::where(['company_id'=>$request->company_id,'gst_setting_id'=>$GstSettings[0]->gst_setting_id])->get()->toArray();
        }
        
        if ($GstSettings) {
            return response()->json([
                'code' => 200,
                'Gstdata' => $GstSettings,
                'GstdataCount' => $GstSettings->count(),
                'GstBranchdata' => $branchs,
                'GstBranchdataCount' => count($branchs),
            ]);
        } else {
            $this->failedMessage();
        }

    }


    public function gstConfigurationBranchList(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'gst_setting_id' => 'required|exists:gst_settings,id'
        ], [
            'company_id.required' => 'Company id is required.',
            'gst_setting_id.required' => 'Gst setting id is required.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } 
        $branchs = GstBranch::where(['company_id'=>$request->company_id,'gst_setting_id'=>$request->gst_setting_id])->get();
        if ($branchs) {
            return response()->json([
                'code' => 200,
                'GstBranchdata' => $branchs,
                'GstBranchdataCount' => $branchs->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'gst_type' => 'required|string',
        ], [
            'gst_type.required' => 'Gst type is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $gst = new GstSetting;
        $gst->company_id = $request->company_id;
        $gst->gst_type = $request->gst_type;
        $gst->gst_no = $request->gst_no;
        $gst->business_type = $request->business_type;
        $gst->validity_from = $request->validity_from;
        $gst->validity_to = $request->validity_to;
        $gst->address = $request->address;
        $gst->state = $request->state;
        $gst->pincode = $request->pincode;
        $gst->scheme = $request->scheme;
        $gst->einvoice_username = $request->einvoice_username;
        $gst->einvoice_password = $request->einvoice_password;
        $gst->ewaybill_username = $request->ewaybill_username;
        $gst->ewaybill_password = $request->ewaybill_password;
        $gst->save();

        if ($gst) {
            return response()->json(['code' => 200, 'message' => 'Gst configuration added successfully!','GSTData'=> $gst,'Id'=> $gst->id]);
        }
         else {
            $this->failedMessage();
        }

    }



    public function add_branch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst_setting_id' => 'required',
            'company_id' => 'required',
            'branch_address' => 'required',
            'branch_city' => 'required',
            'branch_pincode' => 'required',
            'branch_matcenter' => 'required'
        ], [
            'gst_setting_id.required' => 'Gst configuration id is required.',
            'company_id.required' => 'Company id is required.',
            'branch_address.required' => 'Branch address is required.',
            'branch_city.required' => 'Branch city is required.',
            'branch_pincode.required' => 'Branch pincode is required.',
            'branch_matcenter.required' => 'Branch matcenter is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

            $company_id = $request->company_id;
            $gst_setting_id = $request->gst_setting_id;
            $branch_addresss = $request->branch_address;
            $branch_citys = $request->branch_city;
            $branch_pincodes = $request->branch_pincode;
            $branch_matcenter = $request->branch_matcenter;
            $branch_seriess = $request->branch_series;
            $branch_invoice_start_froms = $request->branch_invoice_start_from;

            if(is_array($branch_addresss) && is_array($branch_citys) && is_array($branch_pincodes) && is_array($branch_matcenter))
            {
                foreach ($branch_addresss as $key => $banch) 
                {
                    $branch = new GstBranch;
                    $branch->company_id = $company_id;
                    $branch->gst_setting_id = $gst_setting_id;
                    $branch->branch_address = $banch;
                    $branch->branch_city = $branch_citys[$key];
                    $branch->branch_pincode = $branch_pincodes[$key];
                    $branch->branch_matcenter = $branch_matcenter[$key];
                    $branch->branch_series = $branch_seriess[$key];
                    $branch->branch_invoice_start_from = $branch_invoice_start_froms[$key];
                    $branch->status = '1';
                    $branch->save();
             }

             return response()->json(['code' => 200, 'message' => 'New branch added successfully!','Data'=> $branch,'Id'=> $branch->id]);

            }
            else
            {
                return response()->json(['code' => 422, 'message' => 'Some fields are not sending as array value, try again with correct values!']);
            }

    }

    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
    public function updateGstSetting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst_setting_id' => 'required|exists:gst_settings,id',
            'company_id' => 'required|exists:companies,id',
            'gst_type' => 'required|string',
        ], [
            'gst_type.required' => 'Gst type is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $gst = GstSetting::find($request->gst_setting_id);
        $gst->gst_type = $request->gst_type;
        $gst->company_id = $request->company_id;
        $gst->gst_no = $request->gst_no;
        $gst->business_type = $request->business_type;
        $gst->validity_from = $request->validity_from;
        $gst->validity_to = $request->validity_to;
        $gst->address = $request->address;
        $gst->state = $request->state;
        $gst->pincode = $request->pincode;
        $gst->scheme = $request->scheme;
        $gst->einvoice_username = $request->einvoice_username;
        $gst->einvoice_password = $request->einvoice_password;
        $gst->ewaybill_username = $request->ewaybill_username;
        $gst->ewaybill_password = $request->ewaybill_password;
        $gst->updated_at = Carbon::now();
        $gst->update();

        if ($gst) {
            return response()->json(['code' => 200, 'message' => 'Gst configuration updated successfully!','GSTData'=> $gst,'Id'=> $gst->id]);
        }
         else {
            $this->failedMessage();
        }
    }



    public function updateGstBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:gst_branches,id',
            'gst_setting_id' => 'required|exists:gst_settings,id',
            'company_id' => 'required|exists:companies,id',
            'branch_address' => 'required',
            'branch_city' => 'required',
            'branch_pincode' => 'required',
            'branch_matcenter' => 'required'
        ], [
            'gst_setting_id.required' => 'Gst configuration id is required.',
            'company_id.required' => 'Company id is required.',
            'branch_address.required' => 'Branch address is required.',
            'branch_city.required' => 'Branch city is required.',
            'branch_pincode.required' => 'Branch pincode is required.',
            'branch_matcenter.required' => 'Branch matcenter is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

                    $branch = GstBranch::find($request->branch_id);
                    $branch->company_id = $request->company_id;
                    $branch->gst_setting_id = $request->gst_setting_id;
                    $branch->branch_address = $request->branch_address;
                    $branch->branch_city = $request->branch_city;
                    $branch->branch_pincode = $request->branch_pincode;
                    $branch->branch_matcenter = $request->branch_matcenter;
                    $branch->branch_series = $request->branch_series;
                    $branch->branch_invoice_start_from = $request->branch_invoice_start_from;
                    $branch->status = '1';
                    $branch->updated_at = Carbon::now();
                    $branch->update();
        if($branch)
        {
             return response()->json(['code' => 200, 'message' => 'Branch data updated successfully!','Data'=> $branch,'Id'=> $branch->id]);

            }
            else
            {
                return response()->json(['code' => 422, 'message' => 'Some fields are not sending as array value, try again with correct values!']);
            }

    }


    

    /**
     * Generates failed response and message.
     */
    public function failedMessage()
    {
        return response()->json([
            'code' => 422,
            'message' => 'Something went wrong, please try again after some time.',
        ]);
    }
}
