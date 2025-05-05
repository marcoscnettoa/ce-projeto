<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class RNfNfeTs extends Model
{
    use SoftDeletes;

    protected $table        = 'r_nf_nfe_ts';
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
            $list = RNfNfeTs::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'r_nf_nfe_ts.id')
                                        ->prepend("", "");
        }else {
            $list = RNfNfeTs::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("r_nf_nfe_ts.r_auth", 0)->orWhere("r_nf_nfe_ts.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'r_nf_nfe_ts.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = RNfNfeTs::
                    with(['Cliente','Servico'])
                    ->select('*')
                    ->orderBy('nfe_data_competencia', 'DESC')
                    ->limit($limit);
        }else {
            $list = RNfNfeTs::
                    with(['Cliente','Servico'])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("r_nf_nfe_ts.r_auth", 0)->orWhere("r_nf_nfe_ts.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('nfe_data_competencia', 'DESC');
        }

        // # Filter
        if(!is_null($store) and $request->segment(3) == 'filter'){

            // :: Data de Compentência
            if(isset($store['nfe_data_competencia']) and !empty($store['nfe_data_competencia'])){
                $list->where(function($q) use($store){
                    if($store['operador']['nfe_data_competencia'] == 'contem'){
                        $q->where('nfe_data_competencia','LIKE', '%'.$store['nfe_data_competencia'].'%');
                    }elseif($store['operador']['nfe_data_competencia'] == 'entre'){
                        $q->whereBetween(DB::raw('DATE_FORMAT(nfe_data_competencia, "%Y-%m-%d")'),[$store['nfe_data_competencia'],$store['between']['nfe_data_competencia']]);
                    }else {
                        $q->where('nfe_data_competencia',$store['operador']['nfe_data_competencia'],$store['nfe_data_competencia']);
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

            // :: Cliente
            if(isset($store['mra_nf_cliente_id'])){
                $list->where('mra_nf_cliente_id',$store['mra_nf_cliente_id']);
            }

            // :: Tipo de Operação
            if(isset($store['nfe_tipo_operacao'])){
                $list->where('nfe_tipo_operacao',$store['nfe_tipo_operacao']);
            }

            // :: Número da Nota
            if(isset($store['nf_numero'])){
                $list->where('nf_numero',$store['nf_numero']);
            }

            // :: Chave
            if(isset($store['nf_chave'])){
                $list->where('nf_chave',$store['nf_chave']);
            }

            // :: Status
            if(isset($store['notazz_status'])){
                $list->where('notazz_status',$store['notazz_status']);
            }

        }
        // - #

        $list = $list->get();

        return $list;
    }

    public static function getAllByApi($limit = 100)
    {
        $user = Auth::user();
        if (Permissions::permissaoModerador($user)){
            $list = RNfNfeTs::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = RNfNfeTs::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("r_nf_nfe_ts.r_auth", 0)->orWhere("r_nf_nfe_ts.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    // :: Qt Status - null
    public static function Get_QtStatus_Nao_Emitida(){
        return RNfNfeTs::whereNull('nf_status')->count();
    }

    // :: Qt Status - Pendente
    public static function Get_QtStatus_Pendente(){
        return RNfNfeTs::where('nf_status','PENDENTE')->count();
    }

    // :: Qt Status - EmProcessoDeCancelamento
    public static function Get_QtStatus_Processando(){
        return RNfNfeTs::where('nf_status','PROCESSANDO')->count();
    }

    // :: Qt Status - Autorizada
    public static function Get_QtStatus_Autorizada(){
        return RNfNfeTs::where('nf_status','CONCLUIDO')->count();
    }

    // :: Qt Status - Rejeitada
    public static function Get_QtStatus_Rejeitada(){
        return RNfNfeTs::where('nf_status','REJEITADO')->count();
    }

     // :: Qt Status - Denegada
     public static function Get_QtStatus_Denegada(){
        return RNfNfeTs::where('nf_status','DENEGADO')->count();
    }

    // :: Qt Status - Cancelada
    public static function Get_QtStatus_Cancelada(){
        return RNfNfeTs::where('nf_status','CANCELADO')->count();
    }

    public function Cliente(){
        return $this->belongsTo('App\Models\RNfClientesTs', 'mra_nf_cliente_id');
    }

    public function Servico(){
        return $this->belongsTo('App\Models\RNfServicosTs', 'mra_nf_prod_serv_id');
    }

    public function RNfNfeProdutosItensTs(){
        return $this->hasMany('App\Models\RNfNfeProdutosItensTs', 'mra_nf_nf_e_id','id');
    }

    // ! ( Quantidade * Valor Total )
    public function MRANfNfeProdutosItensQtValorTotal(){
        return $this->hasMany('App\Models\RNfNfeProdutosItensTs', 'mra_nf_nf_e_id','id')->where('quantidade', '>', 0)->sum(DB::raw('quantidade * valor_unitario'));
    }

    public function RNfLogTs(){
        return $this->hasMany('App\Models\RNfLogTs', 'nf_id','id')->orderBy('created_at','DESC');
    }

    public function ConfigEmpresa() {
        return $this->belongsTo('App\Models\RNfConfiguracoesTs', 'mra_nf_cfg_emp_id');
    }
}

