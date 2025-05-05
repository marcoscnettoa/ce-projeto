<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class MRANfNfe extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_nf_nf_e';
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
            $list = MRANfNfe::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_nf_nf_e.id')
                                        ->prepend("", "");
        }else {
            $list = MRANfNfe::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_nf_e.r_auth", 0)->orWhere("mra_nf_nf_e.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_nf_nf_e.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = MRANfNfe::
                    with(['Cliente','Servico'])
                    ->select('*')
                    ->orderBy('nfe_data_competencia', 'DESC')
                    ->limit($limit);
        }else {
            $list = MRANfNfe::
                    with(['Cliente','Servico'])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_nf_e.r_auth", 0)->orWhere("mra_nf_nf_e.r_auth", $user_id);
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
            $list = MRANfNfe::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRANfNfe::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_nf_e.r_auth", 0)->orWhere("mra_nf_nf_e.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    // :: Qt Notazz Status - null
    public static function Get_QtStatus_Null(){
        return MRANfNfe::whereNull('notazz_status')->count();
    }

    // :: Qt Notazz Status - Pendente
    public static function Get_QtStatus_Pendente(){
        return MRANfNfe::where('notazz_status','')->count();
    }

    // :: Qt Notazz Status - Autorizada
    public static function Get_QtStatus_Autorizada(){
        return MRANfNfe::where('notazz_status','Autorizada')->count();
    }

    // :: Qt Notazz Status - AguardandoAutorizacao
    public static function Get_QtStatus_AguardandoAutorizacao(){
        return MRANfNfe::where('notazz_status','AguardandoAutorizacao')->count();
    }

    // :: Qt Notazz Status - EmProcessoDeCancelamento
    public static function Get_QtStatus_EmProcessoDeCancelamento(){
        return MRANfNfe::where('notazz_status','EmProcessoDeCancelamento')->count();
    }

    // :: Qt Notazz Status - AguardandoCancelamento
    public static function Get_QtStatus_AguardandoCancelamento(){
        return MRANfNfe::where('notazz_status','AguardandoCancelamento')->count();
    }

    // :: Qt Notazz Status - Rejeitada
    public static function Get_QtStatus_Rejeitada(){
        return MRANfNfe::where('notazz_status','Rejeitada')->count();
    }

    // :: Qt Notazz Status - Cancelada
    public static function Get_QtStatus_Cancelada(){
        return MRANfNfe::where('notazz_status','Cancelada')->count();
    }

    public function Cliente(){
        return $this->belongsTo('App\Models\MRANfClientes', 'mra_nf_cliente_id');
    }

    public function Servico(){
        return $this->belongsTo('App\Models\MRANfServicos', 'mra_nf_prod_serv_id');
    }

    public function MRANfNfeProdutosItens(){
        return $this->hasMany('App\Models\MRANfNfeProdutosItens', 'mra_nf_nf_e_id','id');
    }

    // ! ( Quantidade * Valor Total )
    public function MRANfNfeProdutosItensQtValorTotal(){
        return $this->hasMany('App\Models\MRANfNfeProdutosItens', 'mra_nf_nf_e_id','id')->where('quantidade', '>', 0)->sum(DB::raw('quantidade * valor_unitario'));
    }

    public function MRANfLog(){
        return $this->hasMany('App\Models\MRANfLog', 'mra_nf_nf_e_id','id')->orderBy('created_at','DESC');
    }

}

