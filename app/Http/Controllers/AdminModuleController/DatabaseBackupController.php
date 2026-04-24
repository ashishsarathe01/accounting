<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DatabaseBackupController extends Controller
{

    public function index()
    {
        return view('admin-module.database-backup.index');
    }

    public function download()
    {
        die("in Process.............");
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $tables = DB::select('SHOW TABLES');
    $sqlScript = "";

    foreach ($tables as $table) {

        $tableArray = (array) $table;
        $tableName = array_values($tableArray)[0];

        // Table structure
        $createTable = DB::select("SHOW CREATE TABLE `$tableName`");
        $sqlScript .= "\n\n".$createTable[0]->{'Create Table'}.";\n\n";

        // Table data chunk wise
        DB::table($tableName)->orderBy('id')->chunk(500, function ($rows) use (&$sqlScript, $tableName) {

            foreach ($rows as $row) {
                $values = array_map(function ($value) {
                    return '"'.addslashes($value).'"';
                }, (array) $row);

                $sqlScript .= "INSERT INTO `$tableName` VALUES(".implode(',', $values).");\n";
            }

        });

    }

    $fileName = "database_backup_".date('Y-m-d_H-i-s').".sql";

    return response($sqlScript)
        ->header('Content-Type', 'application/sql')
        ->header('Content-Disposition', "attachment; filename=$fileName");
    }
    
}