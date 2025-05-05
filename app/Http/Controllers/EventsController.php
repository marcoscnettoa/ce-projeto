<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Redirect;
use Session;
use Validator;
use Exception;
use Response;
use DB;
use PDF;
use Log;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use \App\Models\Events;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;
use setasign\Fpdi\Fpdi;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\Filesystem\Filesystem;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;

use App\Repositories\UploadRepository;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;

class EventsController extends Controller
{
    public function __construct(
        Client $client,
        UploadRepository $uploadRepository,
        ControllerRepository $controllerRepository,
        TemplateRepository $templateRepository
    ) {
        $this->client   = $client;
        $this->upload   = $controllerRepository->upload;
        $this->maxSize  = $controllerRepository->maxSize;

        $this->uploadRepository     = $uploadRepository;
        $this->controllerRepository = $controllerRepository;
        $this->templateRepository = $templateRepository;
    }

    public function index()
    {
        $user = Auth::user();

        $events = Events::getAll(500);

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo events'));
        }

        return view('events.index', [
            'exibe_filtros' => 0,
            'events' => $events,

        ]);
    }

    public function filter(Request $request)
    {
        try {

            $user = Auth::user();

            $store = $request->all();

            $store = array_filter($store);

            $events = Events::select('*');

            if(!empty($store)){

                $operador = [];

                $between = [];

                if (isset($store['operador']) && !empty($store['operador'])) {
                    $operador = $store['operador'];
                    unset($store['operador']);
                }

                if (isset($store['between']) && !empty($store['between'])) {
                    $between = $store['between'];
                    unset($store['between']);
                }

                if (isset($store['_token'])) {
                    unset($store['_token']);
                }

                foreach ($store as $key => $value) {

                    if (array_key_exists($key, $operador)) {
                        if ($operador[$key] == 'contem') {
                            $events->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $events->whereBetween($key, [$value, $between[$key]]);
                        }
                        else
                        {
                            $events->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $events->where($key, $store[$key]);
                        }
                        else
                        {
                            $events->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                    }
                }
            }
            else
            {
                back();
            }

            $events = $events->orderBy('id', 'DESC')->limit(500)->get();

            return view('events.index', [
                'exibe_filtros' => 1,
                'events' => $events,

            ]);

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao aplicar filtros: " . $e->getMessage());

        }

        return Redirect::to('/events');
    }

    public function create()
    {
        $user = Auth::user();

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de cadastro do módulo events'));
        }

        return view('events.add', [

        ]);
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    public function store(Request $request)
    {
        try {

            DB::beginTransaction();

            $user = Auth::user();

            $r_auth = NULL;

            $grids = NULL;

            $redirect = false;

            if ($user) {
                $r_auth = $user->id;
            }

            $store = $request->all();

            if (isset($store['redirect']) && $store['redirect']) {
                if (isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) {
                    $redirect = str_replace('redirect=', '', $_SERVER["QUERY_STRING"]);
                }
            }

            if (isset($store['r_auth'])) {
                $r_auth = $store['r_auth'];
            }

            if (isset($store['grid'])) {
                $grids = $store['grid'];
                unset($store['grid']);
            }

            $events = new Events();

            $store["is_all_day"] = (isset($store["is_all_day"]) && $store["is_all_day"] == "on");

            $store = array_filter($store);

            $relacionamento = array();

            $list = $this->uploadRepository::parseUpload($this->upload, $this->maxSize, $store, $request);

            if (!empty($list)) {

                if (isset($list['relacionamento'])) {
                    $relacionamento = $list['relacionamento'];
                }

                if (isset($list['store'])) {
                    $store = $list['store'];
                }
            }

            $validator = $this->validator($store);

            if ($validator->fails()) {
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            $events->r_auth = $r_auth;

            $events->fill($store);

            $events->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($events, $relacionamento, 'Events', 'events');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($events, $grids, 'events');
            }

            try {

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Session::flash('flash_success', "Evento cadastrado com sucesso!");

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' events: cadastrou ID: ' . $events->id));
            }

            DB::commit();

        } catch (Exception $e) {

            Log::info($e->getMessage());

            Session::flash('flash_error', "Erro ao realizar cadastro!: " . $e->getMessage());

            DB::rollback();
        }

        if ($redirect) {
            return Redirect::to($redirect);
        }

        return Redirect::to('/events');

    }

    public function copy($id)
    {
        $user = Auth::user();

        $events = Events::where('ID', $id)->first();

        if (env('IGNORE_COLUMNS')) {

            $hidden = env('IGNORE_COLUMNS');

            $events = $events->makeHidden(explode(',', $hidden));
        }

        $new = $events->replicate();

        $new->push();

        Session::flash('flash_success', "Linha duplicada com sucesso!");

        return Redirect::to("/events/$new->id/edit");
    }

    public function show($id)
    {
        $user = Auth::user();

        $events = Events::find($id);

        if (!$events) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/events');
        }

        if (!Permissions::permissaoModerador($user) && $events->r_auth != 0 && $events->r_auth != $user->id) 
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/events');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou o ID #' . $id . ' do módulo events'));
        }

        if (isset($events->Template) && $events->Template->template && 1) {

            $url = $this->templateRepository::parseTemplate(['events' => $events]);

            return \Redirect::intended($url);

        }
        else
        {
            return view('events.show', [
                'events' => $events,

            ]);
        }
    }

    public function modal($id)
    {
        $user = Auth::user();

        $events = Events::find($id);

        return view('events.modal', [
            'events' => $events,

        ]);
    }

    public function ajax($value, Request $request)
    {
        $value = str_replace('__H2F__', '/', $value);

        $subForm = $request->get('subForm');

        $user = Auth::user();

        if ($subForm)
        {
            if (Permissions::permissaoModerador($user))
            {
                $events = Events::where($subForm, $value)->get();
            }
            else
            {
               $events = Events::where($subForm, $value)->where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->get();
            }
        }
        else
        {
            $events = Events::where(function($q) use ($user) {
                $q->whereNull('r_auth')
                ->orWhere('r_auth', $user->id)
                ->orWhere('r_auth', 0);
            })->find($value);
        }

        return Response::json($events);
    }

    public function pdf($id)
    {
        $user = Auth::user();

        $events = Events::find($id);

        return view('events.pdf', [
            'events' => $events,

        ]);
    }

    public function importar(Request $request)
    {
        $path = $request->file('csv')->getRealPath();

        $filename = uniqid().".".strtolower($request->file('csv')->getClientOriginalExtension());

        $request->file('csv')->move(public_path("images"), $filename);

        $collection = (new FastExcel)->configureCsv(';', '#', '\n', 'gbk')->import('images/' . $filename);

        $errors = [];

        foreach ($collection as $key => $value) {

            try {

                if (isset($value['id']) && $value['id']) {
                    $events = Events::find($value['id']);
                }
                else
                {
                    $events = new Events();
                }

                $events->fill($value);

                $events->save();

            } catch (Exception $e) {

                Log::info($e->getMessage());

                $value['error'] = $e->getMessage();

                $errors[] = $value;

            }
        }

        if (!empty($errors)) {

            Session::flash('flash_error', "Erro ao importar " . count($errors) . ' linhas! <a href="/errors.xlsx">Clique aqui para baixar o arquivo</a>');

            (new FastExcel($errors))->export('errors.xlsx');
        }

        return Redirect::to('/events');
    }

    public function edit($id)
    {
        $user = Auth::user();

        $events = Events::find($id);

        if (!$events) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/events');
        }

        if (!Permissions::permissaoModerador($user) && $events->r_auth != $user->id) 
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/events');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo events'));
        }

        return view('events.edit', [
            'events' => $events,

        ]);
    }

    public function update(Request $request)
    {
        try {

            $user = Auth::user();

            $r_auth = NULL;

            $grids = NULL;

            $redirect = false;

            if ($user) {
                $r_auth = $user->id;
            }

            DB::beginTransaction();

            $store = $request->all();

            if (isset($store['redirect']) && $store['redirect']) {
                if (isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) {
                    $redirect = str_replace('redirect=', '', $_SERVER["QUERY_STRING"]);
                }
            }

            if (isset($store['r_auth'])) {
                $r_auth = $store['r_auth'];
            }

            if (isset($store['grid'])) {
                $grids = $store['grid'];
                unset($store['grid']);
            }

            $events = Events::find($store['id']);

            if (!$events) {

                \Session::flash('flash_error', 'Evento não encontrado!');

                return Redirect::to('/events');
            }

            if (!Permissions::permissaoModerador($user) && $events->r_auth != $user->id) 
            {
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/events');
            }

            $store["is_all_day"] = (isset($store["is_all_day"]) && $store["is_all_day"] == "on");

            $relacionamento = array();

            $list = $this->uploadRepository::parseUpload($this->upload, $this->maxSize, $store, $request);

            if (!empty($list)) {

                if (isset($list['relacionamento'])) {
                    $relacionamento = $list['relacionamento'];
                }

                if (isset($list['store'])) {
                    $store = $list['store'];
                }
            }

            $validator = $this->validator($store, $store['id']);

            if ($validator->fails()) {
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            $events->r_auth = $r_auth;

            $events->fill($store);

            $events->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($events, $relacionamento, 'Events', 'events');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($events, $grids, 'events');
            }

            try {

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Session::flash('flash_success', "Evento atualizado com sucesso!");

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $events->id . ' do módulo events'));
            }

            DB::commit();

        } catch (Exception $e) {

            Log::info($e->getMessage());

            Session::flash('flash_error', "Erro ao atualizar registro!: " . $e->getMessage());

            DB::rollback();
        }

        if ($redirect) {
            return Redirect::to($redirect);
        }

        return Redirect::to('/events');
    }

    public function destroy($id)
    {
        $events = Events::find($id);

        try {

        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        return $this->controllerRepository::destroy(new Events(), $id, 'events');
    }
}