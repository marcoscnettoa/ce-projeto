<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Redirect;
use Route;
use Session;

use \App\Models\User;
use \App\Models\Logs;
use \App\Models\Permissions;
use \App\Models\Profiles;

class PermissionsController extends Controller
{
    public function user($id)
    {  
		$controllers = [];

		foreach (Route::getRoutes()->getRoutes() as $route)
		{
		    $action = $route->getAction();

		    if (array_key_exists('controller', $action))
		    {	
                if (strpos($action['controller'], '@edit') == false && strpos($action['controller'], '@create') == false && strpos($action['controller'], 'Auth') === false && strpos($action['controller'], 'Api') === false && $action['controller'] != 'App\Http\Controllers\HomeController@index') {
                    $controllers[] = $action['controller'];
			    }
		    }
		}

        sort($controllers);

        $perms = Permissions::where('user_id', $id)->pluck('role', 'id');

        $user = User::find($id);

        if (!$user) {
            Session::flash('flash_error', "Usuário não encontrado!");
            return Redirect::to('/users');
        }

        Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' visualizou a lista de permissões'));

        return view('permissions.index', [
            'lista' => $controllers,
            'perms' => $perms,
        	'user_id' => $id,
            'profile_id' => '',
        ]);
    }

    public function profile($id)
    {  
        $controllers = [];

        foreach (Route::getRoutes()->getRoutes() as $route)
        {
            $action = $route->getAction();

            if (array_key_exists('controller', $action))
            {
                if (strpos($action['controller'], '@edit') == false && strpos($action['controller'], '@create') == false && strpos($action['controller'], 'Auth') === false && strpos($action['controller'], 'Api') === false && $action['controller'] != 'App\Http\Controllers\HomeController@index') {
                    $controllers[] = $action['controller'];
                }
            }
        }

        sort($controllers);

        $perms = Permissions::where('profile_id', $id)->pluck('role', 'id');

        $profile = Profiles::find($id);

        if (!$profile) {
            Session::flash('flash_error', "Perfil não encontrado!");
            return Redirect::to('/profiles');
        }

        Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' visualizou a lista de permissões'));

        return view('permissions.index', [
            'lista' => $controllers,
            'perms' => $perms,
            'user_id' => '',
            'profile_id' => $id
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if (isset($data['profile_id']) && $data['profile_id'])
        {
            Permissions::where('profile_id', $request->get('profile_id'))->delete();
        }
        else
        {
            Permissions::where('user_id', $request->get('user_id'))->delete();
        }

        $permissions = $request->get('permissions');

        if (!empty($permissions)) {
         
            foreach ($permissions as $key => $value) {

                if (strpos($value, '@store') == true) {
                    $permissions[] = str_replace('store', 'create', $value);
                }

                if (strpos($value, '@update') == true) {
                    $permissions[] = str_replace('update', 'edit', $value);
                }
            }

    	    foreach ($permissions as $key => $value) {
                
                $permission = new Permissions();
                
                $permission->role = $value;

                if (isset($data['profile_id']) && $data['profile_id']) 
                {
                    $permission->profile_id = $request->get('profile_id');
                }
                else
                {
                    $permission->user_id = $request->get('user_id');
                }
                
            	$permission->r_auth = Auth::user()->id;

            	$permission->save();
            }

            Session::flash('flash_success', "Permissões cadastradas com sucesso!");

	    }

        if (isset($data['profile_id']) && $data['profile_id']) 
        {
            Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' atualizou permissões do perfil ID:' . $data['profile_id']));
            return Redirect::to('/profiles');
        } 
        elseif (isset($data['user_id']) && $data['user_id'])
        {
            Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' atualizou permissões do usuário ID:' . $data['user_id']));
            return Redirect::to('/users');
        }
        else
        {
            return Redirect::to('/');
        }
    }
}
