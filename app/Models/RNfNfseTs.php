<?php

namespace App\Models;

use App\Models\Permissions;
use http\Client\Request;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class RNfNfseTs extends Model
{
    use SoftDeletes;

    protected $table        = 'r_nf_nfse_ts';
    protected $primaryKey   = 'id';
    protected $guarded      = ['_token'];
    public $timestamps      = true;
    public $filter_with     = [];

    public function makeHidden($attributes)
    {
        $attributes = (array) $attributes;
        foreach ($attributes as $key => $value) {
            if (isset($this->$value)) {
                unset($this->$value);
            }
        }
        return $this;
    }

    public static function list($limit = 100, $field = false)
    {
        if (!$field) {
            $field = 'id';
        }
        $user = Auth::user();
        if(Permissions::permissaoModerador($user)){
            $list = RNfNfseTs::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'r_nf_nfse_ts.id')
                                        ->prepend("", "");
        }else {
            $list = RNfNfseTs::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("r_nf_nfse_ts.r_auth", 0)->orWhere("r_nf_nfse_ts.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'r_nf_nfse_ts.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user   = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = RNfNfseTs::
                    with(['Cliente','Servico'])
                    ->select('*')
                    ->orderBy('cfg_data_competencia', 'DESC')
                    ->limit($limit);
        }else {
            $list = RNfNfseTs::
                    with(['Cliente','Servico'])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("r_nf_nfse_ts.r_auth", 0)->orWhere("r_nf_nfse_ts.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('cfg_data_competencia', 'DESC');
        }

        // # Filter
        if(!is_null($store) and $request->segment(3) == 'filter'){

            // :: Data de Compentência
            if(isset($store['cfg_data_competencia']) and !empty($store['cfg_data_competencia'])){
                $list->where(function($q) use($store){
                    if($store['operador']['cfg_data_competencia'] == 'contem'){
                        $q->where('cfg_data_competencia','LIKE', '%'.$store['cfg_data_competencia'].'%');
                    }elseif($store['operador']['cfg_data_competencia'] == 'entre'){
                        $q->whereBetween(DB::raw('DATE_FORMAT(cfg_data_competencia, "%Y-%m-%d")'),[$store['cfg_data_competencia'],$store['between']['cfg_data_competencia']]);
                    }else {
                        $q->where('cfg_data_competencia',$store['operador']['cfg_data_competencia'],$store['cfg_data_competencia']);
                    }
                });
            }

            // :: Data de Emissão
            if(isset($store['nf_emissao']) and !empty($store['nf_emissao'])){
                $list->where(function($q) use($store){
                    if($store['operador']['nf_emissao'] == 'contem'){
                        $q->where('nf_emissao','LIKE', '%'.$store['nf_emissao'].'%');
                    }elseif($store['operador']['nf_emissao'] == 'entre'){
                        $q->whereBetween(DB::raw('DATE_FORMAT(nf_emissao, "%Y-%m-%d")'),[$store['nf_emissao'],$store['between']['nf_emissao']]);
                    }else {
                        $q->where('nf_emissao',$store['operador']['nf_emissao'],$store['nf_emissao']);
                    }
                });
            }

            // :: Serviço
            if(isset($store['mra_nf_prod_serv_id'])){
                $list->where('mra_nf_prod_serv_id',$store['mra_nf_prod_serv_id']);
            }

            // :: Cliente / Tomador
            if(isset($store['mra_nf_cliente_id'])){
                $list->where('mra_nf_cliente_id',$store['mra_nf_cliente_id']);
            }

            // :: Número da Nota
            if(isset($store['nf_numero'])){
                $list->where('nf_numero',$store['nf_numero']);
            }

            // :: Código de Verificação
            if(isset($store['nf_codigoVerificacao'])){
                $list->where('nf_codigoVerificacao',$store['nf_codigoVerificacao']);
            }

            // :: Com Tomador
            if(isset($store['tomador'])){
                $list->where('tomador',$store['tomador']);
            }

            // :: Status
            if(isset($store['nf_status'])){
                $list->where('nf_status',$store['nf_status']);
            }

        }
        // - #

        $list = $list->get();

        return $list;
    }

    public static function getAllByApi($limit = 100)
    {
        $user = Auth::user();
        if(Permissions::permissaoModerador($user)){
            $list = RNfNfseTs::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = RNfNfseTs::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("r_nf_nfse_ts.r_auth", 0)->orWhere("r_nf_nfse_ts.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    // :: Qt Status - null
    public static function Get_QtStatus_Nao_Emitida(){
        return RNfNfseTs::whereNull('nf_status')->count();
    }

    // :: Qt Status - Pendente
    public static function Get_QtStatus_Pendente(){
        return RNfNfseTs::where('nf_status','PENDENTE')->count();
    }

    // :: Qt Status - EmProcessoDeCancelamento
    public static function Get_QtStatus_Processando(){
        return RNfNfseTs::where('nf_status','PROCESSANDO')->count();
    }

    // :: Qt Status - Autorizada
    public static function Get_QtStatus_Autorizada(){
        return RNfNfseTs::where('nf_status','CONCLUIDO')->count();
    }

    // :: Qt Status - Rejeitada
    public static function Get_QtStatus_Rejeitada(){
        return RNfNfseTs::where('nf_status','REJEITADO')->count();
    }

     // :: Qt Status - Denegada
     public static function Get_QtStatus_Denegada(){
        return RNfNfseTs::where('nf_status','DENEGADO')->count();
    }

    // :: Qt Status - Cancelada
    public static function Get_QtStatus_Cancelada(){
        return RNfNfseTs::where('nf_status','CANCELADO')->count();
    }

    public function Cliente(){
        return $this->belongsTo('App\Models\RNfClientesTs', 'mra_nf_cliente_id');
    }

    public function Servico(){
        return $this->belongsTo('App\Models\RNfServicosTs', 'mra_nf_prod_serv_id');
    }

    public function RNfLogTs(){
        return $this->hasMany('App\Models\RNfLogTs', 'nfse_id', 'id')->orderBy('created_at','DESC');
    }

    public function ConfigEmpresa() {
        return $this->belongsTo('App\Models\RNfConfiguracoesTs', 'mra_nf_cfg_emp_id');
    }
}

