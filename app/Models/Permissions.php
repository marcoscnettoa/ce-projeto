<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Permissions extends Model
{
   	protected $table = 'r_permissions';
   	protected $primaryKey = 'id';

    protected $guarded = ['_token'];

    static public function permissaoUsuario($user, $role)
    {   
        if (!isset($user->perfil)) {
            return false;
        }

        if ($user->perfil->administrator) {
            return true;
        }
        
        $permissao = self::where('role', $role)
            ->where('user_id', Auth::user()->id)
            ->whereNotNull('user_id')
            ->count();

        if ($permissao) {
            return true;
        }

        $permissao = self::where('role', $role)
            ->where('profile_id', $user->perfil->id)
            ->whereNotNull('profile_id')
            ->count();

        if ($permissao) {
            return true;
        }

        return false;
    }

    static public function permissaoModerador($user)
    {
        if (isset($user->perfil) && ($user->perfil->moderator || $user->perfil->administrator)) {
        	return true;
        }
        return false;
    }

    static public function profileAdmin($user)
    {
        if (isset($user->perfil) && $user->perfil->administrator) {
            return true;
        }
        return false;
    }
}
