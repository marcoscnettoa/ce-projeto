<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema; // # -
use \App\Models\Permissions;
use Auth;

class Fornecedores extends Model
{
   	protected $table = 'fornecedores';
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
            $list = Fornecedores::limit($limit)
                ->orderBy($field, 'ASC')
                ->pluck($field, 'fornecedores.id')
                ->prepend("", "");
        }
        else
        {
            $list = Fornecedores::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("fornecedores.r_auth", 0)->orWhere("fornecedores.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($field, 'ASC')
            ->pluck($field, 'fornecedores.id')
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
        if(Schema::hasColumn('fornecedores','kb_order')){
            $orderBy = 'kb_order';
        }

        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = Fornecedores::limit($limit)
                ->orderBy($orderBy, 'ASC')
                ->pluck($field, 'fornecedores.id')
                ->prepend("", "");
        }
        else
        {
            $list = Fornecedores::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("fornecedores.r_auth", 0)->orWhere("fornecedores.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($orderBy, 'ASC')
            ->pluck($field, 'fornecedores.id')
            ->prepend("", "");
        }

        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = Fornecedores::with([])->select('*')->limit($limit)
                ->orderBy('fornecedor', 'DESC')
                ->get();
        }
        else
        {
            $list = Fornecedores::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("fornecedores.r_auth", 0)->orWhere("fornecedores.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy('fornecedor', 'DESC')
            ->get();
        }

        return $list;
    }

    public static function getAllCount()
    {
        $user           = Auth::user();
        if(Permissions::permissaoModerador($user))
        {
            $list_count = Fornecedores::with([])->select('*')->count();
        }
        else
        {
            $list_count = Fornecedores::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;
                if ($user) {
                    $user_id = $user->id;
                }
                $q->where("fornecedores.r_auth", 0)->orWhere("fornecedores.r_auth", $user_id);
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
            $list = Fornecedores::select('*')->limit($limit)
                ->orderBy('fornecedor', 'DESC');
        }
        else
        {
            $list = Fornecedores::select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("fornecedores.r_auth", 0)->orWhere("fornecedores.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy('fornecedor', 'DESC');
        }

        return $list;
    }

}
