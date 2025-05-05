<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema; // # -
use \App\Models\Permissions;
use Auth;

class ContasAPagar extends Model
{
   	protected $table = 'contas_a_pagar';
   	protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['_token'];

    public $filter_with = ['ReferenteA','Fornecedor','FormaDePagamento'];

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

        if (Permissions::permissaoModerador($user))
        {
            $list = ContasAPagar::limit($limit)
                ->orderBy($field, 'ASC')
                ->pluck($field, 'contas_a_pagar.id')
                ->prepend("", "");
        }
        else
        {
            $list = ContasAPagar::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("contas_a_pagar.r_auth", 0)->orWhere("contas_a_pagar.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($field, 'ASC')
            ->pluck($field, 'contas_a_pagar.id')
            ->prepend("", "");
        }

        return $list;
    }

    // # -
    public static function kanban_list($limit = 100, $field = false)
    {

        if (!$field) {
            $field   = 'id';
        }

        $orderBy     = 'id';
        if(Schema::hasColumn('contas_a_pagar','kb_order')){
            $orderBy = 'kb_order';
        }

        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = ContasAPagar::limit($limit)
                ->orderBy($orderBy, 'ASC')
                ->pluck($field, 'contas_a_pagar.id')
                ->prepend("", "");
        }
        else
        {
            $list = ContasAPagar::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("contas_a_pagar.r_auth", 0)->orWhere("contas_a_pagar.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($orderBy, 'ASC')
            ->pluck($field, 'contas_a_pagar.id')
            ->prepend("", "");
        }

        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = ContasAPagar::with(['ReferenteA','Fornecedor','FormaDePagamento'])->select('*')->limit($limit)
                ->orderBy('id', 'DESC')
                ->get();
        }
        else
        {
            $list = ContasAPagar::with(['ReferenteA','Fornecedor','FormaDePagamento'])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("contas_a_pagar.r_auth", 0)->orWhere("contas_a_pagar.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy('id', 'DESC')
            ->get();
        }

        return $list;
    }

    public static function getAllCount()
    {
        $user           = Auth::user();
        if(Permissions::permissaoModerador($user))
        {
            $list_count = ContasAPagar::with([])->select('*')->count();
        }
        else
        {
            $list_count = ContasAPagar::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;
                if ($user) {
                    $user_id = $user->id;
                }
                $q->where("contas_a_pagar.r_auth", 0)->orWhere("contas_a_pagar.r_auth", $user_id);
            })
                ->count();
        }
        return $list_count;
    }

    public static function getAllByApi($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = ContasAPagar::select('*')->limit($limit)
                ->orderBy('id', 'DESC');
        }
        else
        {
            $list = ContasAPagar::select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("contas_a_pagar.r_auth", 0)->orWhere("contas_a_pagar.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy('id', 'DESC');
        }

        return $list;
    }

    public function ReferenteA()
    {
        return $this->belongsTo("App\Models\CadastroDeEmpresas", 'referente_a');
    }

    public function Fornecedor()
    {
        return $this->belongsTo("App\Models\Fornecedores", 'fornecedor');
    }

    public function FormaDePagamento()
    {
        return $this->belongsTo("App\Models\FormasDePagamentos", 'forma_de_pagamento');
    }

    public static function Get_options_tipo_do_valor()
    {
        $options = array (
  0 => '',
  1 => 'Valor Total',
  2 => 'Parcelas',
);

        return $options;
    }

    public function Get_tipo_do_valor()
    {
        $options = self::Get_options_tipo_do_valor();

        if (isset($options[$this->tipo_do_valor])) {
            return $options[$this->tipo_do_valor];
        }
    }

    public static function Get_options_status()
    {
        $options = array (
  0 => '',
  1 => 'Pago',
  2 => 'Aguardando Comprovante',
  3 => 'Negociando',
  4 => 'Pendente',
);

        return $options;
    }

    public function Get_status()
    {
        $options = self::Get_options_status();

        if (isset($options[$this->status])) {
            return $options[$this->status];
        }
    }

}
