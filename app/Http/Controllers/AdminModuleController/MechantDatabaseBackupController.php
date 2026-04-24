<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use ZipArchive;
use Session;
use App\Models\Companies;
class MechantDatabaseBackupController extends Controller
{
    public function backupList(Request $request)
    {   
        $user_id = $request->user_id;
        $company_id = $request->company_id;
        $company_list = Companies::select('id','company_name','gst')
                                ->where('user_id',$request->user_id)
                                ->get();
        if($company_id=="" && count($company_list)>0){
            $company_id = $company_list[0]->id;
        }
        $backups = DB::table('backups')
                    ->orderBy('id','desc')
                    ->where('comp_id',$company_id)
                    ->get();
        return view('admin-module.merchant.db_backup_list',compact('backups','company_list','user_id','company_id'));
    }
    public function createBackup(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $status = $request->status;
        $tables = DB::select('SHOW TABLES');
        $sqlScript = "";
        foreach ($tables as $table) {
            $tableArray = (array)$table;
            $tableName = array_values($tableArray)[0];
            $columns = Schema::getColumnListing($tableName);
            // table structure
            $createTable = DB::select("SHOW CREATE TABLE `$tableName`");
            //$sqlScript .= "\n\n".$createTable[0]->{'Create Table'}.";\n\n";
            // table data
            if (in_array('company_id', $columns)) {
                // Best case: table has id
                DB::table($tableName)
                    ->where('company_id',$request->company_id)
                    ->orderBy('id')
                    ->chunk(500, function ($rows) use (&$sqlScript, $tableName) {
                        foreach ($rows as $row) {
                            $values = array_map(function ($value) {
                                return isset($value) ? '"' . addslashes($value) . '"' : 'NULL';
                            }, (array)$row);

                            $sqlScript .= "INSERT INTO `$tableName` VALUES(" . implode(',', $values) . ");\n";
                        }
                    });
                if($status && $status==1){
                    //DB::table($tableName)->where('company_id',$request->company_id)->delete();
                }
                
            }
        }
        $fileName = "backup_".date('Y-m-d_H-i-s').".sql";
        $path = storage_path('app/'.$fileName);
        file_put_contents($path,$sqlScript);
        // zip create
        $zipName = str_replace('.sql','.zip',$fileName);
        $zipPath = storage_path('app/'.$zipName);
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($path,$fileName);
            $zip->close();
        }
        unlink($path);
        DB::table('backups')->insert([
            'file_name'=>$zipName,
            'file_size'=>filesize($zipPath),
            'comp_id'=>$request->company_id,
            'created_by_admin'=>Session::get('admin_id')
        ]);
        //return response()->download($zipPath);
        return redirect()
        ->back()
        ->with('success', 'Backup created successfully.')
        ->with('download_file', $zipName);
    }
    public function deleteBackup(Request $request)
    {   
        $backup = DB::table('backups')
                    ->find($request->id);
        if($backup){
            $path = storage_path('app/' . $backup->file_name);
            if (file_exists($path)) {
                unlink($path);
            }
            DB::table('backups')->where('id', $request->id)->delete();
            return back()->with('success', 'BackUp File Deleted.');
        }else{
            return back()->with('error', 'BackUp File Not Found.');
        }
    }
    public function restoreBackup(Request $request)
    {
        die("Not Allowed");
        $backup = DB::table('backups')
                    ->find($request->id);
        if($backup){
            $path = storage_path('app/' . $backup->file_name);
            if (file_exists($path)) {
                $sql = file_get_contents($path);
                DB::unprepared($sql);
                return back()->with('success', 'Database Restored.');
            }else{
                return back()->with('error', 'BackUp File Not Found.');
            }            
        }else{
            return back()->with('error', 'BackUp File Not Found.');
        }
    }
}
