<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRAFPlanoContas extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_f_plano_contas';
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
            $list = MRAFPlanoContas::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_f_plano_contas.id')
                                        ->prepend("", "");
        }else {
            $list = MRAFPlanoContas::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_plano_contas.r_auth", 0)->orWhere("mra_f_plano_contas.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_f_plano_contas.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user   = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = MRAFPlanoContas::
                    with([])
                    ->select('*')
                    ->orderBy('id', 'DESC')
                    ->limit($limit);
        }else {
            $list = MRAFPlanoContas::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_plano_contas.r_auth", 0)->orWhere("mra_f_plano_contas.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }

        // # Filter
        if(!is_null($store) and $request->segment(3) == 'filter'){

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
            $list = MRAFPlanoContas::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRAFPlanoContas::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_plano_contas.r_auth", 0)->orWhere("mra_f_plano_contas.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    // :: Options - Geral
    public static function Get_PlanoDeContas_options(){
        return MRAFPlanoContas::where('status', 1)->orderBy('nome','ASC')->pluck('nome', 'id')->prepend("---", "");
    }
}

