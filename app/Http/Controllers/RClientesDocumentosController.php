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

use \App\Models\RClientesDocumentos;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;
use setasign\Fpdi\Fpdi;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\Filesystem\Filesystem;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;

use \App\Models\Documentos;

use App\Repositories\UploadRepository;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;

class RClientesDocumentosController extends Controller
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

        $r_clientes_documentos = RClientesDocumentos::getAll(500);

        $controller_model  = new RClientesDocumentos(); // # -

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo r_clientes_documentos'));
        }

        return view('r_clientes_documentos.index', [
            'exibe_filtros' => 0,
            'r_clientes_documentos' => $r_clientes_documentos,
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

            $r_clientes_documentos = RClientesDocumentos::with((new RClientesDocumentos())->filter_with)->select((new RClientesDocumentos())->getTable().'.*');

            $controller_model  = new RClientesDocumentos(); // # -

            // # Verificação / Filtro GRID para Inclusão
            if(isset($store['grid_fil'])){
                foreach($store['grid_fil'] as $GF_K => $GF){
                    if(in_array($GF_K,['operador','between'])){ continue; }
                    $exp_gf_k = explode('__', $GF_K, 2);
                    // ! Reforço*
                    if(count($exp_gf_k) == 2){
                        $r_clientes_documentos->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        if(count($GF)){
                            foreach($GF as $GF_C => $GF_V){
                                if(empty($GF_V)){ continue; }
                                if(isset($store['grid_fil']['operador'][$GF_K][$GF_C]) || isset($store['grid_fil']['between'][$GF_K][$GF_C])){
                                    if($store['grid_fil']['operador'][$GF_K][$GF_C] == 'contem'){
                                        $r_clientes_documentos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                    }elseif($store['grid_fil']['operador'][$GF_K][$GF_C] == 'entre'){
                                        $r_clientes_documentos->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                    }else {
                                        $r_clientes_documentos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                    }
                                }else {
                                    if(is_numeric($GF_V)){
                                        $r_clientes_documentos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                    }else {
                                        $r_clientes_documentos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
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
                            $r_clientes_documentos->where((new RClientesDocumentos())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $r_clientes_documentos->whereBetween((new RClientesDocumentos())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $r_clientes_documentos->where((new RClientesDocumentos())->getTable().'.'.$key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $r_clientes_documentos->where((new RClientesDocumentos())->getTable().'.'.$key, $store[$key]);
                        }
                        else
                        {
                            $r_clientes_documentos->where((new RClientesDocumentos())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
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
                $r_clientes_documentos = $r_clientes_documentos->orderBy((new RClientesDocumentos())->getTable().'.'.'id', 'DESC')->groupBy((new RClientesDocumentos())->getTable().'.id')->limit(500)->get();
            }
            else
            {
                $r_clientes_documentos = $r_clientes_documentos->where(function($q) use ($r_auth) {
                    $q->where((new RClientesDocumentos())->getTable().'.'.'r_auth', $r_auth)
                    ->orWhere((new RClientesDocumentos())->getTable().'.'.'r_auth', 0);
                })->orderBy((new RClientesDocumentos())->getTable().'.'.'id', 'DESC')->groupBy((new RClientesDocumentos())->getTable().'.id')->limit(500)->get();
            }

            // # -
            $r_clientes_documentos_count  = clone $r_clientes_documentos;
            $r_clientes_documentos_count  = $r_clientes_documentos_count->count((new RClientesDocumentos())->getTable().'.id');

            return view('r_clientes_documentos.index', [
                'exibe_filtros' => 1,
                'r_clientes_documentos' => $r_clientes_documentos,
                'r_clientes_documentos_count' => $r_clientes_documentos_count,
                'controller_model'  => $controller_model, // # -

            ]);

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao aplicar filtros: " . $e->getMessage());

        }

        return Redirect::to('/r_clientes_documentos');
    }

    public function create()
    {
        $user = Auth::user();

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de cadastro do módulo r_clientes_documentos'));
        }

        return view('r_clientes_documentos.add', [

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

            $r_clientes_documentos = new RClientesDocumentos();

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

            $r_clientes_documentos->r_auth = $r_auth;

            $r_clientes_documentos->fill($store);

            $r_clientes_documentos->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($r_clientes_documentos, $relacionamento, 'RClientesDocumentos', 'r_clientes_documentos');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($r_clientes_documentos, $grids, 'r_clientes_documentos');
            }

            try {

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Session::flash('flash_success', "Cadastro realizado com sucesso");

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' r_clientes_documentos: cadastrou ID: ' . $r_clientes_documentos->id));
            }

            DB::commit();

        } catch (Exception $e) {

            Log::info($e->getMessage());

            Session::flash('flash_error', "Erro ao realizar cadastro: " . $e->getMessage());

            DB::rollback();
        }

        if ($redirect) {
            return Redirect::to($redirect);
        }

        return Redirect::to('/r_clientes_documentos');

    }

    public function copy($id)
    {
        $user = Auth::user();

        $r_clientes_documentos = RClientesDocumentos::where('ID', $id)->first();

        if (env('IGNORE_COLUMNS')) {

            $hidden = env('IGNORE_COLUMNS');

            $r_clientes_documentos = $r_clientes_documentos->makeHidden(explode(',', $hidden));
        }

        $new = $r_clientes_documentos->replicate();

        $new->push();

        Session::flash('flash_success', "Linha duplicada com sucesso!");

        return Redirect::to("/r_clientes_documentos/$new->id/edit");
    }

    public function show($id)
    {
        $user = Auth::user();

        $r_clientes_documentos = RClientesDocumentos::find($id);

        if (!$r_clientes_documentos) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/r_clientes_documentos');
        }

        if (!Permissions::permissaoModerador($user) && $r_clientes_documentos->r_auth != 0 && $r_clientes_documentos->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/r_clientes_documentos');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou o ID #' . $id . ' do módulo r_clientes_documentos'));
        }

        if (isset($r_clientes_documentos->Template) && $r_clientes_documentos->Template->template && 1) {

            $url = $this->templateRepository::parseTemplate(['r_clientes_documentos' => $r_clientes_documentos]);

            return \Redirect::intended($url);

        }
        else
        {
            return view('r_clientes_documentos.show', [
                'r_clientes_documentos' => $r_clientes_documentos,

            ]);
        }
    }

    public function modal($id)
    {
        $user = Auth::user();

        $r_clientes_documentos = RClientesDocumentos::find($id);

        return view('r_clientes_documentos.modal', [
            'r_clientes_documentos' => $r_clientes_documentos,

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
                $r_clientes_documentos = RClientesDocumentos::where($subForm, $value);
                if(!empty($subForm2) and !empty($subForm2_value)){
                    $r_clientes_documentos->where($subForm2,$subForm2_value);
                }
                if(!empty($subForm3) and !empty($subForm3_value)){
                    $r_clientes_documentos->where($subForm3,$subForm3_value);
                }
                $r_clientes_documentos = $r_clientes_documentos->get();
            }
            else
            {
               $r_clientes_documentos = RClientesDocumentos::where($subForm, $value)->where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
               });
               if(!empty($subForm2) and !empty($subForm2_value)){
                   $r_clientes_documentos->where($subForm2,$subForm2_value);
               }
               if(!empty($subForm3) and !empty($subForm3_value)){
                   $r_clientes_documentos->where($subForm3,$subForm3_value);
               }
               $r_clientes_documentos = $r_clientes_documentos->get();
            }
        }
        else
        {
            if (Permissions::permissaoModerador($user))
            {
                $r_clientes_documentos = RClientesDocumentos::find($value);
            }
            else
            {
               $r_clientes_documentos = RClientesDocumentos::where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->find($value);
            }
        }

        return Response::json($r_clientes_documentos);
    }

    public function pdf($id)
    {
        $user = Auth::user();

        $r_clientes_documentos = RClientesDocumentos::find($id);

        return view('r_clientes_documentos.pdf', [
            'r_clientes_documentos' => $r_clientes_documentos,

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
                    $r_clientes_documentos = RClientesDocumentos::find($value['id']);
                }
                else
                {
                    $r_clientes_documentos = new RClientesDocumentos();
                }

                $r_clientes_documentos->fill($value);

                $r_clientes_documentos->save();

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

        return Redirect::to('/r_clientes_documentos');
    }

    public function edit($id)
    {
        $user = Auth::user();

        $r_clientes_documentos = RClientesDocumentos::find($id);

        if (!$r_clientes_documentos) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/r_clientes_documentos');
        }

        if (!Permissions::permissaoModerador($user) && $r_clientes_documentos->r_auth != 0 && $r_clientes_documentos->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/r_clientes_documentos');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo r_clientes_documentos'));
        }

        return view('r_clientes_documentos.edit', [
            'r_clientes_documentos' => $r_clientes_documentos,

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

            $r_clientes_documentos = RClientesDocumentos::find($store['id']);

            if (!$r_clientes_documentos) {

                \Session::flash('flash_error', 'Registro não encontrado!');

                return Redirect::to('/r_clientes_documentos');
            }

            if (!Permissions::permissaoModerador($user) && $r_clientes_documentos->r_auth != 0 && $r_clientes_documentos->r_auth != $user->id)
            {
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/r_clientes_documentos');
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

            $r_clientes_documentos->r_auth = $r_auth;

            $r_clientes_documentos->fill($store);

            $r_clientes_documentos->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($r_clientes_documentos, $relacionamento, 'RClientesDocumentos', 'r_clientes_documentos');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($r_clientes_documentos, $grids, 'r_clientes_documentos');
            }

            try {

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            // # -
            if(!\Request::get('modal-close')){
                Session::flash('flash_success', "Registro atualizado com sucesso");
            }

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $r_clientes_documentos->id . ' do módulo r_clientes_documentos'));
            }

            DB::commit();

        } catch (Exception $e) {

            Log::info($e->getMessage());

            Session::flash('flash_error', "Erro ao atualizar registro: " . $e->getMessage());

            DB::rollback();
        }

        if ($redirect) {
            return Redirect::to($redirect);
        }

        // # -
        if(\Request::get('modal-close')){ return view('modal-close'); }

        return Redirect::to('/r_clientes_documentos');
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

            $r_clientes_documentos         = RClientesDocumentos::find($store['id']);

            if(!$r_clientes_documentos){
                return response()->json(['error'=>'Registro não encontrado!'],400);
            }

            if(!Permissions::permissaoModerador($user) && $r_clientes_documentos->r_auth != 0 && $r_clientes_documentos->r_auth != $user->id){
                return response()->json(['error'=>'Você não tem permissão para executar esta ação!'],400);
            }

            $r_clientes_documentos->update([
                $store['item_column']   => $store['item_target_id'],
                'r_auth'                => $r_auth,
            ]);

            DB::commit();

            try {

            }catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $r_clientes_documentos->id . ' do módulo r_clientes_documentos'));
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
        $r_clientes_documentos = RClientesDocumentos::find($id);

        try {

        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        return $this->controllerRepository::destroy(new RClientesDocumentos(), $id, 'r_clientes_documentos');
    }

}
