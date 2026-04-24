<?php

namespace App\Http\Controllers\TaskManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Session;
use DB;

class TaskManagerController extends Controller
{

    public function index(Request $request)
    {
        $this->generateMonthlyTasks();

        $company_id = Session::get('user_company_id');
        $user_id    = Session::get('user_id');

        $query = DB::table('tasks as t')
        ->leftJoin('users as u1', 't.assigned_to', '=', 'u1.id')
        ->leftJoin('users as u2', 't.created_by', '=', 'u2.id')
        ->where('t.company_id', $company_id)
        ->whereNull('t.deleted_at');

        if($request->employee){
            $query->where('t.created_by', $request->employee);
        } else {
            $query->where('t.created_by', $user_id);
        }

        $query->select(
            't.*',
            'u1.name as assigned_user',
            'u2.name as created_user'
        );


        if ($request->filled('from_date') && $request->filled('to_date')) {

            $from = \Carbon\Carbon::parse($request->from_date)->startOfDay();
            $to   = \Carbon\Carbon::parse($request->to_date)->endOfDay();

            $query->where('t.status', 'completed')
                ->whereBetween('t.updated_at', [$from, $to]);
        }
        else {


            if ($request->priority && $request->priority != 'all') {
                $query->where('t.priority', $request->priority);
            }

            if ($request->status && $request->status != 'all') {
                $query->where('t.status', $request->status);
            }
        }

        $tasks = $query->orderBy('t.id','DESC')->get();

        $users = $this->getCompanyUsers($company_id);

        return view('TaskManager.index', compact('tasks','users'));
    }

    public function create()
    {
        $company_id = Session::get('user_company_id');

        $users = $this->getCompanyUsers($company_id);

        return view('TaskManager.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|max:255',
            'assigned_to' => 'required',
            'deadline'    => 'required|date',
            'priority'    => 'required'
        ]);

