<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class BillingHeader extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'BilingId';
    protected $table = "BillingHeader";
}