<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRAFContasBancarias extends Model
{
    use SoftDeletes;

    protected $table       = 'mra_f_contas_bancarias';
    protected $primaryKey  = 'id';
    protected $guarded     = ['_token'];
    public $timestamps     = true;
    public $filter_with    = [];

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
            $list = MRAFContasBancarias::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_f_contas_bancarias.id')
                                        ->prepend("", "");
        }else {
            $list = MRAFContasBancarias::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_contas_bancarias.r_auth", 0)->orWhere("mra_f_contas_bancarias.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_f_contas_bancarias.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user   = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = MRAFContasBancarias::
                    with([])
                    ->select('*')
                    ->orderBy('id', 'DESC')
                    ->limit($limit);
        }else {
            $list = MRAFContasBancarias::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_contas_bancarias.r_auth", 0)->orWhere("mra_f_contas_bancarias.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }

        // # Filter
        if(!is_null($store) and $request->segment(3) == 'filter'){

            // :: Tipo de Conta
            if(isset($store['tipo_conta'])){
                $list->where('tipo_conta',$store['tipo_conta']);
            }

            // :: Banco
            if(isset($store['mra_f_bancos_id'])){
                $list->where('mra_f_bancos_id',$store['mra_f_bancos_id']);
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
            $list = MRAFContasBancarias::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRAFContasBancarias::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_contas_bancarias.r_auth", 0)->orWhere("mra_f_contas_bancarias.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    // :: Tipos de Conta
    public static function Get_options_tipos_de_conta($unsets = null)
    {
        $options = array (
            ""  =>  "---",
            1   => 	"Corrente",
            2   =>  "Poupança",
            //3   =>  "Caixa", // ! Verificar
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    // :: Bancos
    public function MRAFBancos(){
        return $this->belongsTo('App\Models\MRAFBancos', 'mra_f_bancos_id');
    }

    public static function Get_tipos_de_conta($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_tipos_de_conta();
        if(isset($options[$value])) { return $options[$value]; }
    }

    // :: Options - Geral
    public static function Get_ContasBancarias_options(){
        return MRAFContasBancarias::where('status', 1)->orderBy('nome','ASC')->pluck('nome', 'id')->prepend("---", "");
    }

    // :: Valor Atual Total
    public static function Get_ValorAtualTotal(){
        return MRAFContasBancarias::where('status', 1)->sum('valor_atual');
    }

}

