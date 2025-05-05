<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema; // # -
use \App\Models\Permissions;
use Auth;

class Faturamento extends Model
{
   	protected $table = 'faturamento';
   	protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['_token'];

    public $filter_with = ['Cliente','Template'];

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
            $list = Faturamento::limit($limit)
                ->orderBy($field, 'ASC')
                ->pluck($field, 'faturamento.id')
                ->prepend("", "");
        }
        else
        {
            $list = Faturamento::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("faturamento.r_auth", 0)->orWhere("faturamento.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($field, 'ASC')
            ->pluck($field, 'faturamento.id')
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
        if(Schema::hasColumn('faturamento','kb_order')){
            $orderBy = 'kb_order';
        }

        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = Faturamento::limit($limit)
                ->orderBy($orderBy, 'ASC')
                ->pluck($field, 'faturamento.id')
                ->prepend("", "");
        }
        else
        {
            $list = Faturamento::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("faturamento.r_auth", 0)->orWhere("faturamento.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($orderBy, 'ASC')
            ->pluck($field, 'faturamento.id')
            ->prepend("", "");
        }

        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = Faturamento::with(['Cliente','Template'])->select('*')->limit($limit)
                ->orderBy('id', 'DESC')
                ->get();
        }
        else
        {
            $list = Faturamento::with(['Cliente','Template'])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("faturamento.r_auth", 0)->orWhere("faturamento.r_auth", $user_id);
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
            $list_count = Faturamento::with([])->select('*')->count();
        }
        else
        {
            $list_count = Faturamento::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;
                if ($user) {
                    $user_id = $user->id;
                }
                $q->where("faturamento.r_auth", 0)->orWhere("faturamento.r_auth", $user_id);
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
            $list = Faturamento::select('*')->limit($limit)
                ->orderBy('id', 'DESC');
        }
        else
        {
            $list = Faturamento::select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("faturamento.r_auth", 0)->orWhere("faturamento.r_auth", $user_id);
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

    public function Template()
    {
        return $this->belongsTo("App\Models\Templates", 'template');
    }

}
