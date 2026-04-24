<?php

namespace App\Http\Controllers\DbBackup;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use ZipArchive;
use Session;
class BackUpController extends Controller
{
    public function createBackup()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
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
                    ->where('company_id',Session::get('user_company_id'))
                    ->orderBy('id')
                    ->chunk(500, function ($rows) use (&$sqlScript, $tableName) {
                        foreach ($rows as $row) {
                            $values = array_map(function ($value) {
                                return isset($value) ? '"' . addslashes($value) . '"' : 'NULL';
                            }, (array)$row);

                            $sqlScript .= "INSERT INTO `$tableName` VALUES(" . implode(',', $values) . ");\n";
                        }
                    });
                //DB::table($tableName1)->where('company_id',Session::get('user_company_id'))->delete();
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
            'comp_id'=>Session::get('user_company_id'),
            'created_by'=>Session::get('user_id')
        ]);
        return response()->download($zipPath);
    }
    public function restoreBackup($file)
    {
        $path = storage_path('app/'.$file);

        $sql = file_get_contents($path);

        DB::unprepared($sql);

        return back()->with('success','Database Restored');
    }
    public function restoreBackupImportFile(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,zip'
        ]);
    
        $file = $request->file('backup_file');
        $extension = $file->getClientOriginalExtension();
    
        // store file
        $fileName = time() . '.' . $extension;
        $path = $file->storeAs('temp_restore', $fileName);
        $fullPath = storage_path('app/' . $path);
    
        // if zip → extract
        if ($extension === 'zip') {
    
            $zip = new ZipArchive;
            if ($zip->open($fullPath) === TRUE) {
    
                $extractPath = storage_path('app/temp_restore/');
                $zip->extractTo($extractPath);
                $zip->close();
    
                $files = glob($extractPath . '*.sql');
    
                if (empty($files)) {
                    return back()->with('error', 'No SQL file found in zip');
                }
    
                $sqlFile = $files[0];
    
            } else {
                return back()->with('error', 'Unable to open zip file');
            }
    
        } else {
            $sqlFile = $fullPath;
        }
    
        // run sql safely
        $this->runSqlFile($sqlFile);
    
        // cleanup
        @unlink($sqlFile);
    
        return back()->with('success', 'Database Restored Successfully');
    }
    private function runSqlFile($filePath)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
        $handle = fopen($filePath, 'r');
        $query = '';
    
        while (($line = fgets($handle)) !== false) {
    
            $line = trim($line);
    
            if ($line == '' || str_starts_with($line, '--')) {
                continue;
            }
    
            $query .= $line;
    
            if (str_ends_with($line, ';')) {
                DB::unprepared($query);
                $query = '';
            }
        }
    
        fclose($handle);
    
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
    public function backupList()
    {
        $backups = DB::table('backups')->where('comp_id',Session::get('user_company_id'))->orderBy('id','desc')->get();
        return view('DbBackup.index',compact('backups'));
    }
}
