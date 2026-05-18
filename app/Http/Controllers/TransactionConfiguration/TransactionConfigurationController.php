<?php

namespace App\Http\Controllers\TransactionConfiguration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionConfigurationController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Credit Note Configuration Page
    |--------------------------------------------------------------------------
    */

    public function creditNoteConfiguration()
    {

        $config = DB::table('transaction_configurations')

            ->where('transaction_type', 'credit_note')

            ->where(
                'config_key',
                'link_gstr2b_debit_note_edit'
            )

            ->where(
                'comp_id',
                session('user_company_id')
            )

            ->first();

        return view(
            'TransactionConfiguration.CreditNoteConfiguration',
            compact('config')
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Save Credit Note Configuration
    |--------------------------------------------------------------------------
    */

    public function saveCreditNoteConfiguration(Request $request)
    {

        $exists = DB::table('transaction_configurations')

            ->where('transaction_type', 'credit_note')

            ->where(
                'config_key',
                'link_gstr2b_debit_note_edit'
            )

            ->where(
                'comp_id',
                session('user_company_id')
            )

            ->first();



        if($exists){

            DB::table('transaction_configurations')

                ->where('id', $exists->id)

                ->update([

                    'config_value' =>
                        $request->link_gstr2b_debit_note_edit,

                    'updated_at' => now()

                ]);

        } else {

            DB::table('transaction_configurations')

                ->insert([

                    'transaction_type' => 'credit_note',

                    'config_key' =>
                        'link_gstr2b_debit_note_edit',

                    'config_value' =>
                        $request->link_gstr2b_debit_note_edit,

                    'comp_id' =>
                        session('user_company_id'),

                    'created_at' => now(),

                    'updated_at' => now()

                ]);

        }

        return back()->with(
            'success',
            'Credit Note Configuration Saved Successfully'
        );
    }




    /*
    |--------------------------------------------------------------------------
    | Debit Note Configuration Page
    |--------------------------------------------------------------------------
    */

    public function debitNoteConfiguration()
    {

        $config = DB::table('transaction_configurations')

            ->where('transaction_type', 'debit_note')

            ->where(
                'config_key',
                'link_gstr2b_credit_note_edit'
            )

            ->where(
                'comp_id',
                session('user_company_id')
            )

            ->first();

        return view(
            'TransactionConfiguration.DebitNoteConfiguration',
            compact('config')
        );
    }




    /*
    |--------------------------------------------------------------------------
    | Save Debit Note Configuration
    |--------------------------------------------------------------------------
    */

    public function saveDebitNoteConfiguration(Request $request)
    {

        $exists = DB::table('transaction_configurations')

            ->where('transaction_type', 'debit_note')

            ->where(
                'config_key',
                'link_gstr2b_credit_note_edit'
            )

            ->where(
                'comp_id',
                session('user_company_id')
            )

            ->first();



        if($exists){

            DB::table('transaction_configurations')

                ->where('id', $exists->id)

                ->update([

                    'config_value' =>
                        $request->link_gstr2b_credit_note_edit,

                    'updated_at' => now()

                ]);

        } else {

            DB::table('transaction_configurations')

                ->insert([

                    'transaction_type' => 'debit_note',

                    'config_key' =>
                        'link_gstr2b_credit_note_edit',

                    'config_value' =>
                        $request->link_gstr2b_credit_note_edit,

                    'comp_id' =>
                        session('user_company_id'),

                    'created_at' => now(),

                    'updated_at' => now()

                ]);

        }

        return back()->with(
            'success',
            'Debit Note Configuration Saved Successfully'
        );
    }

}