<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema; // # -
use \App\Models\Permissions;
use Auth;

class Companhias extends Model
{
   	protected $table = 'companhias';
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
            $list = Companhias::limit($limit)
                ->orderBy($field, 'ASC')
                ->pluck($field, 'companhias.id')
                ->prepend("", "");
        }
        else
        {
            $list = Companhias::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("companhias.r_auth", 0)->orWhere("companhias.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($field, 'ASC')
            ->pluck($field, 'companhias.id')
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
        if(Schema::hasColumn('companhias','kb_order')){
            $orderBy = 'kb_order';
        }

        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = Companhias::limit($limit)
                ->orderBy($orderBy, 'ASC')
                ->pluck($field, 'companhias.id')
                ->prepend("", "");
        }
        else
        {
            $list = Companhias::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("companhias.r_auth", 0)->orWhere("companhias.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($orderBy, 'ASC')
            ->pluck($field, 'companhias.id')
            ->prepend("", "");
        }

        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = Companhias::with([])->select('*')->limit($limit)
                ->orderBy('id', 'DESC')
                ->get();
        }
        else
        {
            $list = Companhias::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("companhias.r_auth", 0)->orWhere("companhias.r_auth", $user_id);
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
            $list_count = Companhias::with([])->select('*')->count();
        }
        else
        {
            $list_count = Companhias::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;
                if ($user) {
                    $user_id = $user->id;
                }
                $q->where("companhias.r_auth", 0)->orWhere("companhias.r_auth", $user_id);
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
            $list = Companhias::select('*')->limit($limit)
                ->orderBy('id', 'DESC');
        }
        else
        {
            $list = Companhias::select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("companhias.r_auth", 0)->orWhere("companhias.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy('id', 'DESC');
        }

        return $list;
    }

}
