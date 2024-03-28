<?php

namespace App\Console\Commands\Sync;

use App\Models\BillingDetail;
use App\Models\BillingHeader;
use App\Models\Course;
use App\Models\Dosen;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class Briva extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:briva {--kodebayar=} {--tglbayar=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private  $host="https://partner.api.bri.co.id";

    private $consumerSecret='MnBbTYef3MY52obH';
    private $consumerKey='FbBNnaIt7375M9C0LaA4h3m5FJZrGBVZ';
    private $institutionCode='NWUK40575WJ';
    private $brivaNo='12837';
    private $paymentMethodId = 3;
    public $custCode;
    public $kodeBayar;
    public $tglBayar;

    const SUDAH_BAYAR = 3;

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");

    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->kodeBayar=$this->option('kodebayar');
        $this->tglBayar=strtotime($this->option('tglbayar'));


        $this->syncKodeBayar($this->tglBayar,$this->kodeBayar);

        return Command::SUCCESS;
    }

    /*Generate Token */
    public function BRIVAgenerateToken($client_id, $secret_id){
        $url =$this->host."/oauth/client_credential/accesstoken?grant_type=client_credentials";
        $data = "client_id=".$client_id."&client_secret=".$secret_id;
       // echo "generate token ".$data;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);

        $result = curl_exec($ch);
        //$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $info = curl_getinfo($ch);

        if($result===false){
            Log::debug("Brivacontroller  - ".json_encode($info));
            Log::debug("Brivacontroller  - ".json_encode($result));
        }

        curl_close($ch);

        $json = json_decode($result, true);
        $accesstoken = $json['access_token'];

        return $accesstoken;
    }

    /*Generate signature*/
    public function BRIVAgenerateSignature($path,$verb,$token,$timestamp,$payload,$secret){
       // echo "signature token :".$token;
        $payloads = "path=".$path."&verb=".$verb."&token=Bearer ".$token."&timestamp=".$timestamp."&body=$payload";
       // echo "<br/>payload : ".$payloads;
        $signPayload = hash_hmac('sha256', $payloads, $secret, true);
       // echo "<br/>".base64_encode($signPayload);
        return base64_encode($signPayload);
    }

    public function status($custCode) {
        if(empty($custCode)){
             echo "Kode bayar tidak boleh kosong";
             return false;
        }
        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;
        //generate token
        $token = $this->BRIVAgenerateToken($client_id,$secret_id);

        $institutionCode = $this->institutionCode;
        $brivaNo = $this->brivaNo;
        //$custCode = $this->custCode;

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

    function get($custCode) {
        if(empty($custCode)){
            echo "Kobayar tidak boleh kosong";
            return false;
        }

        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;
        //generate token
        $token = $this->BRIVAgenerateToken($client_id,$secret_id);

        $institutionCode = $this->institutionCode;
        $brivaNo = $this->brivaNo;
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

    public function getReportTime($startDate,$startTime, $endDate,$endTime ) {
        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;
        //generate token
        $token = $this->BRIVAgenerateToken($client_id,$secret_id);

        $institutionCode = $this->institutionCode;
        $brivaNo = $this->brivaNo;


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
        $info =  curl_getinfo($chPost);
        $request_header_info = curl_getinfo($chPost, CURLINFO_HEADER_OUT);

        if (isset($report['responseCode']) and in_array($report['responseCode'],['41'])) {

            Log::debug("Brivacon - getReportTime : curl info : ".json_encode($info));
            Log::debug("Brivacon - getReportTime : curl request header info : ".json_encode($request_header_info));
            Log::debug("Brivacon - getReportTime : curl result : ".json_encode($resultPost));
        }
        curl_close($chPost);

        return json_decode($resultPost, true);
    }

    public function syncKodeBayar($tglBayar,$kodeBayar){
        $startDate = date('Y-m-d',$tglBayar);
        $startTime = date('H:i',$tglBayar);
        $endDate = date('Y-m-d',$tglBayar);
        $endTime = (date('H:i',$tglBayar)=='00:00' ? '23:59' : date('H:i',$tglBayar));


        $result = $this->getReportTime(
                                        $startDate,
                                        $startTime,
                                        $endDate,
                                        $endTime
        );

       if(count($result['data']) > 0){
            foreach($result['data'] as $k => $v){

                $bill=BillingHeader::where('PayCode',$v['custCode']);

                if($bill->count() > 0){
                    $billData=$bill->first();
                    $billDetail = BillingDetail::where('BillingID','=',$billData->BilingID)->first();

                    $dataBillToUpdate=[
                        'PaymentStatusID' => 3,
                        'tanggalTransaksi' => $v['paymentDate'],
                        'tanggalTransaksiServer' => $v['paymentDate'],
                        'kodeChanel' => $v['channel'],
                        'kodeTerminal' => $v['tellerid'],
                        'KodeTransaksiBANK' => $v['no_rek'],
                        'nomorJurnalPembukuan' => $v['trxID'],
                        'PaymentMethodID' => $this->paymentMethodId
                    ];

                    if(empty($kodeBayar)){
                        if($billDetail->Amount==$v['amount']){
                            $bill->update($dataBillToUpdate);
                            Log::debug("briva-cron : sukses update billing HEADER: ".json_encode( $dataBillToUpdate));
                            $this->info("Sukses update billing". json_encode($bill->first()));
                        }else{
                            $this->warn("Gagal update, billing amount dengan briva amount tidak sama ".$v['custCode']);
                        }

                    }elseif(!empty($kodeBayar) && $v['custCode']==$kodeBayar){
                        if($billDetail->Amount==$v['amount']){
                            $bill->update($dataBillToUpdate);
                            $this->info("Sukses update billing ".$kodeBayar." : ". json_encode($bill->first()));
                            Log::debug("briva-cron : sukses update billing HEADER: ".json_encode( $dataBillToUpdate));
                        }else{
                            $this->warn("Gagal update, billing amount dengan briva amount tidak sama ".$v['custCode']);
                        }
                    }
                }else{
                    $this->warn("Billing tidak ditemukan di billing header : ".$v['custCode']);
                }
            }
       }else{
        $this->warn("report kosong:". json_encode($result));
        return false;
       }
    }

}
