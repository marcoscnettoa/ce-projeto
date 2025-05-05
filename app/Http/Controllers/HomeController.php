<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use \App\Models\Permissions;
use \App\Models\Indicators;
use \App\Models\Events;

class HomeController extends Controller
{
    public $calendar = 0;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->calendar = env('MODULO_CALENDARIO', 0);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user)) {
            $indicators = Indicators::orderBy('id', 'DESC')->limit(1000)->get();
        } else {
           
            $indicators = Indicators::where(function($q) use ($user) {
                $q->where('r_auth', $user->id)
                ->orWhere('r_auth', 0);
            })->orderBy('id', 'DESC')->limit(1000)->get();
        }

        $calendar = NULL;

        if ($this->calendar) {
           
            $events = [];

            if (Permissions::permissaoModerador($user)) {
                $data = Events::limit(100)->get();
            } else {
               
               $data = Events::where(function($q) use ($user) {
                    $q->where('r_auth', $user->id)
                    ->orWhere('r_auth', 0)
                    ->orWhereNull('r_auth');
                })->orderBy('id', 'DESC')->limit(100)->get();
            }
            
            if($data->count())
            {
                foreach ($data as $key => $value) 
                {
                    $events[] = \Calendar::event(
                        $value->title,
                        $value->is_all_day,
                        new \DateTime($value->start_date),
                        new \DateTime($value->end_date),
                        null,
                        []
                    );
                }
            }

            $calendar = \Calendar::addEvents($events)->setOptions(['locale' => 'pt-br']);
        }

        return view('home',[
            'indicadores' => $indicators,
            'calendar' => $calendar,
        ]);
    }

    public function swagger()
    {
        return \File::get(public_path() . '/doc_swagger/index.html');
    }
}
