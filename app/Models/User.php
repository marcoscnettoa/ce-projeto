<?php

namespace App\Models;

use \App\Models\Permissions;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Auth;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'profile_id', 'username'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $guarded = ['_token'];

    public function perfil()
    {
        return $this->belongsTo('App\Models\Profiles', 'profile_id');
    }

    public function __get($key)
    {
        if (is_null($this->getAttribute($key))) {
            return $this->getAttribute(strtoupper($key));
        } else {
            return $this->getAttribute($key);
        }

    }

    public function sendPasswordResetNotification($token)
    {
       $this->notify(new ResetPassword($token));
    }

    public static function list($limit = 100, $field = false)
    {
        if (!$field) {
            $field = 'id';
        }

        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = User::limit($limit)
                ->orderBy($field, 'DESC')
                ->pluck($field, 'users.id')
                ->prepend("", "");
        }
        else
        {
            $list = User::where(function($q) use ($user)
            {
                $q->where("users.r_auth", 0)->orWhere("users.r_auth", $user->id);
            })
            ->limit($limit)
            ->orderBy($field, 'DESC')
            ->pluck($field, 'users.id')
            ->prepend("", "");
        }

        return $list;
    }

    public static function getAll($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = User::select('*')->orderBy('id', 'DESC')->get();
        }
        else
        {
            $list = User::select('*')->where(function($q) use ($user)
            {
                $q->where("users.r_auth", 0)->orWhere("users.r_auth", $user->id);
            })
            ->orderBy('id', 'DESC')
            ->get();
        }

        return $list;
    }

    public static function getAllByApi($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = User::select('*')->orderBy('id', 'DESC')->limit($limit);
        }
        else
        {
            $list = User::select('*')->where('r_auth', $user->id)->orderBy('id', 'DESC')->limit($limit);
        }

        return $list;
    }

    public function Profile(){
        return $this->belongsTo('App\Models\Profiles', 'profile_id');
    }
}
