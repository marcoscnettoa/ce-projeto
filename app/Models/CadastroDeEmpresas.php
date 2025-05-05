<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema; // # -
use \App\Models\Permissions;
use Auth;

class CadastroDeEmpresas extends Model
{
   	protected $table = 'cadastro_de_empresas';
   	protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['_token'];

    public $filter_with = [];

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
            $list = CadastroDeEmpresas::limit($limit)
                ->orderBy($field, 'ASC')
                ->pluck($field, 'cadastro_de_empresas.id')
                ->prepend("", "");
        }
        else
        {
            $list = CadastroDeEmpresas::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("cadastro_de_empresas.r_auth", 0)->orWhere("cadastro_de_empresas.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($field, 'ASC')
            ->pluck($field, 'cadastro_de_empresas.id')
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
        if(Schema::hasColumn('cadastro_de_empresas','kb_order')){
            $orderBy = 'kb_order';
        }

        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = CadastroDeEmpresas::limit($limit)
                ->orderBy($orderBy, 'ASC')
                ->pluck($field, 'cadastro_de_empresas.id')
                ->prepend("", "");
        }
        else
        {
            $list = CadastroDeEmpresas::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("cadastro_de_empresas.r_auth", 0)->orWhere("cadastro_de_empresas.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($orderBy, 'ASC')
            ->pluck($field, 'cadastro_de_empresas.id')
            ->prepend("", "");
        }

        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = CadastroDeEmpresas::with([])->select('*')->limit($limit)
                ->orderBy('id', 'DESC')
                ->get();
        }
        else
        {
            $list = CadastroDeEmpresas::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("cadastro_de_empresas.r_auth", 0)->orWhere("cadastro_de_empresas.r_auth", $user_id);
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
            $list_count = CadastroDeEmpresas::with([])->select('*')->count();
        }
        else
        {
            $list_count = CadastroDeEmpresas::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;
                if ($user) {
                    $user_id = $user->id;
                }
                $q->where("cadastro_de_empresas.r_auth", 0)->orWhere("cadastro_de_empresas.r_auth", $user_id);
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
            $list = CadastroDeEmpresas::select('*')->limit($limit)
                ->orderBy('id', 'DESC');
        }
        else
        {
            $list = CadastroDeEmpresas::select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("cadastro_de_empresas.r_auth", 0)->orWhere("cadastro_de_empresas.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy('id', 'DESC');
        }

        return $list;
    }

}
