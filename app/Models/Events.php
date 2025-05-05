<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Models\Permissions;
use Auth;

class Events extends Model
{
   	protected $table = 'events';
   	protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['_token'];

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
            $list = Events::limit($limit)
                ->orderBy($field, 'DESC')
                ->pluck($field, 'events.id')
                ->prepend("", ""); 
        } 
        else 
        {
            $list = Events::where(function($q) use ($user) 
            { 
                $user_id = NULL;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("events.r_auth", 0)->orWhere("events.r_auth", $user_id); 
            })
            ->limit($limit)
            ->orderBy($field, 'DESC')
            ->pluck($field, 'events.id')
            ->prepend("", ""); 
        }

        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = Events::select('*')->limit($limit)
                ->orderBy('id', 'DESC')
                ->get(); 
        } 
        else 
        {
            $list = Events::select('*')->where(function($q) use ($user) 
            { 
                $user_id = NULL;

                if ($user) {
                    $user_id = $user->id;
                }

                $q->where("events.r_auth", 0)->orWhere("events.r_auth", $user_id); 
            })
            ->limit($limit)
            ->orderBy('id', 'DESC')
            ->get(); 
        }

        return $list;
    }

}
