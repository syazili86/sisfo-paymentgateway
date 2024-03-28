<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingHeader extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'BilingId';
    protected $table = "BillingHeader";

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['PaymentStatusID','tanggalTransaksi','tanggalTransaksiServer','kodeChanel','kodeTerminal','KodeTransaksiBANK','nomorJurnalPembukuan','PaymentMethodID'];

}
