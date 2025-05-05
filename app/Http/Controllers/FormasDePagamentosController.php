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

use \App\Models\FormasDePagamentos;
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

class FormasDePagamentosController extends Controller
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

        $formas_de_pagamentos = FormasDePagamentos::getAll(500);
        $formas_de_pagamentos_count  = FormasDePagamentos::getAllCount(); // # -

        $controller_model  = new FormasDePagamentos(); // # -

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo formas_de_pagamentos'));
        }

        return view('formas_de_pagamentos.index', [
            'exibe_filtros' => 1,
            'formas_de_pagamentos' => $formas_de_pagamentos,
            'formas_de_pagamentos_count' => $formas_de_pagamentos_count,
            'controller_model'  => $controller_model, // # -

        ]);
    }

    public function filter(Request $request)
    {
        try {

            $user = Auth::user();

            if ($user) {
                $r_auth = $user->id;
            }

            $store = $request->all();

            $store = array_filter($store);

            $formas_de_pagamentos = FormasDePagamentos::with((new FormasDePagamentos())->filter_with)->select((new FormasDePagamentos())->getTable().'.*');

            $controller_model  = new FormasDePagamentos(); // # -

            // # Verificação / Filtro GRID para Inclusão
            if(isset($store['grid_fil'])){
                foreach($store['grid_fil'] as $GF_K => $GF){
                    if(in_array($GF_K,['operador','between'])){ continue; }
                    $exp_gf_k = explode('__', $GF_K, 2);
                    // ! Reforço*
                    if(count($exp_gf_k) == 2){
                        $formas_de_pagamentos->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        if(count($GF)){
                            foreach($GF as $GF_C => $GF_V){
                                if(empty($GF_V)){ continue; }
                                if(isset($store['grid_fil']['operador'][$GF_K][$GF_C]) || isset($store['grid_fil']['between'][$GF_K][$GF_C])){
                                    if($store['grid_fil']['operador'][$GF_K][$GF_C] == 'contem'){
                                        $formas_de_pagamentos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                    }elseif($store['grid_fil']['operador'][$GF_K][$GF_C] == 'entre'){
                                        $formas_de_pagamentos->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                    }else {
                                        $formas_de_pagamentos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                    }
                                }else {
                                    if(is_numeric($GF_V)){
                                        $formas_de_pagamentos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                    }else {
                                        $formas_de_pagamentos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                    }
                                }
                            }
                        }
                    }

                }
            }
            // - #

            unset($store['grid_fil']);

            // # Versão Tradicional
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

                    if ($store[$key] === 'on') {
                        $store[$key] = 1;
                    }

                    if (array_key_exists($key, $operador)) {
                        if ($operador[$key] == 'contem') {
                            $formas_de_pagamentos->where((new FormasDePagamentos())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $formas_de_pagamentos->whereBetween((new FormasDePagamentos())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $formas_de_pagamentos->where((new FormasDePagamentos())->getTable().'.'.$key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $formas_de_pagamentos->where((new FormasDePagamentos())->getTable().'.'.$key, $store[$key]);
                        }
                        else
                        {
                            $formas_de_pagamentos->where((new FormasDePagamentos())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                        }
                    }
                }
            }
            else
            {
                back();
            }

            if (Permissions::permissaoModerador($user))
            {
                $formas_de_pagamentos = $formas_de_pagamentos->orderBy((new FormasDePagamentos())->getTable().'.'.'id', 'DESC')->groupBy((new FormasDePagamentos())->getTable().'.id')->limit(500)->get();
            }
            else
            {
                $formas_de_pagamentos = $formas_de_pagamentos->where(function($q) use ($r_auth) {
                    $q->where((new FormasDePagamentos())->getTable().'.'.'r_auth', $r_auth)
                    ->orWhere((new FormasDePagamentos())->getTable().'.'.'r_auth', 0);
                })->orderBy((new FormasDePagamentos())->getTable().'.'.'id', 'DESC')->groupBy((new FormasDePagamentos())->getTable().'.id')->limit(500)->get();
            }

            // # -
            $formas_de_pagamentos_count  = clone $formas_de_pagamentos;
            $formas_de_pagamentos_count  = $formas_de_pagamentos_count->count((new FormasDePagamentos())->getTable().'.id');

            return view('formas_de_pagamentos.index', [
                'exibe_filtros' => 1,
                'formas_de_pagamentos' => $formas_de_pagamentos,
                'formas_de_pagamentos_count' => $formas_de_pagamentos_count,
                'controller_model'  => $controller_model, // # -

            ]);

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao aplicar filtros: " . $e->getMessage());

        }

        return Redirect::to('/formas_de_pagamentos');
    }

    public function create()
    {

        // # Modal
        $R_modal = false;
        $R_url   = \Request::url();
        if(str_ends_with($R_url, '/create/modal')){ $R_modal = true; }
        // - #

        $user = Auth::user();

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de cadastro do módulo formas_de_pagamentos'));
        }

        return view((!$R_modal?'formas_de_pagamentos.add':'formas_de_pagamentos.add_modal'), [

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

            // # - Modal ( Create / Edit )
            $modalCreateEdit = false;
            if(isset($store['modal-create-edit'])){ $modalCreateEdit = true; }
            unset($store['modal-create-edit']);
            // - #

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

            $formas_de_pagamentos = new FormasDePagamentos();

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
                if(!$modalCreateEdit){
                    return back()->withInput()->with(array('errors' => $validator->errors()), 400);
                }else {
                    return response()->json([
                        'errors' => $validator->errors()
                    ], 400);
                }
            }

            $formas_de_pagamentos->r_auth = $r_auth;
            $formas_de_pagamentos->fill($store);
            $formas_de_pagamentos->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($formas_de_pagamentos, $relacionamento, 'FormasDePagamentos', 'formas_de_pagamentos');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($formas_de_pagamentos, $grids, 'formas_de_pagamentos');
            }

            try {

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' formas_de_pagamentos: cadastrou ID: ' . $formas_de_pagamentos->id));
            }

            DB::commit();

            if(!$modalCreateEdit){
                Session::flash('flash_success', "Cadastro realizado com sucesso!");
            }else {
                return response()->json([
                    'ok'       => true,
                    'id'       => $formas_de_pagamentos->id,
                    'success'  => [[0 => 'Cadastro realizado com sucesso!']]
                ], 200);
            }

        } catch (Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            if(!$modalCreateEdit){
                Session::flash('flash_error', "Erro ao realizar cadastro!: " . $e->getMessage());
            }else {
                return response()->json([
                    'errors'       => [[0 => 'Erro ao realizar cadastro!']]
                ], 400);
            }
        }

        if($redirect) {
            if(!$modalCreateEdit){
                return Redirect::to($redirect);
            }else {
                return response()->json([
                    'ok'       => true,
                    'redirect' => $redirect
                ], 200);
            }
        }

        if(!$modalCreateEdit){
            return Redirect::to('/formas_de_pagamentos');
        }else {
            return response()->json([
                'ok'       => true
            ], 200);
        }

    }

    public function copy($id)
    {
        $user = Auth::user();

        $formas_de_pagamentos = FormasDePagamentos::where('ID', $id)->first();

        if (env('IGNORE_COLUMNS')) {

            $hidden = env('IGNORE_COLUMNS');

            $formas_de_pagamentos = $formas_de_pagamentos->makeHidden(explode(',', $hidden));
        }

        $new = $formas_de_pagamentos->replicate();

        $new->push();

        Session::flash('flash_success', "Linha duplicada com sucesso!");

        return Redirect::to("/formas_de_pagamentos/$new->id/edit");
    }

    public function show($id)
    {
        $user = Auth::user();

        $formas_de_pagamentos = FormasDePagamentos::find($id);

        if (!$formas_de_pagamentos) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/formas_de_pagamentos');
        }

        if (!Permissions::permissaoModerador($user) && $formas_de_pagamentos->r_auth != 0 && $formas_de_pagamentos->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/formas_de_pagamentos');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou o ID #' . $id . ' do módulo formas_de_pagamentos'));
        }

        if (isset($formas_de_pagamentos->Template) && $formas_de_pagamentos->Template->template && 1) {

            $url = $this->templateRepository::parseTemplate(['formas_de_pagamentos' => $formas_de_pagamentos]);

            return \Redirect::intended($url);

        }
        else
        {
            return view('formas_de_pagamentos.show', [
                'formas_de_pagamentos' => $formas_de_pagamentos,

            ]);
        }
    }

    public function modal($id)
    {
        $user = Auth::user();

        $formas_de_pagamentos = FormasDePagamentos::find($id);

        return view('formas_de_pagamentos.modal', [
            'formas_de_pagamentos' => $formas_de_pagamentos,

        ]);
    }

    public function ajax($value, Request $request)
    {
        $value          = str_replace('__H2F__', '/', $value);
        $subForm        = $request->get('subForm');

        $subForm2       = $request->get('subForm2');
        $subForm2_value = $request->get('subForm2_value');

        $subForm3       = $request->get('subForm3');
        $subForm3_value = $request->get('subForm3_value');

        $user     = Auth::user();

        if ($subForm)
        {
            if (Permissions::permissaoModerador($user))
            {
                $formas_de_pagamentos = FormasDePagamentos::where($subForm, $value);
                if(!empty($subForm2) and !empty($subForm2_value)){
                    $formas_de_pagamentos->where($subForm2,$subForm2_value);
                }
                if(!empty($subForm3) and !empty($subForm3_value)){
                    $formas_de_pagamentos->where($subForm3,$subForm3_value);
                }
                $formas_de_pagamentos = $formas_de_pagamentos->get();
            }
            else
            {
               $formas_de_pagamentos = FormasDePagamentos::where($subForm, $value)->where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
               });
               if(!empty($subForm2) and !empty($subForm2_value)){
                   $formas_de_pagamentos->where($subForm2,$subForm2_value);
               }
               if(!empty($subForm3) and !empty($subForm3_value)){
                   $formas_de_pagamentos->where($subForm3,$subForm3_value);
               }
               $formas_de_pagamentos = $formas_de_pagamentos->get();
            }
        }
        else
        {
            if (Permissions::permissaoModerador($user))
            {
                $formas_de_pagamentos = FormasDePagamentos::find($value);
            }
            else
            {
               $formas_de_pagamentos = FormasDePagamentos::where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->find($value);
            }
        }

        return Response::json($formas_de_pagamentos);
    }

    // # -
    public function list(){
        $formas_de_pagamentos = FormasDePagamentos::list(10000, "forma_de_pagamento");
        return Response::json($formas_de_pagamentos);
    }

    public function pdf($id)
    {
        $user = Auth::user();

        $formas_de_pagamentos = FormasDePagamentos::find($id);

        return view('formas_de_pagamentos.pdf', [
            'formas_de_pagamentos' => $formas_de_pagamentos,

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
                    $formas_de_pagamentos = FormasDePagamentos::find($value['id']);
                }
                else
                {
                    $formas_de_pagamentos = new FormasDePagamentos();
                }

                $formas_de_pagamentos->fill($value);

                $formas_de_pagamentos->save();

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

        return Redirect::to('/formas_de_pagamentos');
    }

    public function edit($id)
    {
        $user = Auth::user();

        $formas_de_pagamentos = FormasDePagamentos::find($id);

        if (!$formas_de_pagamentos) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/formas_de_pagamentos');
        }

        if (!Permissions::permissaoModerador($user) && $formas_de_pagamentos->r_auth != 0 && $formas_de_pagamentos->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/formas_de_pagamentos');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo formas_de_pagamentos'));
        }

        return view('formas_de_pagamentos.edit', [
            'formas_de_pagamentos' => $formas_de_pagamentos,

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

            $store = $request->all();

            // # - Mudança Item Quadro Kanban
            if(isset($store['update_kanban'])){ return $this->kanban_update($request); }

            DB::beginTransaction(); // # -

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

            $formas_de_pagamentos = FormasDePagamentos::find($store['id']);

            if (!$formas_de_pagamentos) {

                \Session::flash('flash_error', 'Registro não encontrado!');

                return Redirect::to('/formas_de_pagamentos');
            }

            if (!Permissions::permissaoModerador($user) && $formas_de_pagamentos->r_auth != 0 && $formas_de_pagamentos->r_auth != $user->id)
            {
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/formas_de_pagamentos');
            }

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

            $formas_de_pagamentos->r_auth = $r_auth;

            $formas_de_pagamentos->fill($store);

            $formas_de_pagamentos->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($formas_de_pagamentos, $relacionamento, 'FormasDePagamentos', 'formas_de_pagamentos');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($formas_de_pagamentos, $grids, 'formas_de_pagamentos');
            }

            try {

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            // # -
            if(!\Request::get('modal-close')){
                Session::flash('flash_success', "Registro atualizado com sucesso!");
            }

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $formas_de_pagamentos->id . ' do módulo formas_de_pagamentos'));
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

        // # -
        if(\Request::get('modal-close')){ return view('modal-close'); }

        return Redirect::to('/formas_de_pagamentos');
    }

    public function kanban_update(Request $request)
    {
        try {

            $user       = Auth::user();
            $r_auth     = NULL;

            if($user) {
                $r_auth = $user->id;
            }

            DB::beginTransaction();

            $store          = $request->all();

            // :: Valida os campos necessários
            if(!isset($store['id']) || !isset($store['item_column']) /*|| !isset($store['item_target_id'])*/){
                return response()->json(['error'=>'Ocorreu um erro. Tente Novamente!*'],400);
            }

            $formas_de_pagamentos         = FormasDePagamentos::find($store['id']);

            if(!$formas_de_pagamentos){
                return response()->json(['error'=>'Registro não encontrado!'],400);
            }

            if(!Permissions::permissaoModerador($user) && $formas_de_pagamentos->r_auth != 0 && $formas_de_pagamentos->r_auth != $user->id){
                return response()->json(['error'=>'Você não tem permissão para executar esta ação!'],400);
            }

            $formas_de_pagamentos->update([
                $store['item_column']   => $store['item_target_id'],
                'r_auth'                => $r_auth,
            ]);

            DB::commit();

            try {

            }catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $formas_de_pagamentos->id . ' do módulo formas_de_pagamentos'));
            }

            DB::commit();

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            return response()->json(['error'=>'Ocorreu um erro. Tente Novamente!**'],400);
        }

        return response()->json(['ok'=>'Registro atualizado com sucesso!'],200);

    }

    public function destroy($id)
    {
        $formas_de_pagamentos = FormasDePagamentos::find($id);

        try {

        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        return $this->controllerRepository::destroy(new FormasDePagamentos(), $id, 'formas_de_pagamentos');
    }

}
