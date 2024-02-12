<?php

use Illuminate\Support\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class BRIVATest extends TestCase
{


    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateVA()
    {
        echo url('briva')."\n";
        $response = $this->json('POST',url('briva'), [
            'nama' => 'Kurnia Annisa',
            'paycode'=>'2019181010019',
            'amount'=>1600000,
            'keterangan'=>'ANGS.SPP 1',
            'expired_date'=>Carbon::now()->add(30,'day')->toDateTimeString()]);

        $response->seeJsonEquals([
            'status'=>'success'
        ])->assertResponseStatus(200);
        echo "CreateVA : success \n";
    }

    public function testUpdateStatusVA()
    {
        $statusBayar='N';
        $response = $this->json('PUT',url('briva'), [
            'nama' => 'Kurnia Annisa',
            'paycode'=>'2019181010019',
            'amount'=>1600000,
            'keterangan'=>'ANGS.SPP 2',
            'statusBayar'=>$statusBayar,
            'expired_date'=>Carbon::now()->add(30,'day')->toDateTimeString()]);

        $response->seeJson([
            'status'=>'success'
        ])->assertResponseStatus(200);

        $response = $this->json('GET',url('briva'), [
            'nama' => 'Kurnia Annisa',
            'paycode'=>'2019181010019',
            'amount'=>1600000,
            'keterangan'=>'ANGS.SPP 2',
            'expired_date'=>'2021-01-27 00:00:00']);

        $response->seeJson([
            'statusBayar'=>$statusBayar
        ])->assertResponseStatus(200);

        echo "Update Status VA : success \n";
    }

/*     public function testUpdateVA()
    {
        $response = $this->json('PUT',url('briva'), [
            'nama' => 'Kurnia Annisa',
            'paycode'=>'2019181010019',
            'amount'=>1600000,
            'keterangan'=>'ANGS.SPP 2',
            'statusBayar'=>'N',
            'expired_date'=>Carbon::now()->add(30,'day')->toDateTimeString()]);

        $response->seeJson([
            'status'=>'success'
        ])->assertResponseStatus(200);

        $response = $this->json('GET',url('briva'), [
            'nama' => 'Kurnia Annisa',
            'paycode'=>'2019181010019',
            'amount'=>1600000,
            'keterangan'=>'ANGS.SPP 2',
            'expired_date'=>'2021-01-27 00:00:00']);

        $response->seeJson([
            'Keterangan'=>'ANGS.SPP 2'
        ])->assertResponseStatus(200);

        echo "Update VA : success \n";
    } */

    public function testDestroyVA()
    {
        $response = $this->json('DELETE',url('briva'), [
            'nama' => 'Kurnia Annisa',
            'paycode'=>'2019181010019',
            'amount'=>1600000,
            'keterangan'=>'ANGS.SPP 1',
            'expired_date'=>'2021-01-27 00:00:00']);

        $response->seeJsonEquals([
            'status'=>'success'
        ])->assertResponseStatus(200);

        $response = $this->json('GET',url('briva'), [
            'nama' => 'Kurnia Annisa',
            'paycode'=>'2019181010019',
            'amount'=>1600000,
            'keterangan'=>'ANGS.SPP 1',
            'expired_date'=>'2021-01-27 00:00:00']);

        $response->seeJson([
            'status'=>'error'
        ])->assertResponseStatus(500);

        echo "Destroy VA : success \n";
    }
}
