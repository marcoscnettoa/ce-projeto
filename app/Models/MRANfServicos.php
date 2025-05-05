<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRANfServicos extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_nf_prod_serv';
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
            $list = MRANfServicos::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_nf_prod_serv.id')
                                        ->prepend("", "");
        }else {
            $list = MRANfServicos::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_prod_serv.r_auth", 0)->orWhere("mra_nf_prod_serv.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_nf_prod_serv.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();
        if (Permissions::permissaoModerador($user)){
            $list = MRANfServicos::
                    with([])
                    ->select('*')
                    ->orderBy('id', 'DESC')
                    ->limit($limit)
                    ->get();
        }else {
            $list = MRANfServicos::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_prod_serv.r_auth", 0)->orWhere("mra_nf_prod_serv.r_auth", $user_id);
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
            $list = MRANfServicos::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRANfServicos::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_prod_serv.r_auth", 0)->orWhere("mra_nf_prod_serv.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    public static function lista_servicos()
    {
        $MRANfServicos = MRANfServicos::where('status',1)->orderBy('nome', 'ASC')->pluck('nome', 'id')->prepend("---", "");
        return $MRANfServicos;
    }

}

