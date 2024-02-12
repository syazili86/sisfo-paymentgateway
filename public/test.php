<?php
echo "Test";
$request_headers = array(
    "Content-Type:"."application/json"
);
$datas = array(
'nama'=>'Muhammad Bunyamin',
'paycode'=> '1234567890123',
'amount' => '300000',
'keterangan' => 'bayar test',
'expired_date' =>  '2021-01-31 09:00:00'
);
$payload = json_encode($datas, true);
$urlPost ="http://localhost:9009/briva";
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
print_r($payload);
print_r($resultPost);