        DB::table('tasks')->insert([
            'company_id' => Session::get('user_company_id'),
            'created_by' => Session::get('user_id'),
            'assigned_to'=> $request->assigned_to,
            'title'      => $request->title,
            'description'=> $request->description,
            'priority'   => $request->priority,
            'status'     => 'pending',
            'is_notification_read' => 0, 
            'deadline'   => $request->deadline,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('task.index')
            ->with('success','Task Assigned Successfully');
    }

    public function edit($id)
    {
        $company_id = Session::get('user_company_id');
        $user_id    = Session::get('user_id');

        $task = DB::table('tasks')
            ->where('id', $id)
            ->where('company_id', $company_id)
            ->where('created_by', $user_id)     
            ->whereNull('parent_task_id')       
            ->whereNull('deleted_at')
            ->first();

        if(!$task){
            return redirect()->route('task.index')
                ->with('error','Unauthorized access');
        }

        $users = $this->getCompanyUsers($company_id);

        return view('TaskManager.edit', compact('task','users'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'title'       => 'required|max:255',
            'assigned_to' => 'required',
            'deadline'    => 'required|date',
            'priority'    => 'required'
        ]);

        DB::table('tasks')
            ->where('id', $request->task_id)
            ->update([
                'assigned_to' => $request->assigned_to,
                'title'       => $request->title,
                'description' => $request->description,
                'priority'    => $request->priority,
                'deadline'    => $request->deadline,
                'updated_at'  => now(),
            ]);
        $task = DB::table('tasks')
            ->where('id', $request->task_id)
            ->where('created_by', Session::get('user_id'))
            ->whereNull('parent_task_id')
            ->first();

        if(!$task){
            return back()->with('error','Unauthorized update');
        }

        return redirect()->route('task.index')
            ->with('success','Task Updated Successfully');
    }

    public function delete(Request $request)
    {
        $user_id = Session::get('user_id');

        $task = DB::table('tasks')
            ->where('id', $request->task_id)
            ->where('created_by', $user_id)
            ->whereNull('parent_task_id')
            ->first();

        if(!$task){
            return back()->with('error','Unauthorized delete');
        }

        DB::beginTransaction();

        try {

            $taskIds = DB::table('tasks')
                ->where(function($query) use ($task) {
                    $query->where('root_task_id', $task->id)
                        ->orWhere('id', $task->id);
                })
                ->pluck('id');

            DB::table('tasks')
                ->whereIn('id', $taskIds)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);


            DB::table('tasks')
                ->whereIn('id', $taskIds)
                ->update(['is_notification_read' => 1]);

            DB::table('task_responses')
                ->whereIn('task_id', $taskIds)
                ->update(['is_read' => 1]);

            DB::table('task_logs')
                ->whereIn('task_id', $taskIds)
                ->update(['is_read' => 1]);

            DB::commit();

            return redirect()->back()
                ->with('success','Task & notifications cleared successfully');

        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function detail($id)
    {
        $company_id = Session::get('user_company_id');
        $user_id    = Session::get('user_id');

        $task = DB::table('tasks as t')
    ->leftJoin('users as u1','t.created_by','=','u1.id')
    ->leftJoin('users as u2','t.assigned_to','=','u2.id')
    ->leftJoin('companies as c','t.company_id','=','c.id')
    ->where('t.id',$id)
    ->whereNull('t.deleted_at')
    ->select(
        't.*',
        'u1.name as created_by_name',
        'u2.name as assigned_to_name',
        'c.company_name'
    )
    ->first();

if(!$task){
    return redirect()->back()->with('error','Task not found');
}

/*
|--------------------------------------------------------------------------
| 🚨 COMPANY MISMATCH CHECK
|--------------------------------------------------------------------------
*/
if($task->company_id != $company_id){

    return redirect()
    ->route('dashboard')
    ->with(
        'company_mismatch',
        "This task is assigned to you for {$task->company_name}. Please switch company to view this task."
    );
}

        DB::table('tasks')
            ->where('id', $id)
            ->where('assigned_to', $user_id)
            ->where('is_notification_read', 0)
            ->update([
                'is_notification_read' => 1
            ]);

        DB::table('task_responses')
            ->where('task_id', $id)
            ->where('user_id', '!=', $user_id)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1
            ]);

        DB::table('task_logs')
            ->where('task_id', $id)
            ->where('user_id', '!=', $user_id)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1
            ]);

        $logs = DB::table('task_logs as l')
            ->leftJoin('users as u','l.user_id','=','u.id')
            ->where('l.task_id',$id)
            ->select(
                'l.created_at',
                'l.user_id',
                'u.name',
                'l.description',
                DB::raw("'system' as message_type")
            );

        $responses = DB::table('task_responses as r')
            ->leftJoin('users as u','r.user_id','=','u.id')
            ->where('r.task_id',$id)
            ->select(
                'r.created_at',
                'r.user_id',
                'u.name',
                'r.message as description',
                DB::raw("'user' as message_type")
            );

        $timeline = $logs
            ->unionAll($responses)
            ->orderBy('created_at','ASC')
            ->get();


        $childTask = DB::table('tasks')
            ->where('parent_task_id', $task->id)
            ->whereNull('deleted_at')
            ->first();

        return view('TaskManager.detail', compact('task','timeline','childTask'));
    }

    public function myTasks(Request $request)
    {
        $this->generateMonthlyTasks();

        $company_id = Session::get('user_company_id');
        $user_id    = Session::get('user_id');

        DB::table('tasks')
            ->where('assigned_to', $user_id)
            ->where('is_notification_read', 0)
            ->update([
                'is_notification_read' => 1,
                'updated_at' => now()
            ]);

        $query = DB::table('tasks')
        ->where('company_id', $company_id)
        ->whereNull('deleted_at');

        if($request->employee){
            $query->where('assigned_to', $request->employee);
        } else {
            $query->where('assigned_to', $user_id);
        }

        if ($request->priority && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->status && $request->status != 'all') {

            if ($request->status == 'overdue') {

                $query->where('status', '!=', 'completed')
                    ->where('deadline', '<', now());

            } elseif ($request->status == 'completed') {

                $query->where('status', 'completed');


                if ($request->filled('from_date') && $request->filled('to_date')) {

                    $from = \Carbon\Carbon::parse($request->from_date)->startOfDay();
                    $to   = \Carbon\Carbon::parse($request->to_date)->endOfDay();

                    $query->whereBetween('updated_at', [$from, $to]);
                }

            } else {

                $query->where('status', $request->status);
            }
        }

        $tasks = $query->orderBy('deadline', 'ASC')->get();

        $users = $this->getCompanyUsers($company_id);

        return view('TaskManager.my_tasks', compact('tasks','users'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
            'status'  => 'required'
        ]);

        $user_id = Session::get('user_id');

        $task = DB::table('tasks')->where('id',$request->task_id)->first();

        

        if($task->status == $request->status){
            return back()->with('error','Task already in this status');
        }

        $update = [
            'status' => $request->status,
            'updated_at' => now()
        ];

        if($request->status == 'in_progress' && !$task->started_at){
            $update['started_at'] = now();
        }

        if($request->status == 'completed'){

            $update['completed_at'] = now();

            

        } else {
            $update['completed_at'] = null;
        }

        DB::table('tasks')
            ->where('id',$task->id)
            ->update($update);

        DB::table('task_logs')->insert([
            'task_id' => $task->id,
            'user_id' => $user_id,
            'activity_type' => 'status_changed',
            'description' => "Status changed to {$request->status}",
            'is_read' => 0,
            'created_at' => now(),
        ]);

        return back()->with('success','Status Updated');
    }

    public function addResponse(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
            'message' => 'required'
        ]);

        $user_id = Session::get('user_id');

        $task = DB::table('tasks')->where('id',$request->task_id)->first();

        if(!$task){
            return back()->with('error','Task not found');
        }

        $isCreator  = $user_id == $task->created_by;
        $isAssignee = $user_id == $task->assigned_to;

        if(!$isCreator && !$isAssignee){
            return back()->with('error','Unauthorized');
        }

        if($task->status == 'completed' && !$isCreator){
            return back()->with('error','Task is completed. Only creator can respond.');
        }

        DB::table('task_responses')->insert([
            'task_id' => $request->task_id,
            'user_id' => $user_id,
            'response_type' => 'chat',
            'message' => $request->message,
            'is_read' => 0, 
            'created_at' => now(),
        ]);

        return back()->with('success','Message Sent');
    }

    public function approveTask($id)
    {
        $user_id = Session::get('user_id');

        $task = DB::table('tasks')->where('id',$id)->first();

        if(!$task){
            return back()->with('error','Task not found');
        }

        $parent = DB::table('tasks')->where('id',$task->parent_task_id)->first();

        if(!$parent || $parent->assigned_to != $user_id){
            return back()->with('error','Unauthorized approval');
        }

        DB::table('tasks')
            ->where('id',$task->id)
            ->update([
                'approved_at' => now(),
                'approved_by' => $user_id
            ]);

        DB::table('tasks')
            ->where('id',$parent->id)
            ->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

        DB::table('task_logs')->insert([
            'task_id' => $task->id,
            'user_id' => $user_id,
            'activity_type' => 'approved',
            'description' => "Task approved",
            'is_read' => 0,
            'created_at' => now(),
        ]);

        return back()->with('success','Task Approved');
    }
    public function delegateTask(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
            'assigned_to' => 'required'
        ]);

        $user_id = Session::get('user_id');

        $task = DB::table('tasks')->where('id',$request->task_id)->first();

        if(!$task || $task->assigned_to != $user_id){
            return back()->with('error','Unauthorized delegation');
        }

        if(!$task->delegation_allowed){
            return back()->with('error','Further delegation not allowed');
        }

        $newTaskId = DB::table('tasks')->insertGetId([
            'company_id' => $task->company_id,
            'parent_task_id' => $task->id,
            'root_task_id' => $task->root_task_id ?? $task->id,
            'created_by' => $user_id,
            'assigned_to' => $request->assigned_to,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'deadline' => $task->deadline,
            'delegation_allowed' => $request->delegation_allowed ?? 0,
            'status' => 'pending',
            'is_notification_read' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('task_logs')->insert([
            'task_id' => $task->id,
            'user_id' => $user_id,
            'activity_type' => 'delegated',
            'description' => "Task delegated",
            'is_read' => 0,
            'created_at' => now(),
        ]);

        return back()->with('success','Task Delegated');
    }

    public function delegatedByMe()
    {
        $company_id = Session::get('user_company_id');
        $user_id    = Session::get('user_id');

        $tasks = DB::table('tasks as t')
            ->leftJoin('users as u1', 't.assigned_to', '=', 'u1.id')
            ->where('t.company_id', $company_id)
            ->where('t.created_by', $user_id)
            ->whereNotNull('t.parent_task_id')
            ->whereNull('t.deleted_at')
            ->select('t.*', 'u1.name as assigned_user')
            ->orderBy('t.id','DESC')
            ->get();

        return view('TaskManager.delegated_by_me', compact('tasks'));
    }

    public function monthlyIndex()
    {
        $company_id = Session::get('user_company_id');

        $templates = DB::table('monthly_task_templates as m')
            ->leftJoin('users as u','m.assigned_to','=','u.id')
            ->where('m.company_id',$company_id)
            ->select('m.*','u.name as assigned_user')
            ->orderBy('m.id','DESC')
            ->get();

        return view('TaskManager.monthly_index', compact('templates'));
    }
    public function monthlyCreate()
    {
        $company_id = Session::get('user_company_id');

        $users = $this->getCompanyUsers($company_id);

        return view('TaskManager.monthly_create', compact('users'));
    }
    public function monthlyStore(Request $request)
    {
        $request->validate([
            'assigned_to' => 'required',
            'title.*' => 'required',
            'start_day.*' => 'required|integer|min:1|max:31',
            'end_day.*' => 'required|integer|min:1|max:31',
        ]);

        foreach($request->title as $key => $title){

            DB::table('monthly_task_templates')->insert([
                'company_id' => Session::get('user_company_id'),
                'created_by' => Session::get('user_id'),
                'assigned_to'=> $request->assigned_to,
                'title'      => $title,
                'description'=> $request->description[$key] ?? null,
                'priority'   => $request->priority[$key],
                'start_day'  => $request->start_day[$key],
                'end_day'    => $request->end_day[$key],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('task.monthly.index')
            ->with('success','Monthly Task Created Successfully');
    }
    public function monthlyEdit($id)
    {
        $template = DB::table('monthly_task_templates')
            ->where('id',$id)
            ->first();

        $company_id = Session::get('user_company_id');
        $users = $this->getCompanyUsers($company_id);

        return view('TaskManager.monthly_edit', compact('template','users'));
    }
    public function monthlyUpdate(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'assigned_to' => 'required',
            'start_day' => 'required|integer|min:1|max:31',
            'end_day' => 'required|integer|min:1|max:31',
        ]);

        DB::table('monthly_task_templates')
            ->where('id',$request->id)
            ->update([
                'title' => $request->title,
                'description' => $request->description,
                'priority' => $request->priority,
                'assigned_to' => $request->assigned_to,
                'start_day' => $request->start_day,
                'end_day' => $request->end_day,
                'updated_at' => now(),
            ]);

        $monthlyTasks = DB::table('tasks')
            ->where('monthly_template_id', $request->id)
            ->where('is_monthly', 1)
            ->whereNull('deleted_at')
            ->get();

        foreach($monthlyTasks as $task){

            $newDeadline = \Carbon\Carbon::create(
                $task->monthly_year,
                $task->monthly_month,
                $request->end_day,
                23,59,59
            );

            DB::table('tasks')
                ->where('id', $task->id)
                ->update([
                    'title' => $request->title,
                    'description' => $request->description,
                    'priority' => $request->priority,
                    'assigned_to' => $request->assigned_to,
                    'deadline' => $newDeadline,
                    'updated_at' => now(),
                ]);
        }

        return redirect()->route('task.monthly.index')
            ->with('success','Monthly Task Updated Successfully');
    }

    private function generateMonthlyTasks()
    {
        $todayDay = date('j');
        $currentMonth = date('n');
        $currentYear = date('Y');

        $templates = DB::table('monthly_task_templates')
            ->where('company_id', Session::get('user_company_id'))
            ->get();

        foreach($templates as $template){

            if($todayDay >= $template->start_day){

                $exists = DB::table('tasks')
                    ->where('monthly_template_id', $template->id)
                    ->where('monthly_month', $currentMonth)
                    ->where('monthly_year', $currentYear)
                    ->exists();

                if(!$exists){

                    $deadline = \Carbon\Carbon::create(
                        $currentYear,
                        $currentMonth,
                        $template->end_day,
                        23,59,59
                    );

                    DB::table('tasks')->insert([
                        'company_id' => $template->company_id,
                        'created_by' => $template->created_by,
                        'assigned_to'=> $template->assigned_to,
                        'title'      => $template->title,
                        'description'=> $template->description,
                        'priority'   => $template->priority,
                        'status'     => 'pending',
                        'deadline'   => $deadline,
                        'is_notification_read' => 0,
                        'is_monthly' => 1,
                        'monthly_template_id' => $template->id,
                        'monthly_month' => $currentMonth,
                        'monthly_year'  => $currentYear,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function monthlyDelete(Request $request)
    {
        $templateId = $request->monthly_task_id;

        DB::table('tasks')
            ->where('monthly_template_id', $templateId)
            ->where('is_monthly', 1)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now()
            ]);

        DB::table('monthly_task_templates')
            ->where('id', $templateId)
            ->delete();

        return back()->with('success','Monthly Task Deleted Successfully');
    }
    private function getCompanyUsers($company_id)
    {
        $company = DB::table('companies')
            ->where('id', $company_id)
            ->first();

        return User::where(function ($q) use ($company_id, $company) {
                $q->where('company_id', $company_id)      // Employees
                ->orWhere('id', $company->user_id);    // Owner
            })
            ->where('delete_status', '0')
            ->where('status', '1')
            ->get();
    }

}
