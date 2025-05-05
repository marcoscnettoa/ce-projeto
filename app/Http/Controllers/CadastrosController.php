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

use \App\Models\Cadastros;
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

class CadastrosController extends Controller
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

        $cadastros = Cadastros::getAll(500);
        $cadastros_count  = Cadastros::getAllCount(); // # -

        $controller_model  = new Cadastros(); // # -

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo cadastros'));
        }

        return view('cadastros.index', [
            'exibe_filtros' => 0,
            'cadastros' => $cadastros,
            'cadastros_count' => $cadastros_count,
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

            $cadastros = Cadastros::with((new Cadastros())->filter_with)->select((new Cadastros())->getTable().'.*');

            $controller_model  = new Cadastros(); // # -

            // # Verificação / Filtro GRID para Inclusão
            if(isset($store['grid_fil'])){
                foreach($store['grid_fil'] as $GF_K => $GF){
                    if(in_array($GF_K,['operador','between'])){ continue; }
                    $exp_gf_k = explode('__', $GF_K, 2);
                    // ! Reforço*
                    if(count($exp_gf_k) == 2){
                        $cadastros->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        if(count($GF)){
                            foreach($GF as $GF_C => $GF_V){
                                if(empty($GF_V)){ continue; }
                                if(isset($store['grid_fil']['operador'][$GF_K][$GF_C]) || isset($store['grid_fil']['between'][$GF_K][$GF_C])){
                                    if($store['grid_fil']['operador'][$GF_K][$GF_C] == 'contem'){
                                        $cadastros->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                    }elseif($store['grid_fil']['operador'][$GF_K][$GF_C] == 'entre'){
                                        $cadastros->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                    }else {
                                        $cadastros->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                    }
                                }else {
                                    if(is_numeric($GF_V)){
                                        $cadastros->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                    }else {
                                        $cadastros->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
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
                            $cadastros->where((new Cadastros())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $cadastros->whereBetween((new Cadastros())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $cadastros->where((new Cadastros())->getTable().'.'.$key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $cadastros->where((new Cadastros())->getTable().'.'.$key, $store[$key]);
                        }
                        else
                        {
                            $cadastros->where((new Cadastros())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
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
                $cadastros = $cadastros->orderBy((new Cadastros())->getTable().'.'.'id', 'DESC')->groupBy((new Cadastros())->getTable().'.id')->limit(500)->get();
            }
            else
            {
                $cadastros = $cadastros->where(function($q) use ($r_auth) {
                    $q->where((new Cadastros())->getTable().'.'.'r_auth', $r_auth)
                    ->orWhere((new Cadastros())->getTable().'.'.'r_auth', 0);
                })->orderBy((new Cadastros())->getTable().'.'.'id', 'DESC')->groupBy((new Cadastros())->getTable().'.id')->limit(500)->get();
            }

            // # -
            $cadastros_count  = clone $cadastros;
            $cadastros_count  = $cadastros_count->count((new Cadastros())->getTable().'.id');

            return view('cadastros.index', [
                'exibe_filtros'     => 1,
                'cadastros'         => $cadastros,
                'cadastros_count'   => $cadastros_count, // # -
                'controller_model'  => $controller_model, // # -

            ]);

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao aplicar filtros: " . $e->getMessage());

        }

        return Redirect::to('/cadastros');
    }

    public function create()
    {
        $user = Auth::user();

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de cadastro do módulo cadastros'));
        }

        return view('cadastros.add', [

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

            $cadastros = new Cadastros();

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

            $cadastros->r_auth = $r_auth;

            $cadastros->fill($store);

            $cadastros->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($cadastros, $relacionamento, 'Cadastros', 'cadastros');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($cadastros, $grids, 'cadastros');
            }

            try {

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Session::flash('flash_success', "Cadastro realizado com sucesso!");

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' cadastros: cadastrou ID: ' . $cadastros->id));
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

        return Redirect::to('/cadastros');

    }

    public function copy($id)
    {
        $user = Auth::user();

        $cadastros = Cadastros::where('ID', $id)->first();

        if (env('IGNORE_COLUMNS')) {

            $hidden = env('IGNORE_COLUMNS');

            $cadastros = $cadastros->makeHidden(explode(',', $hidden));
        }

        $new = $cadastros->replicate();

        $new->push();

        Session::flash('flash_success', "Linha duplicada com sucesso!");

        return Redirect::to("/cadastros/$new->id/edit");
    }

    public function show($id)
    {
        $user = Auth::user();

        $cadastros = Cadastros::find($id);

        if (!$cadastros) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/cadastros');
        }

        if (!Permissions::permissaoModerador($user) && $cadastros->r_auth != 0 && $cadastros->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/cadastros');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou o ID #' . $id . ' do módulo cadastros'));
        }

        if (isset($cadastros->Template) && $cadastros->Template->template && 1) {

            $url = $this->templateRepository::parseTemplate(['cadastros' => $cadastros]);

            return \Redirect::intended($url);

        }
        else
        {
            return view('cadastros.show', [
                'cadastros' => $cadastros,

            ]);
        }
    }

    public function modal($id)
    {
        $user = Auth::user();

        $cadastros = Cadastros::find($id);

        return view('cadastros.modal', [
            'cadastros' => $cadastros,

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
                $cadastros = Cadastros::where($subForm, $value);
                if(!empty($subForm2) and !empty($subForm2_value)){
                    $cadastros->where($subForm2,$subForm2_value);
                }
                if(!empty($subForm3) and !empty($subForm3_value)){
                    $cadastros->where($subForm3,$subForm3_value);
                }
                $cadastros = $cadastros->get();
            }
            else
            {
               $cadastros = Cadastros::where($subForm, $value)->where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
               });
               if(!empty($subForm2) and !empty($subForm2_value)){
                   $cadastros->where($subForm2,$subForm2_value);
               }
               if(!empty($subForm3) and !empty($subForm3_value)){
                   $cadastros->where($subForm3,$subForm3_value);
               }
               $cadastros = $cadastros->get();
            }
        }
        else
        {
            if (Permissions::permissaoModerador($user))
            {
                $cadastros = Cadastros::find($value);
            }
            else
            {
               $cadastros = Cadastros::where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->find($value);
            }
        }

        return Response::json($cadastros);
    }

    public function pdf($id)
    {
        $user = Auth::user();

        $cadastros = Cadastros::find($id);

        return view('cadastros.pdf', [
            'cadastros' => $cadastros,

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
                    $cadastros = Cadastros::find($value['id']);
                }
                else
                {
                    $cadastros = new Cadastros();
                }

                $cadastros->fill($value);

                $cadastros->save();

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

        return Redirect::to('/cadastros');
    }

    public function edit($id)
    {
        $user = Auth::user();

        $cadastros = Cadastros::find($id);

        if (!$cadastros) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/cadastros');
        }

        if (!Permissions::permissaoModerador($user) && $cadastros->r_auth != 0 && $cadastros->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/cadastros');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo cadastros'));
        }

        return view('cadastros.edit', [
            'cadastros' => $cadastros,

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

            $cadastros = Cadastros::find($store['id']);

            if (!$cadastros) {

                \Session::flash('flash_error', 'Registro não encontrado!');

                return Redirect::to('/cadastros');
            }

            if (!Permissions::permissaoModerador($user) && $cadastros->r_auth != 0 && $cadastros->r_auth != $user->id)
            {
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/cadastros');
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

            $cadastros->r_auth = $r_auth;

            $cadastros->fill($store);

            $cadastros->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($cadastros, $relacionamento, 'Cadastros', 'cadastros');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($cadastros, $grids, 'cadastros');
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
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $cadastros->id . ' do módulo cadastros'));
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

        return Redirect::to('/cadastros');
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

            $cadastros         = Cadastros::find($store['id']);

            if(!$cadastros){
                return response()->json(['error'=>'Registro não encontrado!'],400);
            }

            if(!Permissions::permissaoModerador($user) && $cadastros->r_auth != 0 && $cadastros->r_auth != $user->id){
                return response()->json(['error'=>'Você não tem permissão para executar esta ação!'],400);
            }

            $cadastros->update([
                $store['item_column']   => $store['item_target_id'],
                'r_auth'                => $r_auth,
            ]);

            DB::commit();

            try {

            }catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $cadastros->id . ' do módulo cadastros'));
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
        $cadastros = Cadastros::find($id);

        try {

        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        return $this->controllerRepository::destroy(new Cadastros(), $id, 'cadastros');
    }

}
