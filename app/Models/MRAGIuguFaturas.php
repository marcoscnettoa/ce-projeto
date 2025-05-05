<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRAGIuguFaturas extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_g_iugu_faturas';
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
            $list = MRAGIuguFaturas::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_g_iugu_faturas.id')
                                        ->prepend("", "");
        }else {
            $list = MRAGIuguFaturas::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_g_iugu_faturas.r_auth", 0)->orWhere("mra_g_iugu_faturas.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_g_iugu_faturas.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user   = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = MRAGIuguFaturas::
                    with([])
                    ->select('*')
                    ->orderBy('id', 'DESC')
                    ->limit($limit);
        }else {
            $list = MRAGIuguFaturas::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_g_iugu_faturas.r_auth", 0)->orWhere("mra_g_iugu_faturas.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }

        // # Filter
        if(!is_null($store) and $request->segment(3) == 'filter'){

            // :: Tipo de Pessoa
            /*if(isset($store['tipo'])){
                $list->where('tipo',$store['tipo']);
            }

            // :: CNPJ
            if(isset($store['cnpj'])){
                $list->where('cnpj',$store['cnpj']);
            }

            // :: CPF
            if(isset($store['cpf'])){
                $list->where('cpf',$store['cpf']);
            }*/

            // :: Status
            /*if(isset($store['status'])){
                $list->where('status',$store['status']);
            }*/

        }
        // - #

        $list = $list->get();

        return $list;
    }

    public static function getAllByApi($limit = 100)
    {
        $user = Auth::user();
        if (Permissions::permissaoModerador($user)){
            $list = MRAGIuguFaturas::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRAGIuguFaturas::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_g_iugu_faturas.r_auth", 0)->orWhere("mra_g_iugu_faturas.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    // :: Suspender Quando Expirar -| iugu_suspend_on_invoice_expired
    /*public static function Get_options_iugu_suspend_on_invoice_expired($unsets = null)
    {
        $options = array (
            "" => "---",
            0  => "Não",
            1  => "Sim"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_iugu_suspend_on_invoice_expired($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_iugu_suspend_on_invoice_expired();
        if (isset($options[$value])) {
            return $options[$value];
        }
    }*/

    public function MRAGIuguClientes(){
        return $this->belongsTo('App\Models\MRAGIuguClientes', 'mra_g_iugu_clientes_id');
    }

    public function MRAGIuguPlanos(){
        return $this->belongsTo('App\Models\MRAGIuguPlanos', 'mra_g_iugu_planos_id');
    }

    public function MRAGIuguAssinaturas(){
        return $this->belongsTo('App\Models\MRAGIuguAssinaturas', 'mra_g_iugu_assinaturas_id');
    }

    public function MRAGIuguFaturasItens(){
        return $this->hasMany('App\Models\MRAGIuguFaturasItens', 'mra_g_iugu_faturas_id','id')->orderBy('id','ASC');
    }

    /*public static function lista_assinaturas()
    {
        $MRAGIuguFaturas = MRAGIuguFaturas::/ *where('status',1)->* /orderBy('nome', 'ASC')->pluck('nome', 'mra_g_iugu_faturas.id')->prepend("---", "");
        return $MRAGIuguFaturas;
    }*/

}

