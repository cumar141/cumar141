<?php
namespace App\Models;
use App\Models\Organization;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgTransaction extends Model
{
    // use SoftDeletes;
    // Define the table associated with the model
    protected $table = 'org_transactions';
    protected $guarded = ['id'];
   
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}