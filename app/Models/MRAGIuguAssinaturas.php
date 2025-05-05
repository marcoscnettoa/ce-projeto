<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRAGIuguAssinaturas extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_g_iugu_assinaturas';
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
            $list = MRAGIuguAssinaturas::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_g_iugu_assinaturas.id')
                                        ->prepend("", "");
        }else {
            $list = MRAGIuguAssinaturas::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_g_iugu_assinaturas.r_auth", 0)->orWhere("mra_g_iugu_assinaturas.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_g_iugu_assinaturas.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user   = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = MRAGIuguAssinaturas::
                    with([])
                    ->select('*')
                    ->orderBy('id', 'DESC')
                    ->limit($limit);
        }else {
            $list = MRAGIuguAssinaturas::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_g_iugu_assinaturas.r_auth", 0)->orWhere("mra_g_iugu_assinaturas.r_auth", $user_id);
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
            $list = MRAGIuguAssinaturas::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRAGIuguAssinaturas::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_g_iugu_assinaturas.r_auth", 0)->orWhere("mra_g_iugu_assinaturas.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    // :: Suspenso -| iugu_suspended
    public static function Get_options_iugu_suspended($unsets = null)
    {
        $options = array (
            "" => "---",
            0  => "Ativo",
            1  => "Suspenso"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_iugu_suspended($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_iugu_suspended();
        if (isset($options[$value])) {
            return $options[$value];
        }
    }

    // :: Ativo -| iugu_active
    public static function Get_options_iugu_active($unsets = null)
    {
        $options = array (
            "" => "---",
            0  => "Não",
            1  => "Sim"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_iugu_active($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_iugu_active();
        if (isset($options[$value])) {
            return $options[$value];
        }
    }

    // :: Ativo -| skip_charge
    public static function Get_options_skip_charge($unsets = null)
    {
        $options = array (
            "" => "---",
            0  => "Não",
            1  => "Sim"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_skip_charge($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_iugu_active();
        if (isset($options[$value])) {
            return $options[$value];
        }
    }

    // :: Suspender Quando Expirar -| iugu_suspend_on_invoice_expired
    public static function Get_options_iugu_suspend_on_invoice_expired($unsets = null)
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
    }

    public function MRAGIuguPlanos(){
        return $this->belongsTo('App\Models\MRAGIuguPlanos', 'mra_g_iugu_planos_id');
    }

    public function MRAGIuguClientes(){
        return $this->belongsTo('App\Models\MRAGIuguClientes', 'mra_g_iugu_clientes_id');
    }

    /*public static function lista_assinaturas()
    {
        $MRAGIuguAssinaturas = MRAGIuguAssinaturas::/ *where('status',1)->* /orderBy('nome', 'ASC')->pluck('nome', 'mra_g_iugu_assinaturas.id')->prepend("---", "");
        return $MRAGIuguAssinaturas;
    }*/

}

