<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BillingHeader;
use App\Models\BillingDetail;
use App\Models\BillingCron;
use App\Models\User;
use App\Models\BillingExpired;
use App\Models\ToUniversityOrInstitution;
use Illuminate\Support\Facades\Validator;

class BrivaController extends Controller implements IController
{
    private  $host="https://partner.api.bri.co.id";

    /** Old */
    private $consumerSecret='MnBbTYef3MY52obH';
    private $consumerKey='FbBNnaIt7375M9C0LaA4h3m5FJZrGBVZ';
    private $institutionCode='NWUK40575WJ';
    private $brivaNo='12837';
    /** Old */

    /** Institution - Updated at 2021-07-08 */
    private $consumerSecretUniversity='MnBbTYef3MY52obH';
    private $consumerKeyUniversity='FbBNnaIt7375M9C0LaA4h3m5FJZrGBVZ';
    private $institutionCodeUniversity='NWUK40575WJ';
    private $brivaNoUniversity='12837';
    /** Institution - Updated at 2021-07-08 */

    /** Universitas - Updated at 2021-07-08 */
    private $consumerSecretInstitution='MnBbTYef3MY52obH';
    private $consumerKeyInstitution='FbBNnaIt7375M9C0LaA4h3m5FJZrGBVZ';
    private $institutionCodeInstitution='NWUK40575WJ';
    private $brivaNoInstitution='12837';
    /** Universitas - Updated at 2021-07-08 */

    private $errorStatus=500;
    private $successStatus=200;

    private $paymentMethodId = 3;
    private $validator;

    public $custCode;
    public $namaClient;
    public $amount;
    public $keterangan;
    public $expiredDate;
    /**  Updated at 2021-07-08 */
    public $tid;
    /**  Updated at 2021-07-08 */
    public $statusBayar;
    public $startDate;
    public $endDate;
    public $startTime;
    public $endTime;
    public $petunjuk;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->validator = Validator::make([
            'brivaNo'=>$this->brivaNo,
            'consumerSecret'=>$this->consumerSecret,
            'consumerKey'=>$this->consumerKey,
            'institutionCode'=>$this->institutionCode,
        ], [
            'brivaNo' => 'required|min:1',
            'consumerSecret'=> 'required|min:1',
            'consumerKey'=> 'required|min:1',
            'institutionCode'=> 'required|min:1',
        ]);

