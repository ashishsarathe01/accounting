<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountHeading;
class AccountHeadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(){
      $accountheading = AccountHeading::where('company_id',0)
                                       ->where('delete', '=', '0')
                                       ->get();
      return view('admin-module.heading.account_head')->with('accountheading', $accountheading);
   }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function show($id){
      
   }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function edit($id){
      $editheading = AccountHeading::find($id);
      return view('admin-module.heading.edit_account_head')->with('editheading', $editheading);
   }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request, $id){
      $validator = Validator::make($request->all(), [
         'name' => 'required|string',
         'bs_profile' => 'required|string',
         'name_sch_three' => 'required|string',
         'bs_profile_three' => 'required|string',
      ], [
         'name.required' => 'Name is required.',
         'bs_profile.required' => 'B/S Profile is required.',
         'name_sch_three.required' => 'Name As Sch is required.',
         'bs_profile_three.required' => 'B/S Profile is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $account =  AccountHeading::find($request->heading_id);
      $account->name = $request->input('name');
      $account->bs_profile = $request->input('bs_profile');
      $account->name_sch_three = $request->input('name_sch_three');
      $account->bs_profile_three = $request->input('bs_profile_three');
      $account->status = $request->input('status');
      $account->updated_at = Carbon::now();
      $account->update();
      return redirect('admin/account-head')->withSuccess('Account Heading updated successfully!');
   }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
