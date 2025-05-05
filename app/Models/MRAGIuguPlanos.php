<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRAGIuguPlanos extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_g_iugu_planos';
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
            $list = MRAGIuguPlanos::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_g_iugu_planos.id')
                                        ->prepend("", "");
        }else {
            $list = MRAGIuguPlanos::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_g_iugu_planos.r_auth", 0)->orWhere("mra_g_iugu_planos.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_g_iugu_planos.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user   = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = MRAGIuguPlanos::
                    with([])
                    ->select('*')
                    ->orderBy('id', 'DESC')
                    ->limit($limit);
        }else {
            $list = MRAGIuguPlanos::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_g_iugu_planos.r_auth", 0)->orWhere("mra_g_iugu_planos.r_auth", $user_id);
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
            $list = MRAGIuguPlanos::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRAGIuguPlanos::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_g_iugu_planos.r_auth", 0)->orWhere("mra_g_iugu_planos.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    public static function lista_planos()
    {
        $MRAGIuguPlanos = MRAGIuguPlanos::/*where('status',1)->*/orderBy('nome', 'ASC')->pluck('nome', 'mra_g_iugu_planos.id')->prepend("---", "");
        return $MRAGIuguPlanos;
    }

}

