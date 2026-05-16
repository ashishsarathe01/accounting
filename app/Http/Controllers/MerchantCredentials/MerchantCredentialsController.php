<?php

namespace App\Http\Controllers\MerchantCredentials;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Crypt;

class MerchantCredentialsController extends Controller
{
    public function index()
    {
        $credentials = DB::table('credentials')
            ->where('comp_id', Session::get('user_company_id'))
            ->orderBy('credential_type', 'ASC')
            ->get();
        foreach ($credentials as $credential) {
            try {
                $credential->password = !empty($credential->password)
                    ? Crypt::decryptString($credential->password)
                    : '';
            } catch (\Exception $e) {
                $credential->password = '';
            }
        }
        return view('MerchantCredentials.index', compact('credentials'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            DB::table('credentials')
                ->where('comp_id', Session::get('user_company_id'))
                ->delete();

            if(isset($request->credential_type) && count($request->credential_type) > 0){

                foreach($request->credential_type as $key => $type){

                    $username = $request->username[$key] ?? '';
                    $password = $request->password[$key] ?? '';

                    // skip fully empty row
                    if(
                        empty($type) &&
                        empty($username) &&
                        empty($password)
                    ){
                        continue;
                    }

                    DB::table('credentials')->insert([

                        'comp_id' => Session::get('user_company_id'),

                        'credential_type' => $type,

                        'username' => $username,

                        'password' => !empty($password)
                            ? Crypt::encryptString($password)
                            : null,

                        'created_at' => now(),
                        'updated_at' => now(),

                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Credentials Saved Successfully');

        } catch (\Exception $e) {

            DB::rollback();

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}