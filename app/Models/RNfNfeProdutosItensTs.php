<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class RNfNfeProdutosItensTs extends Model
{
    use SoftDeletes;

    protected $table        = 'r_nf_nfe_produtos_ts';
    protected $primaryKey   = 'id';
    protected $guarded      = ['_token'];
    public $timestamps      = true;
    public $filter_with     = [];

    public function MRANfNfe(){
        return $this->belongsTo('App\Models\RNfNfeTs', 'mra_nf_nf_e_id');
    }

}

