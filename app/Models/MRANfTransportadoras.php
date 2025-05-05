<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRANfTransportadoras extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_nf_transp';
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
            $list = MRANfTransportadoras::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_nf_transp.id')
                                        ->prepend("", "");
        }else {
            $list = MRANfTransportadoras::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_transp.r_auth", 0)->orWhere("mra_nf_transp.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_nf_transp.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();
        if (Permissions::permissaoModerador($user)){
            $list = MRANfTransportadoras::
                    with([])
                    ->select('*')
                    ->orderBy('id', 'DESC')
                    ->limit($limit)
                    ->get();
        }else {
            $list = MRANfTransportadoras::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_transp.r_auth", 0)->orWhere("mra_nf_transp.r_auth", $user_id);
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
            $list = MRANfTransportadoras::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRANfTransportadoras::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_nf_transp.r_auth", 0)->orWhere("mra_nf_transp.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    public static function lista_transportadoras()
    {
        $MRANfTransportadoras = MRANfTransportadoras::where('status',1)->orderBy('nome', 'ASC')->pluck('nome', 'mra_nf_transp.id')->prepend("---", "");
        return $MRANfTransportadoras;
    }

}

