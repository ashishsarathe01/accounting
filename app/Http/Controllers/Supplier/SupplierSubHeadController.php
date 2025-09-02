<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Session;
use Carbon\Carbon;
use App\Models\SupplierSubHead;
use App\Models\ItemGroups;
class SupplierSubHeadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $heads = SupplierSubHead::with('group')
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->orderBy('sequence')
                                ->get();
        return view('supplier/view_sub_head',["heads"=>$heads]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $groups = ItemGroups::where('company_id', Session::get('user_company_id'))
                            ->where('delete', '=', '0')
                            ->where('status', '=', '1')
                            ->orderBy('group_name')
                            ->get();
        return view('supplier/add_sub_head',["groups"=>$groups]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'sequence' => 'required',
            'group' => 'required'
        ]);
        $head = new SupplierSubHead;
        $head->name = $request->name;
        $head->group_id = $request->group;
        $head->sequence = $request->sequence;
        $head->status = $request->status;
        $head->company_id = Session::get('user_company_id');
        $head->created_by = Session::get('user_id');
        $head->created_at = Carbon::now();
        if($head->save()){
            return redirect()->route('supplier-sub-head.index')->with('success','Supplier Added Successfully');
        }else{
            //return redirect()->route('supplier.index')->with('success','Something Went Wrong.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $groups = ItemGroups::where('company_id', Session::get('user_company_id'))
                            ->where('delete', '=', '0')
                            ->where('status', '=', '1')
                            ->orderBy('group_name')
                            ->get();
        $head_data = SupplierSubHead::find($id);
        return view('supplier/edit_sub_head',["groups"=>$groups,"head_data"=>$head_data]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',
            'sequence' => 'required',
            'group' => 'required'
        ]);
        $head = SupplierSubHead::find($id);
        $head->name = $request->name;
        $head->group_id = $request->group;
        $head->sequence = $request->sequence;
        $head->status = $request->status;
        $head->created_by = Session::get('user_id');
        $head->updated_at = Carbon::now();
        if($head->save()){
            return redirect()->route('supplier-sub-head.index')->with('success','Supplier Updated Successfully');
        }else{
            //return redirect()->route('supplier.index')->with('success','Something Went Wrong.');
        }
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
