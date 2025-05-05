<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema; // # -
use \App\Models\Permissions;
use Auth;

class Vendas extends Model
{
   	protected $table = 'vendas';
   	protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['_token'];

    public $filter_with = [
        'Cliente',
        'Cliente.Documentos',
        'Vendedor',
        'Produto',
        'Servico',
        'Fornecedor',
        'Companhia',
        'Trecho',
        'Template',
        'VendasGridPassageiros',
        'VendasGridPassageiros.Passageiros',
        'VendasGridPagamentos',
        'VendasGridPagamentos.FormaDePagamento'
    ];

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
            $list = Vendas::limit($limit)
                ->orderBy($field, 'ASC')
                ->pluck($field, 'vendas.id')
                ->prepend("", "");
        }
        else
        {
            $list = Vendas::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("vendas.r_auth", 0)->orWhere("vendas.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($field, 'ASC')
            ->pluck($field, 'vendas.id')
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
        if(Schema::hasColumn('vendas','kb_order')){
            $orderBy = 'kb_order';
        }

        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = Vendas::limit($limit)
                ->orderBy($orderBy, 'ASC')
                ->pluck($field, 'vendas.id')
                ->prepend("", "");
        }
        else
        {
            $list = Vendas::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("vendas.r_auth", 0)->orWhere("vendas.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($orderBy, 'ASC')
            ->pluck($field, 'vendas.id')
            ->prepend("", "");
        }

        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = Vendas::with([
                'Cliente',
                'Cliente.Documentos',
                'Vendedor',
                'Produto',
                'Servico',
                'Fornecedor',
                'Companhia',
                'Trecho',
                'Template',
                'VendasGridPassageiros',
                'VendasGridPassageiros.Passageiros',
                'VendasGridPagamentos',
                'VendasGridPagamentos.FormaDePagamento'
            ])->select('*')->limit($limit)
                ->orderBy('id', 'DESC')
                ->get();
        }
        else
        {
            $list = Vendas::with([
                'Cliente',
                'Cliente.Documentos',
                'Vendedor',
                'Produto',
                'Servico',
                'Fornecedor',
                'Companhia',
                'Trecho',
                'Template',
                'VendasGridPassageiros',
                'VendasGridPassageiros.Passageiros',
                'VendasGridPagamentos',
                'VendasGridPagamentos.FormaDePagamento'
            ])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("vendas.r_auth", 0)->orWhere("vendas.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy('id', 'DESC')
            ->get();
        }

        return $list;
    }

    // # -
    public static function getAllCount()
    {
        $user           = Auth::user();
        if(Permissions::permissaoModerador($user))
        {
            $list_count = Vendas::with([])->select('*')->count();
        }
        else
        {
            $list_count = Vendas::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;
                if ($user) {
                    $user_id = $user->id;
                }
                $q->where("vendas.r_auth", 0)->orWhere("vendas.r_auth", $user_id);
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
            $list = Vendas::select('*')->limit($limit)
                ->orderBy('id', 'DESC');
        }
        else
        {
            $list = Vendas::select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("vendas.r_auth", 0)->orWhere("vendas.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy('id', 'DESC');
        }

        return $list;
    }

    public function Cliente()
    {
        return $this->belongsTo("App\Models\Clientes", 'cliente');
    }

    public function Vendedor()
    {
        return $this->belongsTo("App\Models\Vendedores", 'vendedor');
    }

    public function Produto()
    {
        return $this->belongsTo("App\Models\Produtos", 'produto');
    }

    public function Servico()
    {
        return $this->belongsTo("App\Models\Servicos", 'servico');
    }

    public function Fornecedor()
    {
        return $this->belongsTo("App\Models\Fornecedores", 'fornecedor');
    }

    public function Companhia()
    {
        return $this->belongsTo("App\Models\Companhias", 'companhia');
    }

    public function Trecho()
    {
        return $this->belongsTo("App\Models\Trechos", 'trecho');
    }

    public function VendasGridPassageiros()
    {
        return $this->hasMany("App\Models\VendasGridPassageiros", 'vendas_id');
    }

    public function VendasGridPagamentos()
    {
        return $this->hasMany("App\Models\VendasGridPagamentos", 'vendas_id');
    }

    public function Template()
    {
        return $this->belongsTo("App\Models\Templates", 'template');
    }

    public static function Get_options_tipo_de_venda()
    {
        $options = array (
  0 => '',
  1 => 'Recibo',
  2 => 'Faturamento',
);

        return $options;
    }

    public function Get_tipo_de_venda()
    {
        $options = self::Get_options_tipo_de_venda();

        if (isset($options[$this->tipo_de_venda])) {
            return $options[$this->tipo_de_venda];
        }
    }

    public static function Get_options_foi_faturado()
    {
        $options = array (
  0 => '',
  1 => 'Sim',
  2 => 'Não',
);

        return $options;
    }

    public function Get_foi_faturado()
    {
        $options = self::Get_options_foi_faturado();

        if (isset($options[$this->foi_faturado])) {
            return $options[$this->foi_faturado];
        }
    }

}
