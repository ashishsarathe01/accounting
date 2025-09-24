<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class ManageUserController extends Controller
{
    // List all users
    public function index()
    {
        $users = DB::table('admin_users')->get();
        return view('admin-module.manageUser.user', compact('users'));
    }

    // Add user form
    public function create()
    {
        return view('admin-module.manageUser.create');
    }

    // Store new user
    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'mobile'=>'required',
            'email'=>'required|email',
            'marital_status'=>'required',
            'gender'=>'required',
            'dob'=>'required|date',
            'aadhar_id'=>'required',
            'present_address'=>'required',
            'permanent_address'=>'required',
            'date_of_appointment'=>'required|date',
            'status'=>'required'
        ]);

        $data = $request->only([
            'name','mobile','email','marital_status','gender','dob','aadhar_id',
            'present_address','permanent_address','date_of_appointment','status'
        ]);

        // Handle Aadhar file upload
        if ($request->hasFile('aadhar_image')) {
            $file = $request->file('aadhar_image');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/aadhar'), $filename);
            $data['aadhar_image'] = $filename;
        }

        DB::table('admin_users')->insert($data);

        return redirect()->route('admin.manageUser.index')->with('success','User added successfully');
    }

    // Edit user form
    public function edit($id)
    {
        $user = DB::table('admin_users')->where('id',$id)->first();
        return view('admin-module.manageUser.edit', compact('user'));
    }

    // Update user
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'=>'required',
            'mobile'=>'required',
            'email'=>'required|email',
            'marital_status'=>'required',
            'gender'=>'required',
            'dob'=>'required|date',
            'aadhar_id'=>'required',
            'present_address'=>'required',
            'permanent_address'=>'required',
            'date_of_appointment'=>'required|date',
            'status'=>'required'
        ]);

        $data = $request->only([
            'name','mobile','email','marital_status','gender','dob','aadhar_id',
            'present_address','permanent_address','date_of_appointment','status'
        ]);

        // Handle Aadhar file upload
        if ($request->hasFile('aadhar_image')) {
            $file = $request->file('aadhar_image');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/aadhar'), $filename);
            $data['aadhar_image'] = $filename;
        }

        DB::table('admin_users')->where('id',$id)->update($data);

        return redirect()->route('admin.manageUser.index')->with('success','User updated successfully');
    }

    // Delete user
    public function destroy($id)
    {
        DB::table('admin_users')->where('id',$id)->delete();
        return redirect()->route('admin.manageUser.index')->with('success','User deleted successfully');
    }
}
