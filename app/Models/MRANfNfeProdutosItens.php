<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRANfNfeProdutosItens extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_nf_nf_e_prod_i';
    protected $primaryKey   = 'id';
    protected $guarded      = ['_token'];
    public $timestamps      = true;
    public $filter_with     = [];

    public function MRANfNfe(){
        return $this->belongsTo('App\Models\MRANfNfe', 'mra_nf_nf_e_id');
    }

}

