<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

class ThirdPartyController extends Controller
{

    public function verifyGst(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst_no' => 'required'
        ], [
            'gst_no.required' => 'Gst number is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

       $gst_no = trim($request->gst_no);

       $request = array(
        "gst_no"=>$gst_no
       );
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://kraftpaperz.com/stage/api/public/index.php/api/v1/gst-info');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Cookie: laravel_session=rLX0zbRcRreD0ItmNk06XKF8r77IBdqxpC4W91Sn';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) 
        {
           $this->failedMessage();
        }
        curl_close($ch);

        $response = json_decode($result,true);

        if($response['status']==0)
        {
            return response()->json([
            'code' => 422,
            'status' =>$response['status'],
            'message' => $response['message'],
        ]);
        }

        $state_code = substr($gst_no,0,-13);
        $state_list = DB::table('state_list')->where('state_code',$state_code)->get();
        $response['state_code']=$state_code;
        $response['state_name']=$state_list->count()>0?$state_list[0]->state_name:"N/A";

        return response()->json([
                'code' => 200,
                'data' => $response,
                'message' => 'Data fetched successfully!',
            ]);

        
    }


    /**
     * Generates failed response and message.
     */
    public function failedMessage()
    {
        return response()->json([
            'code' => 422,
            'message' => 'Something went wrong, please try again after some time.',
        ]);
    }
}
