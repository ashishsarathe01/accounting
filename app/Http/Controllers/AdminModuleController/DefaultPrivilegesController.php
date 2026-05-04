<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PrivilegesModule;
use Carbon\Carbon;

class DefaultPrivilegesController extends Controller
{
    public function index()
    {
        $assign_privilege = DB::table('default_privilege_mappings')
            ->pluck('module_id')
            ->toArray();

        $privileges = PrivilegesModule::select('id', 'module_name', 'parent_id')
            ->where('status', 1)
            ->get()
            ->toArray();

        $tree = $this->buildTree($privileges);

        return view('admin-module.default-privileges.index', [
            "privileges" => $tree,
            "assign_privilege" => $assign_privilege
        ]);
    }
    private function buildTree(array $elements, $parentId = null)
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
    public function store(Request $request)
    {
        $selected_modules = $request->privileges ?? [];

        foreach ($selected_modules as $module_id) {

            $parent_id = PrivilegesModule::where('id', $module_id)
                ->value('parent_id');

            while ($parent_id) {

                if (!in_array($parent_id, $selected_modules)) {
                    $selected_modules[] = $parent_id;
                }

                $parent_id = PrivilegesModule::where('id', $parent_id)
                    ->value('parent_id');
            }
        }

        $selected_modules = array_unique($selected_modules);

        DB::beginTransaction();

        try {

            DB::table('default_privilege_mappings')->delete();

            foreach ($selected_modules as $module_id) {
                DB::table('default_privilege_mappings')->insert([
                    'module_id' => $module_id,
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->back()->withSuccess('Default privileges updated successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()->back()->withError('Something went wrong.');
        }
    }
}