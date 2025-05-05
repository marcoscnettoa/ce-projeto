<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRAFExtratoBancario extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_f_extrato_bancario';
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
            $list = MRAFExtratoBancario::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_f_extrato_bancario.id')
                                        ->prepend("", "");
        }else {
            $list = MRAFExtratoBancario::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_extrato_bancario.r_auth", 0)->orWhere("mra_f_extrato_bancario.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_f_extrato_bancario.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user   = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = MRAFExtratoBancario::
                    with([])
                    ->select('*')
                    ->orderBy('id', 'DESC')
                    ->limit($limit);
        }else {
            $list = MRAFExtratoBancario::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_extrato_bancario.r_auth", 0)->orWhere("mra_f_extrato_bancario.r_auth", $user_id);
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

            // :: Cliente
            if(isset($store['mra_f_clientes_id'])){
                $list->where('mra_f_clientes_id',$store['mra_f_clientes_id']);
            }

            // :: Fornecedor
            if(isset($store['mra_f_fornecedores_id'])){
                $list->where('mra_f_fornecedores_id',$store['mra_f_fornecedores_id']);
            }

            // :: Conta Bancária
            if(isset($store['mra_f_contas_bancarias_id'])){
                $list->where('mra_f_contas_bancarias_id',$store['mra_f_contas_bancarias_id']);
            }

            // :: Tipo de Movimentação
            if(isset($store['tipo'])){
                $list->where('tipo',$store['tipo']);
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
            $list = MRAFExtratoBancario::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRAFExtratoBancario::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_extrato_bancario.r_auth", 0)->orWhere("mra_f_extrato_bancario.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    // :: Tipos - ( Entrada / Saída )
    public static function Get_options_tipos($unsets = null)
    {
        $options = array (
            ""  =>  "---",
            1   => 	"Entrada",
            2   =>  "Saída"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_tipos($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_tipos();
        if(isset($options[$value])) { return $options[$value]; }
    }

    // :: Status - ( Pago / Pendente )
    public static function Get_options_status($unsets = null)
    {
        $options = array (
            ""  =>  "---",
            1   => 	"Concluído",
            2   =>  "Pendente"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_status($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_status();
        if(isset($options[$value])) { return $options[$value]; }
    }

    // :: Clientes
    public function MRAFClientes(){
        return $this->belongsTo('App\Models\MRAFClientes', 'mra_f_clientes_id');
    }

    // :: Fornecedores
    public function MRAFFornecedores(){
        return $this->belongsTo('App\Models\MRAFFornecedores', 'mra_f_fornecedores_id');
    }

    // :: Contas Bancárias
    public function MRAFContasBancarias(){
        return $this->belongsTo('App\Models\MRAFContasBancarias', 'mra_f_contas_bancarias_id');
    }

    // :: Valor Total - Entradas
    public static function Get_ValorTotal_Entradas(){
        return MRAFExtratoBancario::where('tipo', 1)->sum('valor');
    }

    // :: Valor Total - Saídas
    public static function Get_ValorTotal_Saidas(){
        return MRAFExtratoBancario::where('tipo', 2)->sum('valor');
    }

}

