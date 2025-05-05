<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class RNfClientesTs extends Model
{
    use SoftDeletes;

    protected $table        = 'r_nf_clientes_ts';
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
            $list = RNfClientesTs::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'r_nf_clientes_ts.id')
                                        ->prepend("", "");
        }else {
            $list = RNfClientesTs::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("r_nf_clientes_ts.r_auth", 0)->orWhere("r_nf_clientes_ts.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'r_nf_clientes_ts.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();
        if (Permissions::permissaoModerador($user)){
            $list = RNfClientesTs::
                    with([])
                    ->select('*')
                    ->orderBy('id', 'DESC')
                    ->limit($limit)
                    ->get();
        }else {
            $list = RNfClientesTs::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("r_nf_clientes_ts.r_auth", 0)->orWhere("r_nf_clientes_ts.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC')
                    ->get();
        }
        return $list;
    }

    public static function getAllByApi($limit = 100)
    {
        $user = Auth::user();
        if (Permissions::permissaoModerador($user)){
            $list = RNfClientesTs::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = RNfClientesTs::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("r_nf_clientes_ts.r_auth", 0)->orWhere("r_nf_clientes_ts.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    public static function lista_clientes()
    {
        $RNfClientes = RNfClientesTs::where('status',1)->orderBy('nome', 'ASC')->pluck('nome', 'r_nf_clientes_ts.id')->prepend("---", "");
        return $RNfClientes;
    }

}

