<?php

namespace App\Console\Commands\Sync;

use App\Models\Course;
use App\Models\Dosen;
use App\Models\Student;
use App\Models\User;
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
    public $custCode;
    public $kodeBayar;

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

        $this->syncKodeBayar($this->kodeBayar);

        return Command::SUCCESS;
    }

    /*Generate Token */
    public function generateToken($client_id, $secret_id){
        $url =$this->host."/oauth/client_credential/accesstoken?grant_type=client_credentials";
        //$data = "client_id=".$client_id."&client_secret=".$secret_id;
        $data = ['client_id'=>$client_id,'client_secret'=>$secret_id];

        // echo "generate token ".$data;
        $request=Http::asForm()->post($url,$data);

        $json = json_decode($request, true);
        $accesstoken = $json['access_token'];
        return $accesstoken;
    }


    private function syncKodeBayar($kodeBayar){
        $client_id = $this->consumerKey;
        $secret_id = $this->consumerSecret;
        $timestamp = gmdate("Y-m-d\TH:i:s.000\Z");
        $secret = $secret_id;
        //generate token
        $token = $this->generateToken($client_id,$secret_id);

        $this->custCode=$kodeBayar;
        $payload = null;
        $path = "/v1/briva/".$this->institutionCode."/".$this->brivaNo."/".$this->custCode;
        $verb = "GET";
        $base64sign = $this->generateSignature($path, $verb, $token, $timestamp, $payload, $secret);
        $request_headers = array(
           // "Authorization:Bearer " . $token,
            "BRI-Timestamp:" . $timestamp,
            "BRI-Signature:" . $base64sign,
        );

        $request= Http::withToken($token)->withHeaders($request_headers)->get($this->host.$path);
        dd($request->body());
        return json_decode($request, true);
    }


    /*Generate signature*/
    public function generateSignature($path,$verb,$token,$timestamp,$payload,$secret){
        // echo "signature token :".$token;
         $payloads = "path=".$path."&verb=".$verb."&token=Bearer ".$token."&timestamp=".$timestamp."&body=$payload";
//        dd($payloads);
         $signPayload = hash_hmac('sha256', $payloads, $secret, true);
        // echo "<br/>".base64_encode($signPayload);
         return base64_encode($signPayload);
     }

}
