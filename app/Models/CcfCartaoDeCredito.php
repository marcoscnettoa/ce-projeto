<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema; // # -
use \App\Models\Permissions;
use Auth;

class CcfCartaoDeCredito extends Model
{
   	protected $table = 'ccf_cartao_de_credito';
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
            $list = CcfCartaoDeCredito::limit($limit)
                ->orderBy($field, 'ASC')
                ->pluck($field, 'ccf_cartao_de_credito.id')
                ->prepend("", "");
        }
        else
        {
            $list = CcfCartaoDeCredito::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("ccf_cartao_de_credito.r_auth", 0)->orWhere("ccf_cartao_de_credito.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($field, 'ASC')
            ->pluck($field, 'ccf_cartao_de_credito.id')
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
        if(Schema::hasColumn('ccf_cartao_de_credito','kb_order')){
            $orderBy = 'kb_order';
        }

        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = CcfCartaoDeCredito::limit($limit)
                ->orderBy($orderBy, 'ASC')
                ->pluck($field, 'ccf_cartao_de_credito.id')
                ->prepend("", "");
        }
        else
        {
            $list = CcfCartaoDeCredito::where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("ccf_cartao_de_credito.r_auth", 0)->orWhere("ccf_cartao_de_credito.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy($orderBy, 'ASC')
            ->pluck($field, 'ccf_cartao_de_credito.id')
            ->prepend("", "");
        }

        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = CcfCartaoDeCredito::with([])->select('*')->limit($limit)
                ->orderBy('id', 'DESC')
                ->get();
        }
        else
        {
            $list = CcfCartaoDeCredito::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("ccf_cartao_de_credito.r_auth", 0)->orWhere("ccf_cartao_de_credito.r_auth", $user_id);
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
            $list_count = CcfCartaoDeCredito::with([])->select('*')->count();
        }
        else
        {
            $list_count = CcfCartaoDeCredito::with([])->select('*')->where(function($q) use ($user)
            {
                $user_id = 0;
                if ($user) {
                    $user_id = $user->id;
                }
                $q->where("ccf_cartao_de_credito.r_auth", 0)->orWhere("ccf_cartao_de_credito.r_auth", $user_id);
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
            $list = CcfCartaoDeCredito::select('*')->limit($limit)
                ->orderBy('id', 'DESC');
        }
        else
        {
            $list = CcfCartaoDeCredito::select('*')->where(function($q) use ($user)
            {
                $user_id = 0;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("ccf_cartao_de_credito.r_auth", 0)->orWhere("ccf_cartao_de_credito.r_auth", $user_id);
            })
            ->limit($limit)
            ->orderBy('id', 'DESC');
        }

        return $list;
    }

}
