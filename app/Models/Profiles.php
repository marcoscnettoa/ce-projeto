<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Profiles extends Model
{
   	protected $table = 'r_profiles';
   	protected $primaryKey = 'id';

    protected $guarded = ['_token'];

    static public function returnDefault()
    {
    	$profile = self::where('default', 1)->first();

        if ($profile) {
        	return $profile->id;
        }
        return null;
    }

    public static function getAllByApi($limit = 100)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user))
        {
            $list = Profiles::select('*')->orderBy('id', 'DESC')->limit($limit);
        }
        else
        {
            $list = Profiles::select('*')->where('r_auth', $user->id)->orderBy('id', 'DESC')->limit($limit);
        }

        return $list;
    }
}
