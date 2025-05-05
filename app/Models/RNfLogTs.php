<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class RNfLogTs extends Model
{
    use SoftDeletes;

    protected $table        = 'r_nf_log_ts';
    protected $primaryKey   = 'id';
    protected $guarded      = ['_token'];
    public $timestamps      = true;
    public $filter_with     = [];

    public function Empresa(){
        return $this->belongsTo('App\Models\RNfConfiguracoesTs', 'mra_nf_cfg_emp_id');
    }

    public function Cliente(){
        return $this->belongsTo('App\Models\RNfClientesTs', 'mra_nf_cliente_id');
    }

    public function Transportadora(){
        return $this->belongsTo('App\Models\RNfTransportadorasTs', 'mra_nf_transp_id');
    }

    public function Produto(){
        return $this->belongsTo('App\Models\RNfProdutosTs', 'mra_nf_prod_id');
    }

    public function Servico(){
        return $this->belongsTo('App\Models\RNfServicosTs', 'mra_nf_prod_serv_id');
    }
}

