<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use \App\Models\Logs;
use \App\Models\Permissions;

class LogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        if (Permissions::permissaoModerador($user)) {
            $logs = Logs::orderBy('id', 'DESC')->limit(1000)->get();
        } else {
            $logs = Logs::where('r_auth', $user->id)->orderBy('id', 'DESC')->limit(1000)->get();
        }

        return view('logs.index', ['logs' => $logs]);
    }
}
