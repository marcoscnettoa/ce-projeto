<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRAFFornecedores extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_f_fornecedores';
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
            $list = MRAFFornecedores::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_f_fornecedores.id')
                                        ->prepend("", "");
        }else {
            $list = MRAFFornecedores::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_fornecedores.r_auth", 0)->orWhere("mra_f_fornecedores.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_f_fornecedores.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user   = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = MRAFFornecedores::
                    with([])
                    ->select('*')
                    ->orderBy('id', 'DESC')
                    ->limit($limit);
        }else {
            $list = MRAFFornecedores::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_fornecedores.r_auth", 0)->orWhere("mra_f_fornecedores.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }

        // # Filter
        if(!is_null($store) and $request->segment(3) == 'filter'){

            // :: Tipo de Pessoa
            if(isset($store['tipo'])){
                $list->where('tipo',$store['tipo']);
            }

            // :: CNPJ
            if(isset($store['cnpj'])){
                $list->where('cnpj',$store['cnpj']);
            }

            // :: CPF
            if(isset($store['cpf'])){
                $list->where('cpf',$store['cpf']);
            }

            // :: Status
            if(isset($store['status'])){
                $list->where('status',$store['status']);
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
            $list = MRAFFornecedores::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRAFFornecedores::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_fornecedores.r_auth", 0)->orWhere("mra_f_fornecedores.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    public static function lista_fornecedores()
    {
        $MRAFFornecedores = MRAFFornecedores::where('status',1)->orderBy('nome', 'ASC')->pluck('nome', 'mra_f_fornecedores.id')->prepend("---", "");
        return $MRAFFornecedores;
    }

}

