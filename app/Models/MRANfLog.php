<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRANfLog extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_nf_log';
    protected $primaryKey   = 'id';
    protected $guarded      = ['_token'];
    public $timestamps      = true;
    public $filter_with     = [];

    public function Empresa(){
        return $this->belongsTo('App\Models\MRANfConfiguracoes', 'mra_nf_cfg_emp_id');
    }

    public function Cliente(){
        return $this->belongsTo('App\Models\MRANfClientes', 'mra_nf_cliente_id');
    }

    public function Transportadora(){
        return $this->belongsTo('App\Models\MRANfTransportadoras', 'mra_nf_transp_id');
    }

    public function Produto(){
        return $this->belongsTo('App\Models\MRANfProdutos', 'mra_nf_prod_id');
    }

    public function Servico(){
        return $this->belongsTo('App\Models\MRANfServicos', 'mra_nf_prod_serv_id');
    }

}

