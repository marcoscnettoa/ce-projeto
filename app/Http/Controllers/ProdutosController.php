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

use \App\Models\Produtos;
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

class ProdutosController extends Controller
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

        $produtos = Produtos::getAll(500);
        $produtos_count  = Produtos::getAllCount(); // # -

        $controller_model  = new Produtos(); // # -

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo produtos'));
        }

        return view('produtos.index', [
            'exibe_filtros' => 1,
            'produtos' => $produtos,
            'produtos_count' => $produtos_count,
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

            $produtos = Produtos::with((new Produtos())->filter_with)->select((new Produtos())->getTable().'.*');

            $controller_model  = new Produtos(); // # -

            // # Verificação / Filtro GRID para Inclusão
            if(isset($store['grid_fil'])){
                foreach($store['grid_fil'] as $GF_K => $GF){
                    if(in_array($GF_K,['operador','between'])){ continue; }
                    $exp_gf_k = explode('__', $GF_K, 2);
                    // ! Reforço*
                    if(count($exp_gf_k) == 2){
                        $produtos->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        if(count($GF)){
                            foreach($GF as $GF_C => $GF_V){
                                if(empty($GF_V)){ continue; }
                                if(isset($store['grid_fil']['operador'][$GF_K][$GF_C]) || isset($store['grid_fil']['between'][$GF_K][$GF_C])){
                                    if($store['grid_fil']['operador'][$GF_K][$GF_C] == 'contem'){
                                        $produtos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                    }elseif($store['grid_fil']['operador'][$GF_K][$GF_C] == 'entre'){
                                        $produtos->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                    }else {
                                        $produtos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                    }
                                }else {
                                    if(is_numeric($GF_V)){
                                        $produtos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                    }else {
                                        $produtos->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
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
                            $produtos->where((new Produtos())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $produtos->whereBetween((new Produtos())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $produtos->where((new Produtos())->getTable().'.'.$key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $produtos->where((new Produtos())->getTable().'.'.$key, $store[$key]);
                        }
                        else
                        {
                            $produtos->where((new Produtos())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
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
                $produtos = $produtos->orderBy((new Produtos())->getTable().'.'.'id', 'DESC')->groupBy((new Produtos())->getTable().'.id')->limit(500)->get();
            }
            else
            {
                $produtos = $produtos->where(function($q) use ($r_auth) {
                    $q->where((new Produtos())->getTable().'.'.'r_auth', $r_auth)
                    ->orWhere((new Produtos())->getTable().'.'.'r_auth', 0);
                })->orderBy((new Produtos())->getTable().'.'.'id', 'DESC')->groupBy((new Produtos())->getTable().'.id')->limit(500)->get();
            }

            // # -
            $produtos_count  = clone $produtos;
            $produtos_count  = $produtos_count->count((new Produtos())->getTable().'.id');

            return view('produtos.index', [
                'exibe_filtros' => 1,
                'produtos' => $produtos,
                'produtos_count' => $produtos_count,
                'controller_model'  => $controller_model, // # -

            ]);

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao aplicar filtros: " . $e->getMessage());

        }

        return Redirect::to('/produtos');
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
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de cadastro do módulo produtos'));
        }

        return view((!$R_modal?'produtos.add':'produtos.add_modal'), [

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

            $produtos = new Produtos();

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

            $produtos->r_auth = $r_auth;
            $produtos->fill($store);
            $produtos->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($produtos, $relacionamento, 'Produtos', 'produtos');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($produtos, $grids, 'produtos');
            }

            try {

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' produtos: cadastrou ID: ' . $produtos->id));
            }

            DB::commit();

            if(!$modalCreateEdit){
                Session::flash('flash_success', "Cadastro realizado com sucesso!");
            }else {
                return response()->json([
                    'ok'       => true,
                    'id'       => $produtos->id,
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
            return Redirect::to('/produtos');
        }else {
            return response()->json([
                'ok'       => true
            ], 200);
        }

    }

    public function copy($id)
    {
        $user = Auth::user();

        $produtos = Produtos::where('ID', $id)->first();

        if (env('IGNORE_COLUMNS')) {

            $hidden = env('IGNORE_COLUMNS');

            $produtos = $produtos->makeHidden(explode(',', $hidden));
        }

        $new = $produtos->replicate();

        $new->push();

        Session::flash('flash_success', "Linha duplicada com sucesso!");

        return Redirect::to("/produtos/$new->id/edit");
    }

    public function show($id)
    {
        $user = Auth::user();

        $produtos = Produtos::find($id);

        if (!$produtos) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/produtos');
        }

        if (!Permissions::permissaoModerador($user) && $produtos->r_auth != 0 && $produtos->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/produtos');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou o ID #' . $id . ' do módulo produtos'));
        }

        if (isset($produtos->Template) && $produtos->Template->template && 1) {

            $url = $this->templateRepository::parseTemplate(['produtos' => $produtos]);

            return \Redirect::intended($url);

        }
        else
        {
            return view('produtos.show', [
                'produtos' => $produtos,

            ]);
        }
    }

    public function modal($id)
    {
        $user = Auth::user();

        $produtos = Produtos::find($id);

        return view('produtos.modal', [
            'produtos' => $produtos,

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
                $produtos = Produtos::where($subForm, $value);
                if(!empty($subForm2) and !empty($subForm2_value)){
                    $produtos->where($subForm2,$subForm2_value);
                }
                if(!empty($subForm3) and !empty($subForm3_value)){
                    $produtos->where($subForm3,$subForm3_value);
                }
                $produtos = $produtos->get();
            }
            else
            {
               $produtos = Produtos::where($subForm, $value)->where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
               });
               if(!empty($subForm2) and !empty($subForm2_value)){
                   $produtos->where($subForm2,$subForm2_value);
               }
               if(!empty($subForm3) and !empty($subForm3_value)){
                   $produtos->where($subForm3,$subForm3_value);
               }
               $produtos = $produtos->get();
            }
        }
        else
        {
            if (Permissions::permissaoModerador($user))
            {
                $produtos = Produtos::find($value);
            }
            else
            {
               $produtos = Produtos::where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->find($value);
            }
        }

        return Response::json($produtos);
    }

    // # -
    public function list(){
        $produtos = Produtos::list(10000, "produto");
        return Response::json($produtos);
    }

    public function pdf($id)
    {
        $user = Auth::user();

        $produtos = Produtos::find($id);

        return view('produtos.pdf', [
            'produtos' => $produtos,

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
                    $produtos = Produtos::find($value['id']);
                }
                else
                {
                    $produtos = new Produtos();
                }

                $produtos->fill($value);

                $produtos->save();

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

        return Redirect::to('/produtos');
    }

    public function edit($id)
    {
        $user = Auth::user();

        $produtos = Produtos::find($id);

        if (!$produtos) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/produtos');
        }

        if (!Permissions::permissaoModerador($user) && $produtos->r_auth != 0 && $produtos->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/produtos');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo produtos'));
        }

        return view('produtos.edit', [
            'produtos' => $produtos,

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

            $produtos = Produtos::find($store['id']);

            if (!$produtos) {

                \Session::flash('flash_error', 'Registro não encontrado!');

                return Redirect::to('/produtos');
            }

            if (!Permissions::permissaoModerador($user) && $produtos->r_auth != 0 && $produtos->r_auth != $user->id)
            {
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/produtos');
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

            $produtos->r_auth = $r_auth;

            $produtos->fill($store);

            $produtos->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($produtos, $relacionamento, 'Produtos', 'produtos');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($produtos, $grids, 'produtos');
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
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $produtos->id . ' do módulo produtos'));
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

        return Redirect::to('/produtos');
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

            $produtos         = Produtos::find($store['id']);

            if(!$produtos){
                return response()->json(['error'=>'Registro não encontrado!'],400);
            }

            if(!Permissions::permissaoModerador($user) && $produtos->r_auth != 0 && $produtos->r_auth != $user->id){
                return response()->json(['error'=>'Você não tem permissão para executar esta ação!'],400);
            }

            $produtos->update([
                $store['item_column']   => $store['item_target_id'],
                'r_auth'                => $r_auth,
            ]);

            DB::commit();

            try {

            }catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $produtos->id . ' do módulo produtos'));
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
        $produtos = Produtos::find($id);

        try {

        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        return $this->controllerRepository::destroy(new Produtos(), $id, 'produtos');
    }

}
