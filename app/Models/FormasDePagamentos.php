<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema; // # -
use \App\Models\Permissions;
use Auth;

class FormasDePagamentos extends Model
{
   	protected $table = 'formas_de_pagamentos';
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
            $list = FormasDePagamentos::limit($limit)
                ->orderBy($field, 'ASC')
                ->pluck($field, 'formas_de_pagamentos.id')
                ->prepend("", "");
        }
        else
        {
            $list = FormasDePagamentos::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("formas_de_pagamentos.r_auth", 0)->orWhere("formas_de_pagamentos.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($field, 'ASC')
            ->pluck($field, 'formas_de_pagamentos.id')
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
        if(Schema::hasColumn('formas_de_pagamentos','kb_order')){
            $orderBy = 'kb_order';
        }

        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = FormasDePagamentos::limit($limit)
                ->orderBy($orderBy, 'ASC')
                ->pluck($field, 'formas_de_pagamentos.id')
                ->prepend("", "");
        }
        else
        {
            $list = FormasDePagamentos::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("formas_de_pagamentos.r_auth", 0)->orWhere("formas_de_pagamentos.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($orderBy, 'ASC')
            ->pluck($field, 'formas_de_pagamentos.id')
            ->prepend("", "");
        }

        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = FormasDePagamentos::with([])->select('*')->limit($limit)
                ->orderBy('id', 'DESC')
                ->get();
        }
        else
        {
            $list = FormasDePagamentos::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("formas_de_pagamentos.r_auth", 0)->orWhere("formas_de_pagamentos.r_auth", $user_id);
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
            $list_count = FormasDePagamentos::with([])->select('*')->count();
        }
        else
        {
            $list_count = FormasDePagamentos::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;
                if ($user) {
                    $user_id = $user->id;
                }
                $q->where("formas_de_pagamentos.r_auth", 0)->orWhere("formas_de_pagamentos.r_auth", $user_id);
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
            $list = FormasDePagamentos::select('*')->limit($limit)
                ->orderBy('id', 'DESC');
        }
        else
        {
            $list = FormasDePagamentos::select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("formas_de_pagamentos.r_auth", 0)->orWhere("formas_de_pagamentos.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy('id', 'DESC');
        }

        return $list;
    }

}
