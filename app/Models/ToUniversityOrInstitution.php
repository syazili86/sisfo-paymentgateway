<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class ToUniversityOrInstitution extends Model
{
    public $timestamps = false;
    protected $table = "TuitionToUniversityOrInstitution";
    protected $hidden  = ['id','tuitionMasterId','userUpdater','updatedAt'];
}