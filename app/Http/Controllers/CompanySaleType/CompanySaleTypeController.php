<?php

namespace App\Http\Controllers\CompanySaleType;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use DB;

class CompanySaleTypeController extends Controller
{
    public function index()
    {
        $saleTypes = DB::table('company_sale_types')

        ->orderBy('id','DESC')

        ->get();

        return view(
            'admin-module.CompanySaleType.CompanySaleTypeList',
            compact('saleTypes')
        );
    }

    public function create()
    {
        return view(
            'admin-module.CompanySaleType.AddCompanySaleType'
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'sale_type' => 'required'

        ]);

        DB::table('company_sale_types')

        ->insert([

            'sale_type' => $request->sale_type,

            'created_at' => now(),

            'updated_at' => now()

        ]);

        return redirect()

        ->route(
            'admin.company-sale-types.index'
        )

        ->with(
            'success',
            'Company Sale Type Added Successfully'
        );
    }

    public function edit($id)
    {
        $saleType = DB::table('company_sale_types')

        ->where('id',$id)

        ->first();

        return view(
            'admin-module.CompanySaleType.EditCompanySaleType',
            compact('saleType')
        );
    }

    public function update(Request $request,$id)
    {
        $request->validate([

            'sale_type' => 'required'

        ]);

        DB::table('company_sale_types')

        ->where('id',$id)

        ->update([

            'sale_type' => $request->sale_type,

            'updated_at' => now()

        ]);

        return redirect()

        ->route(
            'admin.company-sale-types.index'
        )

        ->with(
            'success',
            'Company Sale Type Updated Successfully'
        );
    }

    public function delete(Request $request)
    {
        DB::table('company_sale_types')

        ->where(
            'id',
            $request->id
        )

        ->delete();

        return redirect()

        ->back()

        ->with(
            'success',
            'Company Sale Type Deleted Successfully'
        );
    }

    public function setCompanySaleType()
    {
        $companies = DB::table('companies')

        ->where('status','1')

        ->where('delete','0')

        ->orderBy('company_name','ASC')

        ->get();

        $saleTypes = DB::table('company_sale_types')

        ->orderBy('sale_type','ASC')

        ->get();

        foreach($saleTypes as $saleType)
        {
            $saleType->sale_type = strtoupper(
                $saleType->sale_type
            );
        }

        return view(
            'admin-module.CompanySaleType.SetCompanySaleType',
            compact(
                'companies',
                'saleTypes'
            )
        );
    }
    public function updateCompanySaleType(Request $request)
    {
        if($request->company_sale_type)
        {
            foreach(
                $request->company_sale_type
                as $companyId => $saleType
            )
            {
                DB::table('companies')

                ->where('id',$companyId)

                ->update([

                    'company_sale_type' =>
                    strtoupper($saleType)

                ]);
            }
        }

        return redirect()

        ->back()

        ->with(
            'success',
            'Company Sale Type Updated Successfully'
        );
    }
}