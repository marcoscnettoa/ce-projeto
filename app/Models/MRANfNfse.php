<?php

namespace App\Models;

use App\Models\Permissions;
use http\Client\Request;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRANfNfse extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_nf_nfs_e';
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
            $list = MRANfNfse::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_nf_nfs_e.id')
                                        ->prepend("", "");
        }else {
            $list = MRANfNfse::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_nfs_e.r_auth", 0)->orWhere("mra_nf_nfs_e.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_nf_nfs_e.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user   = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = MRANfNfse::
                    with(['Cliente','Servico'])
                    ->select('*')
                    ->orderBy('cfg_data_competencia', 'DESC')
                    ->limit($limit);
        }else {
            $list = MRANfNfse::
                    with(['Cliente','Servico'])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_nfs_e.r_auth", 0)->orWhere("mra_nf_nfs_e.r_auth", $user_id);
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
        if(Permissions::permissaoModerador($user)){
            $list = MRANfNfse::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRANfNfse::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_nfs_e.r_auth", 0)->orWhere("mra_nf_nfs_e.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    // :: Qt Notazz Status - null
    public static function Get_QtStatus_Null(){
        return MRANfNfse::whereNull('notazz_status')->count();
    }

    // :: Qt Notazz Status - Pendente
    public static function Get_QtStatus_Pendente(){
        return MRANfNfse::where('notazz_status','')->count();
    }

    // :: Qt Notazz Status - Autorizada
    public static function Get_QtStatus_Autorizada(){
        return MRANfNfse::where('notazz_status','Autorizada')->count();
    }

    // :: Qt Notazz Status - AguardandoAutorizacao
    public static function Get_QtStatus_AguardandoAutorizacao(){
        return MRANfNfse::where('notazz_status','AguardandoAutorizacao')->count();
    }

    // :: Qt Notazz Status - EmProcessoDeCancelamento
    public static function Get_QtStatus_EmProcessoDeCancelamento(){
        return MRANfNfse::where('notazz_status','EmProcessoDeCancelamento')->count();
    }

    // :: Qt Notazz Status - AguardandoCancelamento
    public static function Get_QtStatus_AguardandoCancelamento(){
        return MRANfNfse::where('notazz_status','AguardandoCancelamento')->count();
    }

    // :: Qt Notazz Status - Rejeitada
    public static function Get_QtStatus_Rejeitada(){
        return MRANfNfse::where('notazz_status','Rejeitada')->count();
    }

    // :: Qt Notazz Status - Cancelada
    public static function Get_QtStatus_Cancelada(){
        return MRANfNfse::where('notazz_status','Cancelada')->count();
    }

    public function Cliente(){
        return $this->belongsTo('App\Models\MRANfClientes', 'mra_nf_cliente_id');
    }

    public function Servico(){
        return $this->belongsTo('App\Models\MRANfServicos', 'mra_nf_prod_serv_id');
    }

    public function MRANfLog(){
        return $this->hasMany('App\Models\MRANfLog', 'mra_nf_nfs_e_id','id')->orderBy('created_at','DESC');
    }

}

