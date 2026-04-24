<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PrivilegesModule;
use App\Models\PrivilegesModuleMapping;
use App\Models\Companies;
use App\Helpers\CommonHelper;
use App\Models\Accounts;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;

class MerchantEmployeeController extends Controller
{
    public function index()
{
    Gate::authorize('view-module', 20);

    $employees = User::where('delete_status', '0')
        ->where('company_id', Session::get('user_company_id'))
        ->orderBy('id', 'DESC')
        ->get();

    return view('merchant_employee')->with('employee', $employees);
}


    public function create()
    {
        Gate::authorize('action-module', 81);
         $top_groups = [2,3, 11];

      // Step 2: Get all child group IDs recursively
      $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }

      // Remove duplicates just in case
      $group_ids = array_unique($all_groups);
      $party_list = Accounts::whereIn('company_id', [Session::get('user_company_id'),0])
                              ->where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('under_group',$group_ids)
                              ->select('accounts.id','accounts.account_name as name')
                              ->orderBy('name')
                              ->get(); 
        // ===== Salary / Wages Accounts (Group 12, 15 + children) =====
        // ===== Salary / Wages Account Groups =====
$salary_root_groups = [12, 15]; // DIRECT EXPENSE, INDIRECT EXPENSE

$salary_group_ids = [];

foreach ($salary_root_groups as $gid) {
    $salary_group_ids[] = $gid;

    // get all nested child groups
    $salary_group_ids = array_merge(
        $salary_group_ids,
        CommonHelper::getAllChildGroupIds(
            $gid,
            Session::get('user_company_id')
        )
    );
}

$salary_group_ids = array_unique($salary_group_ids);

// ===== Fetch Accounts under those groups =====
$salary_account_list = Accounts::whereIn('company_id', [
        Session::get('user_company_id'),
        0
    ])
    ->where('delete', '0')
    ->where('status', '1')
    ->whereIn('under_group', $salary_group_ids)
    ->select('id', 'account_name as name')
    ->orderBy('name')
    ->get();

        
    // ===== Payroll Heads =====
$payroll_heads = DB::table('payroll_heads')
    ->where('company_id', Session::get('user_company_id'))
    ->whereNotIn('type', ['esic', 'pf', 'tds'])
    ->orderBy('id')
    ->get();

return view('merchant_employee_add')
    ->with('party_list', $party_list)
    ->with('salary_account_list', $salary_account_list)
    ->with('payroll_heads', $payroll_heads);
    }

   public function store(Request $request)
{
    Gate::authorize('action-module', 81);

    // Check if email already exists
    if (User::where('email', $request->email)->exists()) {
        return redirect('manage-merchant-employee')->withError('Email already exists.');
    }

    // Handle file uploads
    $employee_photo = null;
    if ($request->hasFile('employee_photo')) {
        $file = $request->file('employee_photo');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/employees/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $employee_photo = 'uploads/employees/'.$filename;
    }

    $aadhar_file = null;
    if ($request->hasFile('aadhar_file')) {
        $file = $request->file('aadhar_file');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/aadhar/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $aadhar_file = 'uploads/aadhar/'.$filename;
    }

    $bank_passbook = null;
    if ($request->hasFile('bank_passbook')) {
        $file = $request->file('bank_passbook');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/bank_passbook/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $bank_passbook = 'uploads/bank_passbook/'.$filename;
    }

        $accountant_id_file = null;
    if ($request->hasFile('accountant_id_file')) {
        $file = $request->file('accountant_id_file');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/accountants/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $accountant_id_file = 'uploads/accountants/'.$filename;
    }

    $ca_id_file = null;
    if ($request->hasFile('ca_id_file')) {
        $file = $request->file('ca_id_file');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/ca/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $ca_id_file = 'uploads/ca/'.$filename;
    }

    $other_id_file = null;
    if ($request->hasFile('other_id_file')) {
        $file = $request->file('other_id_file');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/other/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $other_id_file = 'uploads/other/'.$filename;
    }

    // Create main user
    $user = new User;
    $user->name = $request->name;
    $user->email = $request->email;
    $user->mobile_no = $request->mobile;
    $user->password = Hash::make($request->mobile);
    $user->address = $request->address;
    $user->type = $request->type_of_user;
    $user->company_id = Session::get('user_company_id');
    $user->status = $request->status;

    if($request->type_of_user=="EMPLOYEE"){
    $user->branch = $request->employee_branch ?? null;
    $user->attendance_status = $request->attendance_status ?? 'Enable';
    $user->attendance_location = $request->attendance_location ?? null;
    $user->account_name = $request->salary_account ?? null;
    $user->salary_wages_account = $request->salary_wages_account ?? null;
    $user->salary = $request->salary_amount ?? null;
    $user->under = $request->salary_under ?? null;
    $user->tds_applicable = $request->tds_applicable ?? 'No';
    $user->esi_applicable = $request->esi_applicable ?? 'No';
    $user->pf_applicable = $request->pf_applicable ?? 'No';
    $user->start_date = $request->salary_start_date ?? null;    
    $user->edit_from = $request->salary_edit_from ?? null;
    $user->marital_status = $request->marital_status ?? 'Unmarried';
    $user->gender = $request->gender ?? null;
    $user->dob = $request->dob ?? null;
    $user->aadhar_id = $request->aadhar_id ?? null;
    $user->aadhar_attachment = $aadhar_file;
    $user->disability = $request->disability ?? 'No';
    $user->disability_type = $request->disability_type ?? null;
    $user->esic_number = $request->esic_number ?? null;
    $user->uan_number = $request->uan_number ?? null;
    $user->pan_number = $request->pan_number ?? null;
    $user->present_address = $request->present_address ?? null;
    $user->permanent_address = $request->permanent_address ?? null;
    $user->bank_name = $request->bank_name ?? null;
    $user->account_number = $request->bank_account_number ?? null;
    $user->ifsc_code = $request->bank_ifsc ?? null;
    $user->branch_address = $request->bank_branch_address ?? null;
    $user->passbook_attachment = $bank_passbook;
    }
    // Accountant, CA, Other
    elseif($request->type_of_user=="ACCOUNTANT"){
    $user->date_of_joining = $request->accountant_doj ?? null;
    $user->id_attachment = $accountant_id_file;
   }elseif($request->type_of_user=="CA"){
    $user->date_of_joining = $request->ca_doj ?? null;
    $user->id_attachment = $ca_id_file;
   }else{
      $user->date_of_joining = $request->other_doj ?? null;
      $user->id_attachment = $other_id_file;
   }
 
    $user->created_by = Session::get('user_id');
    $user->created_at = now();
    $user->save(); 

    $user_id = $user->id;
if ($request->type_of_user == "EMPLOYEE" && $request->has('salary_heads')) {

    foreach ($request->salary_heads as $headId => $amount) {

        $amount = floatval($amount);

        if ($amount != 0) {

            DB::table('user_salary_structures')->updateOrInsert(
                [
                    'company_id' => Session::get('user_company_id'),
                    'user_id' => $user_id,
                    'payroll_head_id' => $headId,
                ],
                [
                    'amount' => $amount,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
    // Save family members if any
    $family_names = $request->input('family_name', []);
    $family_relations = $request->input('family_relation', []);
    $family_dobs = $request->input('family_dob', []);
    $family_aadhars = $request->input('family_aadhar', []);
    $family_present_addresses = $request->input('family_present_address', []);
    $family_permanent_addresses = $request->input('family_permanent_address', []);
    $family_nominees = $request->input('family_nominee', []);
    $family_nominee_percent = $request->input('family_nominee_percent', []);
    $family_aadhar_files = $request->file('family_aadhar_file', []);

    for ($i = 0; $i < count($family_names); $i++) {
        if (!empty($family_names[$i])) {
            $aadharPath = null;
            if (isset($family_aadhar_files[$i])) {
                $file = $family_aadhar_files[$i];
                $filename = time().'_'.$i.'_'.$file->getClientOriginalName();
                $destination = storage_path('app/uploads/family_aadhar/'.$filename);

                $this->compressImage($file->getPathname(), $destination);
                $aadharPath = 'uploads/family_aadhar/'.$filename;
            }


            DB::table('employee_family_details')->insert([
                'user_id' => $user_id,
                'name' => $family_names[$i],
                'relation' => $family_relations[$i] ?? null,
                'dob' => $family_dobs[$i] ?? null,
                'aadhar_id' => $family_aadhars[$i] ?? null,
                'aadhar_attachment' => $aadharPath,
                'present_address' => $family_present_addresses[$i] ?? null,
                'permanent_address' => $family_permanent_addresses[$i] ?? null,
                'nominee' => $family_nominees[$i] ?? 'No',
                'nominee_percentage' => $family_nominee_percent[$i] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    return redirect('manage-merchant-employee')->withSuccess('User added successfully.');
}



    public function update(Request $request, $id)
{
    Gate::authorize('action-module', 34); 
    $user = User::findOrFail($id);

    // Check if email already exists for other users
    if (User::where('email', $request->email)->where('id', '!=', $id)->exists()) {
        return redirect()->back()->withError('Email already exists.');
    }

    // Handle file uploads
    $employee_photo = $user->photo;
    if ($request->hasFile('employee_photo')) {
        $this->deleteOldFile($user->photo);

        $file = $request->file('employee_photo');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/employees/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $employee_photo = 'uploads/employees/'.$filename;
    }

    $aadhar_file = $user->aadhar_attachment;
    if ($request->hasFile('aadhar_file')) {
        $this->deleteOldFile($user->aadhar_attachment);

        $file = $request->file('aadhar_file');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/aadhar/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $aadhar_file = 'uploads/aadhar/'.$filename;
    }

    $bank_passbook = $user->passbook_attachment;
    if ($request->hasFile('bank_passbook')) {
        $this->deleteOldFile($user->passbook_attachment);

        $file = $request->file('bank_passbook');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/bank_passbook/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $bank_passbook = 'uploads/bank_passbook/'.$filename;
    }

    $accountant_id_file = $user->id_attachment;
    if ($request->hasFile('accountant_id_file')) {
        $this->deleteOldFile($user->id_attachment);

        $file = $request->file('accountant_id_file');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/accountants/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $accountant_id_file = 'uploads/accountants/'.$filename;
    }

    $ca_id_file = $user->id_attachment;
    if ($request->hasFile('ca_id_file')) {
        $this->deleteOldFile($user->id_attachment);

        $file = $request->file('ca_id_file');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/ca/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $ca_id_file = 'uploads/ca/'.$filename;
    }

    $other_id_file = $user->id_attachment;
    if ($request->hasFile('other_id_file')) {
        $this->deleteOldFile($user->id_attachment);

        $file = $request->file('other_id_file');
        $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
        $destination = storage_path('app/uploads/other/'.$filename);

        $this->compressImage($file->getPathname(), $destination);
        $other_id_file = 'uploads/other/'.$filename;
    }

    // Update main user
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile_no = $request->mobile;
            $user->address = $request->address;
            $user->type = $request->type_of_user;
            $user->status = $request->status;

            // Only apply these if the user is an EMPLOYEE
            if($request->type_of_user == "EMPLOYEE") {
               $user->branch = $request->employee_branch ?? null;
               $user->attendance_status = $request->attendance_status ?? 'Enable';
               $user->attendance_location = $request->attendance_location ?? null;
               $user->photo = $employee_photo ?? null;
               $user->account_name = $request->salary_account ?? null;
               $user->salary = $request->salary_amount ?? null;
               $user->salary_wages_account = $request->salary_wages_account ?? null;
               $user->under = $request->salary_under ?? null;
               $user->tds_applicable = $request->tds_applicable ?? 'No';
               $user->esi_applicable = $request->esi_applicable ?? 'No';
               $user->pf_applicable = $request->pf_applicable ?? 'No';
               $user->start_date = $request->salary_start_date ?? null;
               $user->edit_from = $request->salary_edit_from ?? null;
               $user->marital_status = $request->marital_status ?? 'Unmarried';
               $user->gender = $request->gender ?? null;
               $user->dob = $request->dob ?? null;
               $user->aadhar_id = $request->aadhar_id ?? null;
               $user->aadhar_attachment = $aadhar_file ?? null;
               $user->disability = $request->disability ?? 'No';
               $user->disability_type = $request->disability_type ?? null;
               $user->esic_number = $request->esic_number ?? null;
               $user->uan_number = $request->uan_number ?? null;
               $user->pan_number = $request->pan_number ?? null;
               $user->present_address = $request->present_address ?? null;
               $user->permanent_address = $request->permanent_address ?? null;
               $user->bank_name = $request->bank_name ?? null;
               $user->account_number = $request->bank_account_number ?? null;
               $user->ifsc_code = $request->bank_ifsc ?? null;
               $user->branch_address = $request->bank_branch_address ?? null;
               $user->passbook_attachment = $bank_passbook ?? null;
            }

               // Accountant, CA, Other
               elseif($request->type_of_user=="ACCOUNTANT"){
               $user->date_of_joining = $request->accountant_doj ?? null;
               $user->id_attachment = $accountant_id_file;
               }elseif($request->type_of_user=="CA"){
               $user->date_of_joining = $request->ca_doj ?? null;
               $user->id_attachment = $ca_id_file;
               }else{
                  $user->date_of_joining = $request->other_doj ?? null;
                  $user->id_attachment = $other_id_file;
               }
               $user->updated_at = now();

               $user->save();
               // ===== Update Salary Structure =====
if ($request->type_of_user == "EMPLOYEE") {

    $existingHeads = DB::table('user_salary_structures')
        ->where('user_id', $user->id)
        ->pluck('payroll_head_id')
        ->toArray();

    $submittedHeads = array_keys($request->salary_heads ?? []);

    // Delete removed heads
    $headsToDelete = array_diff($existingHeads, $submittedHeads);

    if (!empty($headsToDelete)) {
        DB::table('user_salary_structures')
            ->where('user_id', $user->id)
            ->whereIn('payroll_head_id', $headsToDelete)
            ->delete();
    }

    // Insert / Update current heads
    if ($request->has('salary_heads')) {

        foreach ($request->salary_heads as $headId => $amount) {

            $amount = floatval($amount);

            if ($amount != 0) {

                DB::table('user_salary_structures')->updateOrInsert(
                    [
                        'company_id' => Session::get('user_company_id'),
                        'user_id' => $user->id,
                        'payroll_head_id' => $headId,
                    ],
                    [
                        'amount' => $amount,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
               $existingFamilyIds = DB::table('employee_family_details')
                  ->where('user_id', $user->id)
                  ->pluck('id')
                  ->toArray();

               $submittedFamilyIds = array_filter($request->input('family_id', []));

               $familyIdsToDelete = array_diff($existingFamilyIds, $submittedFamilyIds);

               if (!empty($familyIdsToDelete)) {

                  $attachments = DB::table('employee_family_details')
                        ->whereIn('id', $familyIdsToDelete)
                        ->pluck('aadhar_attachment');

                  foreach ($attachments as $file) {
                        $this->deleteOldFile($file);
                  }

                  DB::table('employee_family_details')
                        ->whereIn('id', $familyIdsToDelete)
                        ->delete();
               }
               // Update family members
               $family_ids = $request->input('family_id', []); 
               $family_names = $request->input('family_name', []);
               $family_relations = $request->input('family_relation', []);
               $family_dobs = $request->input('family_dob', []);
               $family_aadhars = $request->input('family_aadhar', []);
               $family_present_addresses = $request->input('family_present_address', []);
               $family_permanent_addresses = $request->input('family_permanent_address', []);
               $family_nominees = $request->input('family_nominee', []);
               $family_nominee_percent = $request->input('family_nominee_percent', []);
               $family_aadhar_files = $request->file('family_aadhar_file', []);

               for ($i = 0; $i < count($family_names); $i++) {
                  $aadharPath = null;

                  if (isset($family_aadhar_files[$i])) {

                        if (!empty($family_ids[$i])) {
                           $old = DB::table('employee_family_details')
                              ->where('id', $family_ids[$i])
                              ->value('aadhar_attachment');

                           $this->deleteOldFile($old);
                        }

                        $file = $family_aadhar_files[$i];
                        $filename = time().'_'.$i.'_'.$file->getClientOriginalName();
                        $destination = storage_path('app/uploads/family_aadhar/'.$filename);

                        $this->compressImage($file->getPathname(), $destination);
                        $aadharPath = 'uploads/family_aadhar/'.$filename;
                  }



                  if (!empty($family_names[$i])) {
                        if (isset($family_ids[$i]) && DB::table('employee_family_details')->where('id', $family_ids[$i])->exists()) {
                           // Update existing family member
                           DB::table('employee_family_details')->where('id', $family_ids[$i])->update([
                              'name' => $family_names[$i],
                              'relation' => $family_relations[$i] ?? null,
                              'dob' => $family_dobs[$i] ?? null,
                              'aadhar_id' => $family_aadhars[$i] ?? null,
                              'aadhar_attachment' => $aadharPath ?? DB::raw('aadhar_attachment'),
                              'present_address' => $family_present_addresses[$i] ?? null,
                              'permanent_address' => $family_permanent_addresses[$i] ?? null,
                              'nominee' => $family_nominees[$i] ?? 'No',
                              'nominee_percentage' => $family_nominee_percent[$i] ?? null,
                              'updated_at' => now(),
                           ]);
                        } else {
                           // Insert new family member
                           DB::table('employee_family_details')->insert([
                              'user_id' => $user->id,
                              'name' => $family_names[$i],
                              'relation' => $family_relations[$i] ?? null,
                              'dob' => $family_dobs[$i] ?? null,
                              'aadhar_id' => $family_aadhars[$i] ?? null,
                              'aadhar_attachment' => $aadharPath,
                              'present_address' => $family_present_addresses[$i] ?? null,
                              'permanent_address' => $family_permanent_addresses[$i] ?? null,
                              'nominee' => $family_nominees[$i] ?? 'No',
                              'nominee_percentage' => $family_nominee_percent[$i] ?? null,
                              'created_at' => now(),
                              'updated_at' => now(),
                           ]);
                        }
                  }
               }

    return redirect('manage-merchant-employee')->withSuccess('User updated successfully.');
}


   public function edit($id)
{
    Gate::authorize('action-module', 34); 
     $top_groups = [2,3, 11];

      // Step 2: Get all child group IDs recursively
      $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }

      // Remove duplicates just in case
      $group_ids = array_unique($all_groups);
      $party_list = Accounts::whereIn('company_id', [Session::get('user_company_id'),0])
                              ->where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('under_group',$group_ids)
                              ->select('accounts.id','accounts.account_name as name')
                              ->orderBy('name')
                              ->get();
        // ===== Salary / Wages Accounts  =====
        $salary_groups = [12, 15];

        $all_salary_groups = [];
        foreach ($salary_groups as $group_id) {
            $all_salary_groups[] = $group_id;
            $all_salary_groups = array_merge(
                $all_salary_groups,
                CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id'))
            );
        }

        $salary_group_ids = array_unique($all_salary_groups);

        $salary_account_list = Accounts::whereIn('company_id', [Session::get('user_company_id'), 0])
            ->where('delete', '0')
            ->where('status', '1')
            ->whereIn('under_group', $salary_group_ids)
            ->select('id', 'account_name as name')
            ->orderBy('name')
            ->get();

    $employee = User::findOrFail($id);

    // Fetch family details
    $family_details = DB::table('employee_family_details')
        ->where('user_id', $id)
        ->get(); // returns collection of objects

    // ===== Payroll Heads =====
$payroll_heads = DB::table('payroll_heads')
    ->where('company_id', Session::get('user_company_id'))
    ->whereNotIn('type', ['esic', 'pf', 'tds'])
    ->orderBy('id')
    ->get();
// ===== Existing Salary Structure =====
$user_salary_structure = DB::table('user_salary_structures')
    ->where('user_id', $id)
    ->pluck('amount', 'payroll_head_id')
    ->toArray();
return view('merchant_employee_edit', [
    'employee' => $employee,
    'family_details' => $family_details,
    'party_list' => $party_list,
    'salary_account_list' => $salary_account_list,
    'payroll_heads' => $payroll_heads,
    'user_salary_structure' => $user_salary_structure
]);
}

    public function destroy($id)
    {
        Gate::authorize('action-module', 35);

        User::where('id', $id)->update([
            'status' => '0',
            'delete_status' => '1',
            'deleted_at' => now(),
            'deleted_by' => Session::get('user_id')
        ]);

        // Optionally delete family members
        DB::table('employee_family_details')->where('user_id', $id)->delete();

        return redirect('manage-merchant-employee')->withSuccess('User deleted successfully.');
    }

    public function failedMessage($msg, $url)
    {
        return redirect($url)->withError($msg);
    }

    // Privileges
    public function employeePrivileges($id)
    {
        Gate::authorize('action-module', 36);

        $assign_privilege = PrivilegesModuleMapping::where('employee_id', $id)
            ->pluck('module_id')
            ->toArray();

        $privileges = PrivilegesModule::select('id', 'module_name', 'parent_id')
            ->where('status', 1)
            ->get()
            ->toArray();

        $tree = $this->buildTree($privileges);

        $user_id = Session::get('user_type') == "OWNER"
            ? Session::get('user_id')
            : Companies::where('id', Session::get('user_company_id'))->value('user_id');

        $company = Companies::select('id', 'company_name')
            ->where('user_id', $user_id)
            ->where('status', '1')
            ->where('delete', '0')
            ->get();

        return view('merchant_employee_privileges', [
            "privileges" => $tree,
            "employee_id" => $id,
            "company" => $company
        ]);
    }

    function buildTree(array $elements, $parentId = null)
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    public function setEmployeePrivileges(Request $request)
    {
        Gate::authorize('action-module', 36);

        if ($request->apply_all) {
            $companies = Companies::select('id')
                ->where('user_id', auth()->id())
                ->where('status', '1')
                ->where('delete', '0')
                ->get();

            foreach ($companies as $company) {
                PrivilegesModuleMapping::where('employee_id', $request->employee_id)
                    ->where('company_id', $company->id)
                    ->delete();

                if (!empty($request->privileges)) {
                    foreach ($request->privileges as $module_id) {
                        PrivilegesModuleMapping::create([
                            'module_id' => $module_id,
                            'employee_id' => $request->employee_id,
                            'company_id' => $company->id,
                            'created_at' => now(),
                        ]);
                    }
                }
            }
        } else {
            PrivilegesModuleMapping::where('employee_id', $request->employee_id)
                ->where('company_id', $request->company_id)
                ->delete();

            if (!empty($request->privileges)) {
                foreach ($request->privileges as $module_id) {
                    PrivilegesModuleMapping::create([
                        'module_id' => $module_id,
                        'employee_id' => $request->employee_id,
                        'company_id' => $request->company_id,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        return redirect('merchant-employee-privileges/' . $request->employee_id)
            ->withSuccess('Privileges updated successfully.');
    }

    private function compressImage($sourcePath, $destinationPath)
{
    $info = @getimagesize($sourcePath);

    // Not an image (PDF etc.)
    if (!$info) {
        copy($sourcePath, $destinationPath);
        return;
    }

    $mime = $info['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;

        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            $tmp = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagecopy($tmp, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            $image = $tmp;
            break;

        case 'image/webp':
            $image = imagecreatefromwebp($sourcePath);
            break;

        default:
            copy($sourcePath, $destinationPath);
            return;
    }

    // Fix rotation
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($sourcePath);
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 3: $image = imagerotate($image, 180, 0); break;
                case 6: $image = imagerotate($image, -90, 0); break;
                case 8: $image = imagerotate($image, 90, 0); break;
            }
        }
    }

    $width  = imagesx($image);
    $height = imagesy($image);

    $maxWidth = 1600;

    if ($width > $maxWidth) {
        $newWidth  = $maxWidth;
        $newHeight = intval($height * ($newWidth / $width));

        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($tmp, $image, 0, 0, 0, 0,
            $newWidth, $newHeight, $width, $height
        );
        $image = $tmp;
    }

    imagejpeg($image, $destinationPath, 88);
}
private function deleteOldFile($path)
{
    if ($path) {
        $fullPath = storage_path('app/' . $path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}

}
