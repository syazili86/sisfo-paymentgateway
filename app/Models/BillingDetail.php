<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class BillingDetail extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'BillingDetailId';
    protected $table = "BillingDetail";
}