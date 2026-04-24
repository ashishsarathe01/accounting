<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceType;

class AttendanceTypeController extends Controller
{
    public function index()
    {
        // Get all attendance types (global)
        $types = AttendanceType::orderBy('id','desc')->get();

        return view('admin-module.attendance-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        // Basic validation
        $request->validate([
            'type_name.*' => 'required|string|max:255'
        ]);

        if($request->type_name)
        {
            foreach($request->type_name as $type)
            {
                if(!empty($type))
                {
                    AttendanceType::create([
                        'type_name' => trim($type)
                    ]);
                }
            }
        }

        return redirect()->back()->with('success','Attendance Types Saved Successfully');
    }

    public function delete($id)
    {
        $type = AttendanceType::find($id);

        if($type)
        {
            $type->delete();
        }

        return redirect()->back()->with('success','Deleted Successfully');
    }
}