<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Auth;
use Route;
use Session;
use Redirect;
use Exception;

use \App\Models\Permissions;
use \App\Models\User;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function callAction($method, $parameters)
    {
        if (!Permissions::permissaoUsuario(Auth::user(), Route::getCurrentRoute()->getActionName()) && !in_array($method, ['showLoginForm', 'login', 'logout', 'showLinkRequestForm', 'sendResetLinkEmail', 'showRegistrationForm', 'register', 'showResetForm', 'reset']) && Route::getCurrentRoute()->getActionName() != 'App\Http\Controllers\HomeController@index') {

            Session::flash('flash_error', 'Você não tem permissão para executar asa esta ação!');

            return Redirect::to('/');
        }

        return parent::callAction($method, $parameters);
    }
}
