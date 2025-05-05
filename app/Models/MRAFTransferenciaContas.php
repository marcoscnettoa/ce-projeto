<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRAFTransferenciaContas extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_f_transf_contas';
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
            $list = MRAFTransferenciaContas::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_f_transf_contas.id')
                                        ->prepend("", "");
        }else {
            $list = MRAFTransferenciaContas::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_transf_contas.r_auth", 0)->orWhere("mra_f_transf_contas.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_f_transf_contas.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user   = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = MRAFTransferenciaContas::
                    with([])
                    ->select('*')
                    ->orderBy('id', 'DESC')
                    ->limit($limit);
        }else {
            $list = MRAFTransferenciaContas::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_transf_contas.r_auth", 0)->orWhere("mra_f_transf_contas.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }

        // # Filter
        if(!is_null($store) and $request->segment(3) == 'filter'){

            // :: Data da Movimentação
            if(isset($store['created_at']) and !empty($store['created_at'])){
                $list->where(function($q) use($store){
                    if($store['operador']['created_at'] == 'contem'){
                        $q->where('created_at','LIKE', '%'.$store['created_at'].'%');
                    }elseif($store['operador']['created_at'] == 'entre'){
                        $q->whereBetween(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d")'),[$store['created_at'],$store['between']['created_at']]);
                    }else {
                        $q->where('created_at',$store['operador']['created_at'],$store['created_at']);
                    }
                });
            }

            // :: Conta de Origem
            if(isset($store['mra_f_conta_ori_id'])){
                $list->where('mra_f_conta_ori_id',$store['mra_f_conta_ori_id']);
            }

            // :: Conta de Destino
            if(isset($store['mra_f_conta_des_id'])){
                $list->where('mra_f_conta_des_id',$store['mra_f_conta_des_id']);
            }

            // :: Descrição
            if(isset($store['descricao'])){
                $list->where('descricao','LIKE', '%'.$store['descricao'].'%');
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
            $list = MRAFTransferenciaContas::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRAFTransferenciaContas::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_transf_contas.r_auth", 0)->orWhere("mra_f_transf_contas.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    // :: Options - Geral
    public static function Get_ContasBancarias_options(){
        return MRAFContasBancarias::where('status', 1)->orderBy('nome','ASC')->pluck('nome', 'id')->prepend("---", "");
    }

    // :: Conta de Origem
    public function MRAFContasBancarias_origem(){
        return $this->belongsTo('App\Models\MRAFContasBancarias', 'mra_f_conta_ori_id');
    }

    // :: Conta de Destino
    public function MRAFContasBancarias_destino(){
        return $this->belongsTo('App\Models\MRAFContasBancarias', 'mra_f_conta_des_id');
    }

}

