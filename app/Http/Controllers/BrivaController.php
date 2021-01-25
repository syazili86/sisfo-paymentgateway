<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BrivaController extends Controller
{
    public $host="https://sandbox.partner.api.bri.co.id";

    protected $consumerSecret='1A4ZqDzRe2h7ok4j';
    protected $consumerKey='ggyM3OiddA46GiUB3R6WrR9BzbYDKBj0';
    protected $institutionCode='J104408';
    protected $brivaNo=77777;
    private $custCode;
    private $namaClient;
    private $amount;
    private $keterangan;
    private $expiredDate;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function createVA(Request $request){
        $this->validate($request, [
            'nama' => 'required|min:3|max:40',
            'paycode' => 'required|min:10|max:10',
            'amount'=>'required|numeric',
            'keterangan'=>'required',
            'expired_date'=>'required|date_format:Y-m-d H:i:s|after:today'

        ]);

        $this->namaClient=$request->nama;
        $this->custCode=$request->paycode;
        $this->amount=$request->amount;
        $this->keterangan=$request->keterangan;
        $this->expiredDate=$request->expired_date;
        return $this->BrivaUpdate();

    }

    /*Generate Token */
    public function BRIVAgenerateToken($client_id, $secret_id){
        $url =$this->host."/oauth/client_credential/accesstoken?grant_type=client_credentials";
        $data = "client_id=".$client_id."&client_secret=".$secret_id;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

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

    public function BrivaUpdate(){

        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;
        //generate token
        $token = $this->BRIVAgenerateToken($client_id,$secret_id);

        $institutionCode = $this->institutionCode;
        $brivaNo = $this->brivaNo;
        $custCode = $this->custCode;
        $nama = $this->namaClient;
        $amount=$this->amount;
        $keterangan=$this->keterangan;
        $expiredDate=$this->expiredDate;

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
    //
}
