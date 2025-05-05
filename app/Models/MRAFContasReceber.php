<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRAFContasReceber extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_f_contas_receber';
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
            $list = MRAFContasReceber::limit($limit)
                                        ->orderBy($field, 'DESC')
                                        ->pluck($field, 'mra_f_contas_receber.id')
                                        ->prepend("", "");
        }else {
            $list = MRAFContasReceber::
                    where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_contas_receber.r_auth", 0)->orWhere("mra_f_contas_receber.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy($field, 'DESC')
                    ->pluck($field, 'mra_f_contas_receber.id')
                    ->prepend("", "");
        }
        return $list;
    }

    public static function getAll($limit = 100, $request = null)
    {
        $user   = Auth::user();

        $store  = (!is_null($request)?$request->all():null);

        if (Permissions::permissaoModerador($user)){
            $list = MRAFContasReceber::
                    with([])
                    ->select('*')
                    ->orderBy('data_competencia', 'DESC')
                    ->limit($limit);
        }else {
            $list = MRAFContasReceber::
                    with([])
                    ->select('*')
                    ->where(function($q) use ($user){
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_contas_receber.r_auth", 0)->orWhere("mra_f_contas_receber.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('data_competencia', 'DESC');
        }

        // # Filter
        if(!is_null($store) and $request->segment(3) == 'filter'){

            // :: Data de Compentência
            if(isset($store['data_competencia']) and !empty($store['data_competencia'])){
                $list->where(function($q) use($store){
                    if($store['operador']['data_competencia'] == 'contem'){
                        $q->where('data_competencia','LIKE', '%'.$store['data_competencia'].'%');
                    }elseif($store['operador']['data_competencia'] == 'entre'){
                        $q->whereBetween(DB::raw('DATE_FORMAT(data_competencia, "%Y-%m-%d")'),[$store['data_competencia'],$store['between']['data_competencia']]);
                    }else {
                        $q->where('data_competencia',$store['operador']['data_competencia'],$store['data_competencia']);
                    }
                });
            }

            // :: Vencimento
            if(isset($store['vencimento']) and !empty($store['vencimento'])){
                $list->where(function($q) use($store){
                    if($store['operador']['vencimento'] == 'contem'){
                        $q->where('vencimento','LIKE', '%'.$store['vencimento'].'%');
                    }elseif($store['operador']['vencimento'] == 'entre'){
                        $q->whereBetween(DB::raw('DATE_FORMAT(vencimento, "%Y-%m-%d")'),[$store['vencimento'],$store['between']['vencimento']]);
                    }else {
                        $q->where('vencimento',$store['operador']['vencimento'],$store['vencimento']);
                    }
                });
            }

            // :: Clientes
            if(isset($store['mra_f_clientes_id'])){
                $list->where('mra_f_clientes_id',$store['mra_f_clientes_id']);
            }

            // :: Tipo de Pagamento
            if(isset($store['tipo_pagamento'])){
                $list->where('tipo_pagamento',$store['tipo_pagamento']);
            }

            // :: Status
            if(isset($store['status'])){
                $list->where('status',$store['status']);
            }

            // :: Status - Sim
            if(isset($store['anexos'])){
                $list->where(function($q) use($store) {
                    if($store['anexos']){
                        $q->whereNotNull('anexo');
                        $q->orWhereNotNull('anexo2');
                    }else {
                        $q->whereNull('anexo');
                        $q->whereNull('anexo2');
                    }
                });
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
            $list = MRAFContasReceber::
                    select('*')
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }else {
            $list = MRAFContasReceber::
                    select('*')
                    ->where(function($q) use ($user)
                    {
                        $user_id = 0;
                        if ($user) {
                            $user_id = $user->id;
                        }
                        $q->where("mra_f_contas_receber.r_auth", 0)->orWhere("mra_f_contas_receber.r_auth", $user_id);
                    })
                    ->limit($limit)
                    ->orderBy('id', 'DESC');
        }
        return $list;
    }

    public function MRAFClientes(){
        return $this->belongsTo('App\Models\MRAFClientes', 'mra_f_clientes_id');
    }

    public function MRAFCentroCusto(){
        return $this->belongsTo('App\Models\MRAFCentroCusto', 'mra_f_centro_custo_id');
    }

    public function MRAFPlanoContas(){
        return $this->belongsTo('App\Models\MRAFPlanoContas', 'mra_f_plano_contas_id');
    }

    public function MRAFContasBancarias(){
        return $this->belongsTo('App\Models\MRAFContasBancarias', 'mra_f_contas_bancarias_id');
    }

    public function MRAFContasReceberParcelas(){
        return $this->hasMany('App\Models\MRAFContasReceberParcelas', 'mra_f_contas_receber_id','id')->orderBy('id','ASC');
    }

    // :: Qt Status Concluídos
    public static function Get_QtStatus_Concluidos(){
        return MRAFContasReceber::where('status', 1)->count();
    }

    // :: Qt Status Pendentes
    public static function Get_QtStatus_Pendentes(){
        return MRAFContasReceber::where('status', 2)->count();
    }

    // :: Valor Total - Pagamentos Pagos
    public static function Get_ValorTotalPagamentos_Pagos(){
        // ! (Valor + Juros + Multa) | tipo_pagamento 1 = À Vista | av_status_pagamento 1 = Pago
        $CR_AVista_ValorJurosMulta_Pagos = MRAFContasReceber::select(DB::raw('SUM(COALESCE(valor,0) + COALESCE(juros,0) + COALESCE(multa,0) - COALESCE(valor_entrada,0)) AS sum'))
                                                            ->where('tipo_pagamento',1)
                                                            ->where('av_status_pagamento',1)
                                                            ->first()->sum;
        // ! Entradas | entrada_status_pagamento 1 = Pago
        $CR_Entradas_Pagos               = MRAFContasReceber::select(DB::raw('SUM(COALESCE(valor_entrada,0)) AS sum'))
                                                            ->where('entrada_status_pagamento',1)
                                                            ->first()->sum;
        // ! Parcelas | status_pagamento 1 = Pago
        $CR_Parcelas_Pagos               = MRAFContasReceberParcelas::select(DB::raw('SUM(COALESCE(mra_f_contas_receber_parc.valor,0)) AS sum'))
                                                                    ->leftJoin('mra_f_contas_receber','mra_f_contas_receber.id','mra_f_contas_receber_parc.mra_f_contas_receber_id')
                                                                    ->where('mra_f_contas_receber_parc.status_pagamento',1)
                                                                    ->whereNull('mra_f_contas_receber.deleted_at')
                                                                    ->first()->sum;

        return ($CR_AVista_ValorJurosMulta_Pagos + $CR_Entradas_Pagos + $CR_Parcelas_Pagos);
    }

    // :: Valor Total - Pagamentos Pendentes
    public static function Get_ValorTotalPagamentos_Pendentes(){
        // ! (Valor + Juros + Multa) | tipo_pagamento 1 = À Vista | av_status_pagamento 1 = Pago
        $CR_AVista_ValorJurosMulta_Pagos = MRAFContasReceber::select(DB::raw('SUM(COALESCE(valor,0) + COALESCE(juros,0) + COALESCE(multa,0) - COALESCE(valor_entrada,0)) AS sum'))
                                                            ->where('tipo_pagamento',1)
                                                            ->where('av_status_pagamento',2)
                                                            ->first()->sum;
        // ! Entradas | entrada_status_pagamento 1 = Pago
        $CR_Entradas_Pagos               = MRAFContasReceber::select(DB::raw('SUM(COALESCE(valor_entrada,0)) AS sum'))
                                                            ->where('entrada_status_pagamento',2)
                                                            ->first()->sum;
        // ! Parcelas | status_pagamento 1 = Pago
        $CR_Parcelas_Pagos               = MRAFContasReceberParcelas::select(DB::raw('SUM(COALESCE(mra_f_contas_receber_parc.valor,0)) AS sum'))
                                                                    ->leftJoin('mra_f_contas_receber','mra_f_contas_receber.id','mra_f_contas_receber_parc.mra_f_contas_receber_id')
                                                                    ->where('mra_f_contas_receber_parc.status_pagamento',2)
                                                                    ->whereNull('mra_f_contas_receber.deleted_at')
                                                                    ->first()->sum;

        return ($CR_AVista_ValorJurosMulta_Pagos + $CR_Entradas_Pagos + $CR_Parcelas_Pagos);
    }

}

