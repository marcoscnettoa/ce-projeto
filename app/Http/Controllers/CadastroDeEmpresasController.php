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

use \App\Models\CadastroDeEmpresas;
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

class CadastroDeEmpresasController extends Controller
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

        $cadastro_de_empresas = CadastroDeEmpresas::getAll(500);
        $cadastro_de_empresas_count  = CadastroDeEmpresas::getAllCount(); // # -

        $controller_model  = new CadastroDeEmpresas(); // # -

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo cadastro_de_empresas'));
        }

        return view('cadastro_de_empresas.index', [
            'exibe_filtros' => 1,
            'cadastro_de_empresas' => $cadastro_de_empresas,
            'cadastro_de_empresas_count' => $cadastro_de_empresas_count,
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

            $cadastro_de_empresas = CadastroDeEmpresas::with((new CadastroDeEmpresas())->filter_with)->select((new CadastroDeEmpresas())->getTable().'.*');

            $controller_model  = new CadastroDeEmpresas(); // # -

            // # Verificação / Filtro GRID para Inclusão
            if(isset($store['grid_fil'])){
                foreach($store['grid_fil'] as $GF_K => $GF){
                    if(in_array($GF_K,['operador','between'])){ continue; }
                    $exp_gf_k = explode('__', $GF_K, 2);
                    // ! Reforço*
                    if(count($exp_gf_k) == 2){
                        $cadastro_de_empresas->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        if(count($GF)){
                            foreach($GF as $GF_C => $GF_V){
                                if(empty($GF_V)){ continue; }
                                if(isset($store['grid_fil']['operador'][$GF_K][$GF_C]) || isset($store['grid_fil']['between'][$GF_K][$GF_C])){
                                    if($store['grid_fil']['operador'][$GF_K][$GF_C] == 'contem'){
                                        $cadastro_de_empresas->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                    }elseif($store['grid_fil']['operador'][$GF_K][$GF_C] == 'entre'){
                                        $cadastro_de_empresas->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                    }else {
                                        $cadastro_de_empresas->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                    }
                                }else {
                                    if(is_numeric($GF_V)){
                                        $cadastro_de_empresas->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                    }else {
                                        $cadastro_de_empresas->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
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
                            $cadastro_de_empresas->where((new CadastroDeEmpresas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $cadastro_de_empresas->whereBetween((new CadastroDeEmpresas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $cadastro_de_empresas->where((new CadastroDeEmpresas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $cadastro_de_empresas->where((new CadastroDeEmpresas())->getTable().'.'.$key, $store[$key]);
                        }
                        else
                        {
                            $cadastro_de_empresas->where((new CadastroDeEmpresas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
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
                $cadastro_de_empresas = $cadastro_de_empresas->orderBy((new CadastroDeEmpresas())->getTable().'.'.'id', 'DESC')->groupBy((new CadastroDeEmpresas())->getTable().'.id')->limit(500)->get();
            }
            else
            {
                $cadastro_de_empresas = $cadastro_de_empresas->where(function($q) use ($r_auth) {
                    $q->where((new CadastroDeEmpresas())->getTable().'.'.'r_auth', $r_auth)
                    ->orWhere((new CadastroDeEmpresas())->getTable().'.'.'r_auth', 0);
                })->orderBy((new CadastroDeEmpresas())->getTable().'.'.'id', 'DESC')->groupBy((new CadastroDeEmpresas())->getTable().'.id')->limit(500)->get();
            }

            // # -
            $cadastro_de_empresas_count  = clone $cadastro_de_empresas;
            $cadastro_de_empresas_count  = $cadastro_de_empresas_count->count((new CadastroDeEmpresas())->getTable().'.id');

            return view('cadastro_de_empresas.index', [
                'exibe_filtros' => 1,
                'cadastro_de_empresas' => $cadastro_de_empresas,
                'cadastro_de_empresas_count' => $cadastro_de_empresas_count,
                'controller_model'  => $controller_model, // # -

            ]);

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao aplicar filtros: " . $e->getMessage());

        }

        return Redirect::to('/cadastro_de_empresas');
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
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de cadastro do módulo cadastro_de_empresas'));
        }

        return view((!$R_modal?'cadastro_de_empresas.add':'cadastro_de_empresas.add_modal'), [

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

            $cadastro_de_empresas = new CadastroDeEmpresas();

if ($request->logotipo_empresa) {

if ($request->hasFile("logotipo_empresa")) {

    if (!in_array($request->logotipo_empresa->getClientOriginalExtension(), $this->upload)) {

        return back()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));

    }

    if ($request->logotipo_empresa->getSize() > $this->maxSize) {
        return back()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");

    }

    $file = base64_encode($request->logotipo_empresa->getClientOriginalName()) . "-" . uniqid().".".$request->logotipo_empresa->getClientOriginalExtension();

    if(env("FILESYSTEM_DRIVER") == "s3") {

        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("logotipo_empresa")));

    } else {

        $request->logotipo_empresa->move(public_path("images"), $file);

    }

    $store["logotipo_empresa"] = $file;

}
} else {
    $store["logotipo_empresa"] = null;

}
$store["comercio"] = (isset($store["comercio"]) && $store["comercio"] == "on");
$store["servico"] = (isset($store["servico"]) && $store["servico"] == "on");
$store["industria"] = (isset($store["industria"]) && $store["industria"] == "on");
$store["importador"] = (isset($store["importador"]) && $store["importador"] == "on");

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

            $cadastro_de_empresas->r_auth = $r_auth;

            $cadastro_de_empresas->fill($store);

            $cadastro_de_empresas->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($cadastro_de_empresas, $relacionamento, 'CadastroDeEmpresas', 'cadastro_de_empresas');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($cadastro_de_empresas, $grids, 'cadastro_de_empresas');
            }

            try {

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' cadastro_de_empresas: cadastrou ID: ' . $cadastro_de_empresas->id));
            }

            DB::commit();

            if(!$modalCreateEdit){
                Session::flash('flash_success', "Cadastro realizado com sucesso!");
            }else {
                return response()->json([
                    'ok'       => true,
                    'id'       => $cadastro_de_empresas->id,
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
            return Redirect::to('/cadastro_de_empresas');
        }else {
            return response()->json([
                'ok'       => true
            ], 200);
        }

    }

    public function copy($id)
    {
        $user = Auth::user();

        $cadastro_de_empresas = CadastroDeEmpresas::where('ID', $id)->first();

        if (env('IGNORE_COLUMNS')) {

            $hidden = env('IGNORE_COLUMNS');

            $cadastro_de_empresas = $cadastro_de_empresas->makeHidden(explode(',', $hidden));
        }

        $new = $cadastro_de_empresas->replicate();

        $new->push();

        Session::flash('flash_success', "Linha duplicada com sucesso!");

        return Redirect::to("/cadastro_de_empresas/$new->id/edit");
    }

    public function show($id)
    {
        $user = Auth::user();

        $cadastro_de_empresas = CadastroDeEmpresas::find($id);

        if (!$cadastro_de_empresas) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/cadastro_de_empresas');
        }

        if (!Permissions::permissaoModerador($user) && $cadastro_de_empresas->r_auth != 0 && $cadastro_de_empresas->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/cadastro_de_empresas');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou o ID #' . $id . ' do módulo cadastro_de_empresas'));
        }

        if (isset($cadastro_de_empresas->Template) && $cadastro_de_empresas->Template->template && 1) {

            $url = $this->templateRepository::parseTemplate(['cadastro_de_empresas' => $cadastro_de_empresas]);

            return \Redirect::intended($url);

        }
        else
        {
            return view('cadastro_de_empresas.show', [
                'cadastro_de_empresas' => $cadastro_de_empresas,

            ]);
        }
    }

    public function modal($id)
    {
        $user = Auth::user();

        $cadastro_de_empresas = CadastroDeEmpresas::find($id);

        return view('cadastro_de_empresas.modal', [
            'cadastro_de_empresas' => $cadastro_de_empresas,

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
                $cadastro_de_empresas = CadastroDeEmpresas::where($subForm, $value);
                if(!empty($subForm2) and !empty($subForm2_value)){
                    $cadastro_de_empresas->where($subForm2,$subForm2_value);
                }
                if(!empty($subForm3) and !empty($subForm3_value)){
                    $cadastro_de_empresas->where($subForm3,$subForm3_value);
                }
                $cadastro_de_empresas = $cadastro_de_empresas->get();
            }
            else
            {
               $cadastro_de_empresas = CadastroDeEmpresas::where($subForm, $value)->where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
               });
               if(!empty($subForm2) and !empty($subForm2_value)){
                   $cadastro_de_empresas->where($subForm2,$subForm2_value);
               }
               if(!empty($subForm3) and !empty($subForm3_value)){
                   $cadastro_de_empresas->where($subForm3,$subForm3_value);
               }
               $cadastro_de_empresas = $cadastro_de_empresas->get();
            }
        }
        else
        {
            if (Permissions::permissaoModerador($user))
            {
                $cadastro_de_empresas = CadastroDeEmpresas::find($value);
            }
            else
            {
               $cadastro_de_empresas = CadastroDeEmpresas::where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->find($value);
            }
        }

        return Response::json($cadastro_de_empresas);
    }

    // # -
    public function list(){
        $cadastro_de_empresas = CadastroDeEmpresas::list(10000, "nome_fantasia_empresa");
        return Response::json($cadastro_de_empresas);
    }

    public function pdf($id)
    {
        $user = Auth::user();

        $cadastro_de_empresas = CadastroDeEmpresas::find($id);

        return view('cadastro_de_empresas.pdf', [
            'cadastro_de_empresas' => $cadastro_de_empresas,

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
                    $cadastro_de_empresas = CadastroDeEmpresas::find($value['id']);
                }
                else
                {
                    $cadastro_de_empresas = new CadastroDeEmpresas();
                }

                $cadastro_de_empresas->fill($value);

                $cadastro_de_empresas->save();

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

        return Redirect::to('/cadastro_de_empresas');
    }

    public function edit($id)
    {
        $user = Auth::user();

        $cadastro_de_empresas = CadastroDeEmpresas::find($id);

        if (!$cadastro_de_empresas) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/cadastro_de_empresas');
        }

        if (!Permissions::permissaoModerador($user) && $cadastro_de_empresas->r_auth != 0 && $cadastro_de_empresas->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/cadastro_de_empresas');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo cadastro_de_empresas'));
        }

        return view('cadastro_de_empresas.edit', [
            'cadastro_de_empresas' => $cadastro_de_empresas,

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

            $cadastro_de_empresas = CadastroDeEmpresas::find($store['id']);

            if (!$cadastro_de_empresas) {

                \Session::flash('flash_error', 'Registro não encontrado!');

                return Redirect::to('/cadastro_de_empresas');
            }

            if (!Permissions::permissaoModerador($user) && $cadastro_de_empresas->r_auth != 0 && $cadastro_de_empresas->r_auth != $user->id)
            {
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/cadastro_de_empresas');
            }

if ($request->logotipo_empresa) {

if ($request->hasFile("logotipo_empresa")) {

    if (!in_array($request->logotipo_empresa->getClientOriginalExtension(), $this->upload)) {

        return back()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));

    }

    if ($request->logotipo_empresa->getSize() > $this->maxSize) {
        return back()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");

    }

    $file = base64_encode($request->logotipo_empresa->getClientOriginalName()) . "-" . uniqid().".".$request->logotipo_empresa->getClientOriginalExtension();

    if(env("FILESYSTEM_DRIVER") == "s3") {

        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("logotipo_empresa")));

    } else {

        $request->logotipo_empresa->move(public_path("images"), $file);

    }

    $store["logotipo_empresa"] = $file;

}
} else {
    $store["logotipo_empresa"] = null;

}
$store["comercio"] = (isset($store["comercio"]) && $store["comercio"] == "on");
$store["servico"] = (isset($store["servico"]) && $store["servico"] == "on");
$store["industria"] = (isset($store["industria"]) && $store["industria"] == "on");
$store["importador"] = (isset($store["importador"]) && $store["importador"] == "on");

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

            $cadastro_de_empresas->r_auth = $r_auth;

            $cadastro_de_empresas->fill($store);

            $cadastro_de_empresas->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($cadastro_de_empresas, $relacionamento, 'CadastroDeEmpresas', 'cadastro_de_empresas');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($cadastro_de_empresas, $grids, 'cadastro_de_empresas');
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
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $cadastro_de_empresas->id . ' do módulo cadastro_de_empresas'));
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

        return Redirect::to('/cadastro_de_empresas');
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

            $cadastro_de_empresas         = CadastroDeEmpresas::find($store['id']);

            if(!$cadastro_de_empresas){
                return response()->json(['error'=>'Registro não encontrado!'],400);
            }

            if(!Permissions::permissaoModerador($user) && $cadastro_de_empresas->r_auth != 0 && $cadastro_de_empresas->r_auth != $user->id){
                return response()->json(['error'=>'Você não tem permissão para executar esta ação!'],400);
            }

            $cadastro_de_empresas->update([
                $store['item_column']   => $store['item_target_id'],
                'r_auth'                => $r_auth,
            ]);

            DB::commit();

            try {

            }catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $cadastro_de_empresas->id . ' do módulo cadastro_de_empresas'));
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
        $cadastro_de_empresas = CadastroDeEmpresas::find($id);

        try {

        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        return $this->controllerRepository::destroy(new CadastroDeEmpresas(), $id, 'cadastro_de_empresas');
    }

}