        if ($this->validator->fails()) {
            return response()->json(
                ['status'=>'error','metadata'=>['message'=>$this->validator->errors()]],
                $this->errorStatus
            );
        }
    }

    public function index(Request $request){

            $this->validate($request, [
                'paycode' => 'required|min:13|max:13',
            ]);
            $this->custCode=$request->paycode;
            $respon=$this->get();
            if(!isset($respon['status']) || $respon['status']==false){
                return response()->json(
                    ['status'=>'error','metadata'=>$respon],
                    $this->errorStatus
                );
            } else {
              $metadata=$this;
              if ($respon['status']['code'] == "0602") {
                return response()->json([
                    "status" => "error",
                    "metadata" =>
                        [
                            "status" => false,
                            "errDesc" => "Institution Code Tidak Boleh Kosong",
                            "responseCode" => "03",
                            "data" => [
                                "institutionCode" => $this->institutionCode,
                                "brivaNo" => (string) $this->brivaNo,
                                "custCode" => $this->custCode
                            ]
                        ],
                    ],
                    $this->errorStatus
                );
                } else {
                return response()->json(
                    array_merge(['status'=>'success'],['metadata'=>$respon]),
                    $this->successStatus
                );
                }
            }
    }

    public function status(Request $request){

        $this->validate($request, [
            'paycode' => 'required|min:13|max:13',
        ]);
        $this->custCode=$request->paycode;
        $respon=$this->getStatus();
        if(!isset($respon['status']) || $respon['status']==false){
            return response()->json(
                ['status'=>'error','metadata'=>$respon],
                $this->errorStatus
             );
        }else{

          $metadata=$this;
          return response()->json(
             array_merge(['status'=>'success'],['metadata'=>$respon]),
             $this->successStatus
          );
        }
    }

    public function destroy(Request $request){
        $this->validate($request, [
            'paycode' => 'required|min:13|max:13',
        ]);
        $this->custCode=$request->paycode;
        $respon=$this->delete();
        if(!isset($respon['status']) || $respon['status']==false){
            if ($respon == null) {
                $respon = [
                    'status' => false,
                    'errDesc' => "Data Customer Tidak Ditemukan",
                    'responseCode' => "14",
                    'data' => [
                        "institutionCode" => $this->institutionCode,
                        "brivaNo" => (string) $this->brivaNo,
                        "custCode" => $this->custCode
                    ]
                ];
            }
            return response()->json(
                ['status'=>'error','metadata'=>$respon],
                $this->errorStatus
             );
        }else{
          $metadata=$this;
          return response()->json(
             array_merge(['status'=>'success','metadata'=>$respon],compact($metadata)),
             $this->successStatus
          );
        }

    }

    public function update(Request $request){
        $this->validate($request, [
            'paycode' => 'required|min:13|max:13',
            'statusBayar' => 'in:Y,N'
        ]);
        $this->custCode=$request->paycode;
        $this->statusBayar=$request->statusBayar;
        $respon=$this->updateStatus();

        if(!isset($respon['status']) || $respon['status']==false){
            return response()->json(
                ['status'=>'error','metadata'=>$respon],
                $this->errorStatus
             );
        }else{
          return response()->json(
             array_merge(['status'=>'success'],['metadata'=>$respon]),
             $this->successStatus
          );
        }
    }

    public function store(Request $request){
        $this->validate($request, [
            'nama' => 'required|min:3|max:40',
            'paycode' => 'required|min:10|max:13',
            'amount'=>'required|numeric|gt:0',
            'keterangan'=>'required',
            'expired_date'=>'required|date_format:Y-m-d H:i:s|after:today',
            /**  Updated at 2021-07-08 */
            'tid'=> 'required|numeric'
            /**  Updated at 2021-07-08 */
        ]);
        if ($this->brivaNo <= 0 || $this->brivaNo == '') {
            return response()->json(
                ['status'=>'error','metadata'=>[
                    'status' => false,
                    'errDesc' => 'General Error',
                    'responseCode' => '99'
                    ]
                ],
                $this->errorStatus
             );
        }
        $this->namaClient=$request->nama;
        $this->custCode=$request->paycode;
        $this->amount=$request->amount;
        $this->keterangan=$request->keterangan;
        $this->expiredDate=$request->expired_date;
        /**  Updated at 2021-07-08 */
        $this->tid=$request->tid;
        /**  Updated at 2021-07-08 */

       $respon = $this->create();
       if(!isset($respon['status']) || $respon['status']==false){
           return response()->json(
               ['status'=>'error','metadata'=>$respon],
               $this->errorStatus
            );
       }else{
         $metadata=$this;
         return response()->json(
            array_merge(['status'=>'success','metadata'=>$respon],compact($metadata)),
            $this->successStatus
         );
       }
    }

    /*Generate Token */
    public function BRIVAgenerateToken($client_id, $secret_id){
        $url =$this->host."/oauth/client_credential/accesstoken?grant_type=client_credentials";
        $data = "client_id=".$client_id."&client_secret=".$secret_id;
        echo "generate token ".$data;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        //dd($result);
        $json = json_decode($result, true);
        $accesstoken = $json['access_token'];

        return $accesstoken;
    }

    /*Generate signature*/
    public function BRIVAgenerateSignature($path,$verb,$token,$timestamp,$payload,$secret){

        $payloads = "path=$path&verb=$verb&token=Bearer $token&timestamp=$timestamp&body=$payload";
        $signPayload = hash_hmac('sha256', $payloads, $secret, true);
        return base64_encode($signPayload);
    }

    public function createVA(Request $request){
        $this->validate($request, [
            'nama' => 'required|min:3|max:40',
            'paycode' => 'required|min:13|max:13',
            'amount'=>'required|numeric',
            'keterangan'=>'required',
            'expired_date'=>'required|date_format:Y-m-d H:i:s|after:today'

        ]);

        $this->namaClient=$request->nama;
        $this->custCode=$request->paycode;
        $this->amount=$request->amount;
        $this->keterangan=$request->keterangan;
        $this->expiredDate=$request->expired_date;
        return $this->create();

    }

    public function getVA(Request $request){
        $this->validate($request, [
            'paycode' => 'required|min:13|max:13',
        ]);
        $this->custCode=$request->paycode;
        return $this->get();
    }

    public function deleteVA(Request $request){
        $this->validate($request, [
            'paycode' => 'required|min:13|max:13',
        ]);
        $this->custCode=$request->paycode;
        return $this->delete();
    }

    public function updateStatusVA(Request $request) {
        $this->validate($request, [
            'paycode' => 'required|min:13|max:13',
            'statusBayar' => 'in:Y,N'
        ]);
        $this->custCode=$request->paycode;
        $this->statusBayar=$request->statusBayar;
        return $this->updateStatus();
    }

    public function getReportVA(Request $request){
        $this->validate($request, [
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d'
        ]);
        $this->startDate = str_replace('-','',$request->startDate);
        $this->endDate = str_replace('-','',$request->endDate);
        $result =  $this->getReport();
        if ($result['status'] == false and $result['responseCode'] == "01") {
            return response()->json(
                [
                    "status" => false,
                    "errDesc" => "No Briva Tidak Boleh Kosong",
                    "responseCode" => "01",
                    "data" => [
                        "institutionCode" => $this->institutionCode,
                        "brivaNo" => (string) $this->brivaNo,
                        "startTime" => $this->startDate,
                        "endTime" => $this->endDate
                    ]
                ],
                $this->errorStatus
             );
        } else if ($result['status']['code'] == "0602") {
            return response()->json(
                [
                    "status" => false,
                    "errDesc" => "Institution Code Tidak Boleh Kosong",
                    "responseCode" => "03",
                    "data" => [
                        "institutionCode" => $this->institutionCode,
                        "brivaNo" => (string) $this->brivaNo,
                        "startTime" => $this->startDate,
                        "endTime" => $this->endDate
                    ]
                ],
                $this->errorStatus
            );
        }
        else {
            return $result;
        }
    }

    public function getReportTimeVA(Request $request){
        $this->validate($request, [
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i'
        ]);
        $this->startDate = $request->startDate;
        $this->endDate = $request->endDate;
        $this->startTime = $request->startTime;
        $this->endTime = $request->endTime;
        return $this->getReportTime();
    }

    public function create(){
        /**  checkType - Updated at 2021-07-08 */
        $checkType = ToUniversityOrInstitution::where('tuitionMasterId',$this->tid)->first();

        if($checkType['isUniversity'] == null){
            return ['status'=>false,'msg'=>'Data pembayaran belum diatur'];
        }else{
            if($checkType['isUniversity'] == 1){
                $this->consumerKey = $this->consumerKeyUniversity;
                $this->consumerSecret = $this->consumerSecretUniversity;
                $this->institutionCode = $this->institutionCodeUniversity;
                $this->brivaNo = $this->brivaNoUniversity;
            }elseif($checkType['isUniversity'] == 0){
                $this->consumerKey = $this->consumerKeyInstitution;
                $this->consumerSecret = $this->consumerSecretInstitution;
                $this->institutionCode = $this->institutionCodeInstitution;
                $this->brivaNo = $this->brivaNoInstitution;
            }
        }
        /**  checkType - Updated at 2021-07-08 */

        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;

        $token = $this->BRIVAgenerateToken($client_id,$secret_id);

        $institutionCode = $this->institutionCode;
        $brivaNo = $this->brivaNo;
        $custCode = $this->custCode;
        $nama = $this->namaClient;
        $amount=$this->amount;
        $keterangan=$this->keterangan;
        $expiredDate=$this->expiredDate;

        $del=$this->delete();
        dd($del);
        $datas = array('institutionCode' => $institutionCode ,
            'brivaNo' => $brivaNo,
            'custCode' => $custCode,
            'nama' => $nama,
            'amount' => $amount,
            'keterangan' => $keterangan,
            'expiredDate' => $expiredDate);

        $payload = json_encode($datas, true);
        $path = "/v1/briva";
        $verb = "POST";

        //generate signature
        $base64sign = $this->BRIVAgenerateSignature($path,$verb,$token,$timestamp,$payload,$secret);

        $request_headers = array(
                            "Content-Type:"."application/json",
                            "Authorization:Bearer " . $token,
                            "BRI-Timestamp:" . $timestamp,
                            "BRI-Signature:" . $base64sign,
                        );

        $urlPost =$this->host."/v1/briva";
        $chPost = curl_init();
        curl_setopt($chPost, CURLOPT_URL,$urlPost);
        curl_setopt($chPost, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($chPost, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($chPost, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($chPost, CURLINFO_HEADER_OUT, true);
        curl_setopt($chPost, CURLOPT_RETURNTRANSFER, true);
        $resultPost = curl_exec($chPost);
        $httpCodePost = curl_getinfo($chPost, CURLINFO_HTTP_CODE);
        curl_close($chPost);


        $jsonPost = json_decode($resultPost, true);
        return $jsonPost;
    }

    function get() {
        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;
        //generate token
        $token = $this->BRIVAgenerateToken($client_id,$secret_id);

        $institutionCode = $this->institutionCode;
        $brivaNo = $this->brivaNo;
        $custCode = $this->custCode;

        $payload = null;
        $path = "/v1/briva/".$institutionCode."/".$brivaNo."/".$custCode;
        $verb = "GET";
        $base64sign = $this->BRIVAgenerateSignature($path, $verb, $token, $timestamp, $payload, $secret);

        $request_headers = array(
            "Authorization:Bearer " . $token,
            "BRI-Timestamp:" . $timestamp,
            "BRI-Signature:" . $base64sign,
        );

        $urlPost =$this->host."/v1/briva/".$institutionCode."/".$brivaNo."/".$custCode;
        $chPost = curl_init();
        curl_setopt($chPost,CURLOPT_VERBOSE, true);
        curl_setopt($chPost, CURLOPT_URL, $urlPost);
        curl_setopt($chPost, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($chPost, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($chPost, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($chPost, CURLINFO_HEADER_OUT, true);
        curl_setopt($chPost, CURLOPT_RETURNTRANSFER, true);
        $resultPost = curl_exec($chPost);
        $httpCodePost = curl_getinfo($chPost, CURLINFO_HTTP_CODE);
        curl_close($chPost);
        return json_decode($resultPost, true);
    }

    function getStatus() {
        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;
        //generate token
        $token = $this->BRIVAgenerateToken($client_id,$secret_id);

        $institutionCode = $this->institutionCode;
        $brivaNo = $this->brivaNo;
        $custCode = $this->custCode;

        $payload = null;
        $path = "/v1/briva/status/".$institutionCode."/".$brivaNo."/".$custCode;
        $verb = "GET";
        $base64sign = $this->BRIVAgenerateSignature($path, $verb, $token, $timestamp, $payload, $secret);

        $request_headers = array(
            "Authorization:Bearer " . $token,
            "BRI-Timestamp:" . $timestamp,
            "BRI-Signature:" . $base64sign,
        );

        $urlPost =$this->host."/v1/briva/status/".$institutionCode."/".$brivaNo."/".$custCode;
        $chPost = curl_init();
        curl_setopt($chPost,CURLOPT_VERBOSE, true);
        curl_setopt($chPost, CURLOPT_URL, $urlPost);
        curl_setopt($chPost, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($chPost, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($chPost, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($chPost, CURLINFO_HEADER_OUT, true);
        curl_setopt($chPost, CURLOPT_RETURNTRANSFER, true);
        $resultPost = curl_exec($chPost);
        $httpCodePost = curl_getinfo($chPost, CURLINFO_HTTP_CODE);
        curl_close($chPost);
        return json_decode($resultPost, true);
    }

    function delete() {
        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;
        //generate token
        $token = $this->BRIVAgenerateToken($client_id,$secret_id);
        echo "token ".$token." consumerkey:".$client_id." consumerSecret:".$secret_id;

        $institutionCode = $this->institutionCode;
        $brivaNo = $this->brivaNo;
        $custCode = $this->custCode;

        $datas = array(
            'institutionCode' => $institutionCode ,
            'brivaNo' => $brivaNo,
            'custCode' => $custCode
        );

        $payload = "institutionCode=".$institutionCode."&brivaNo=".$brivaNo."&custCode=".$custCode;
        $path = "/v1/briva";
        $verb = "DELETE";
        echo "path :".$path.",verb :".$verb.", token :".$token.",ts :".$timestamp.",payload : ".$payload.",secret: ".$secret;

        $base64sign = $this->BRIVAgenerateSignature($path, $verb, $token, $timestamp, $payload, $secret);

        $request_headers = array(
            "Authorization:Bearer " . $token,
            "BRI-Timestamp:" . $timestamp,
            "BRI-Signature:" . $base64sign,
        );

        $urlPost =$this->host."/v1/briva";
        $chPost = curl_init();
        curl_setopt($chPost, CURLOPT_URL, $urlPost);
        curl_setopt($chPost, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($chPost, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($chPost, CURLINFO_HEADER_OUT, true);
        curl_setopt($chPost, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($chPost, CURLOPT_RETURNTRANSFER, true);

        $resultPost = curl_exec($chPost);
        $httpCodePost = curl_getinfo($chPost, CURLINFO_HTTP_CODE);
        curl_close($chPost);
        return json_decode($resultPost, true);
    }

    function updateStatus() {
        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;
        //generate token
        $token = $this->BRIVAgenerateToken($client_id,$secret_id);

        $institutionCode = $this->institutionCode;
        $brivaNo = $this->brivaNo;
        $custCode = $this->custCode;
        $statusBayar = $this->statusBayar;

        $datas = array(
            'institutionCode' => $institutionCode ,
            'brivaNo' => $brivaNo,
            'custCode' => $custCode,
            'statusBayar'=> $statusBayar
        );

        $payload = json_encode($datas, true);
        $path = "/v1/briva/status";
        $verb = "PUT";
        $base64sign = $this->BRIVAgenerateSignature($path, $verb, $token, $timestamp, $payload, $secret);

        $request_headers = array(
            "Content-Type:"."application/json",
            "Authorization:Bearer " . $token,
            "BRI-Timestamp:" . $timestamp,
            "BRI-Signature:" . $base64sign,
        );

        $urlPost =$this->host."/v1/briva/status";
        $chPost = curl_init();
        curl_setopt($chPost, CURLOPT_URL, $urlPost);
        curl_setopt($chPost, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($chPost, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($chPost, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($chPost, CURLINFO_HEADER_OUT, true);
        curl_setopt($chPost, CURLOPT_RETURNTRANSFER, true);

        $resultPost = curl_exec($chPost);
        $httpCodePost = curl_getinfo($chPost, CURLINFO_HTTP_CODE);
        curl_close($chPost);
        return json_decode($resultPost, true);
    }

    function getReport() {
        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;
        //generate token
        $token = $this->BRIVAgenerateToken($client_id,$secret_id);

        $institutionCode = $this->institutionCode;
        $brivaNo = $this->brivaNo;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $payload = null;
        $path = "/v1/briva/report/".$institutionCode."/".$brivaNo."/".$startDate."/".$endDate;
        $verb = "GET";
        $base64sign = $this->BRIVAgenerateSignature($path, $verb, $token, $timestamp, $payload, $secret);

        $request_headers = array(
            "Authorization:Bearer " . $token,
            "BRI-Timestamp:" . $timestamp,
            "BRI-Signature:" . $base64sign,
        );

        $urlPost =$this->host."/v1/briva/report/".$institutionCode."/".$brivaNo."/".$startDate."/".$endDate;
        $chPost = curl_init();
        curl_setopt($chPost, CURLOPT_URL, $urlPost);
        curl_setopt($chPost, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($chPost, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($chPost, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($chPost, CURLINFO_HEADER_OUT, true);
        curl_setopt($chPost, CURLOPT_RETURNTRANSFER, true);

        $resultPost = curl_exec($chPost);
        $httpCodePost = curl_getinfo($chPost, CURLINFO_HTTP_CODE);
        curl_close($chPost);

        return json_decode($resultPost, true);
    }

    function getReportTime() {
        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;
        //generate token
        $token = $this->BRIVAgenerateToken($client_id,$secret_id);

        $institutionCode = $this->institutionCode;
        $brivaNo = $this->brivaNo;
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $startTime = $this->startTime;
        $endTime = $this->endTime;

        $payload = null;
        $path = "/v1/briva/report_time/".$institutionCode."/".$brivaNo."/".$startDate."/".$startTime."/".$endDate."/".$endTime;
        $verb = "GET";
        $base64sign = $this->BRIVAgenerateSignature($path, $verb, $token, $timestamp, $payload, $secret);

        $request_headers = array(
            "Authorization:Bearer " . $token,
            "BRI-Timestamp:" . $timestamp,
            "BRI-Signature:" . $base64sign,
        );

        $urlPost =$this->host."/v1/briva/report_time/".$institutionCode."/".$brivaNo."/".$startDate."/".$startTime."/".$endDate."/".$endTime;
        $chPost = curl_init();
        curl_setopt($chPost, CURLOPT_URL, $urlPost);
        curl_setopt($chPost, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($chPost, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($chPost, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($chPost, CURLINFO_HEADER_OUT, true);
        curl_setopt($chPost, CURLOPT_RETURNTRANSFER, true);

        $resultPost = curl_exec($chPost);
        $httpCodePost = curl_getinfo($chPost, CURLINFO_HTTP_CODE);
        curl_close($chPost);

        return json_decode($resultPost, true);
    }
    //

    function cron() {
        //cari pembayaran briva
        $durasiTarikData = 60*60; //data ditarik 1 jam
        $dbEnd = (BillingCron::where('PaymentMethodId',$this->paymentMethodId)->max('endTime'));
        $lastEnd = !empty($dbEnd) ? strtotime($dbEnd) : strtotime('2021-01-27 22:00:00');
        if ($lastEnd + $durasiTarikData < time()) {
            $this->startDate = date('Y-m-d', $lastEnd);
            $this->endDate = date('Y-m-d', $lastEnd + $durasiTarikData);
            $this->startTime = date('H:i', $lastEnd);
            $this->endTime = date('H:i', $lastEnd + $durasiTarikData);
        } else {
            $this->startDate = date('Y-m-d', strtotime(date('Y-m-d H:i:s')) - $durasiTarikData);
            $this->endDate = date('Y-m-d');
            $this->startTime = date('H:i', strtotime(date('Y-m-d H:i:s')) - $durasiTarikData);
            $this->endTime = date('H:i');
        }
        if ($this->startDate <> $this->endDate) {
            $this->startDate = $this->endDate;
            $this->startTime = '00:00';
        }
        $report = ($this->getReportTime());
        if (isset($report['responseCode']) and $report['responseCode'] = '00' and $report['status']) {
            //ada pembayaran
            foreach ($report['data'] as $k => $v) {
                $bill = BillingHeader::where('PayCode',$v['custCode']);
                if ($bill->count() > 0) {
                    $bill = $bill->first();
                    if ($bill->PaymentStatusID <> 3) {
                        BillingHeader::where('PayCode',$v['custCode'])
                        ->update(
                        [
                            'PaymentStatusID' => 3,
                            'tanggalTransaksi' => $v['paymentDate'],
                            'tanggalTransaksiServer' => $v['paymentDate'],
                            'kodeChanel' => $v['channel'],
                            'kodeTerminal' => $v['tellerid'],
                            'KodeTransaksiBANK' => $v['no_rek'],
                            'nomorJurnalPembukuan' => $v['trxID'],
                            'PaymentMethodID' => $this->paymentMethodId
                        ]
                        );
                    }
                }
            }
        }
        if (isset($report['responseCode']) and in_array($report['responseCode'],['41','00'])) {
            //jika pembayaran briva kosong atau ada
            if (empty($dbEnd)) {
                $bC = new BillingCron();
                $bC->paymentMethodId = $this->paymentMethodId;
            } else {
                $bC = BillingCron::where('PaymentMethodId',$this->paymentMethodId)->orderBy('endTime','desc')->first();
            }
            $bC->runTime = date('Y-m-d H:i:s');
            $bC->startTime = $this->startDate.' '.$this->startTime.':00';
            $bC->endTime = $this->endDate.' '.$this->endTime.':00';
            $bC->save();
        }

        //cari billing briva yang sudah expired
        $exp = BillingHeader::join('BillingExpired','BillingHeader.BilingId','=','BillingExpired.BillingId')
                    ->where('BillingHeader.PaymentStatusId','<>',3)
                    ->where('BillingExpired.PaymentMethodId',$this->paymentMethodId)
                    ->where('BillingExpired.expiredDate', '<', date('Y-m-d H:i:s'))->get();
        foreach ($exp as $exp) {
            $this->custCode =  $exp->PayCode;
            $get = $this->get();
            if ($get['responseCode'] == '00' and $get['status']) {
                if($get['data']['statusBayar'] == "Y") {
                    BillingHeader::where('PayCode',$this->custCode)
                        ->update(
                        [
                            'PaymentStatusID' => 3,
                            'tanggalTransaksi' => date('Y-m-d H:i:s'),
                            'tanggalTransaksiServer' => date('Y-m-d H:i:s'),
                            'PaymentMethodID' => $this->paymentMethodId
                        ]
                        );
                } else {
                    $this->delete();
                    BillingExpired::where('PaymentMethodId',$this->paymentMethodId)
                                ->where('BillingId',$exp->BillingID)
                                ->delete();
                }
            } else {
                BillingExpired::where('PaymentMethodId',$this->paymentMethodId)
                    ->where('BillingId',$exp->BillingID)
                    ->delete();
            }
        }
    }
}
