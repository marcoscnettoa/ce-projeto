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

use \App\Models\CcfCartaoDeCredito;
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

class CcfCartaoDeCreditoController extends Controller
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

        $ccf_cartao_de_credito = CcfCartaoDeCredito::getAll(500);
        $ccf_cartao_de_credito_count  = CcfCartaoDeCredito::getAllCount(); // # -

        $controller_model  = new CcfCartaoDeCredito(); // # -

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo ccf_cartao_de_credito'));
        }

        return view('ccf_cartao_de_credito.index', [
            'exibe_filtros' => 1,
            'ccf_cartao_de_credito' => $ccf_cartao_de_credito,
            'ccf_cartao_de_credito_count' => $ccf_cartao_de_credito_count,
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

            $ccf_cartao_de_credito = CcfCartaoDeCredito::with((new CcfCartaoDeCredito())->filter_with)->select((new CcfCartaoDeCredito())->getTable().'.*');

            $controller_model  = new CcfCartaoDeCredito(); // # -

            // # Verificação / Filtro GRID para Inclusão
            if(isset($store['grid_fil'])){
                foreach($store['grid_fil'] as $GF_K => $GF){
                    if(in_array($GF_K,['operador','between'])){ continue; }
                    $exp_gf_k = explode('__', $GF_K, 2);
                    // ! Reforço*
                    if(count($exp_gf_k) == 2){
                        $ccf_cartao_de_credito->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        if(count($GF)){
                            foreach($GF as $GF_C => $GF_V){
                                if(empty($GF_V)){ continue; }
                                if(isset($store['grid_fil']['operador'][$GF_K][$GF_C]) || isset($store['grid_fil']['between'][$GF_K][$GF_C])){
                                    if($store['grid_fil']['operador'][$GF_K][$GF_C] == 'contem'){
                                        $ccf_cartao_de_credito->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                    }elseif($store['grid_fil']['operador'][$GF_K][$GF_C] == 'entre'){
                                        $ccf_cartao_de_credito->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                    }else {
                                        $ccf_cartao_de_credito->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                    }
                                }else {
                                    if(is_numeric($GF_V)){
                                        $ccf_cartao_de_credito->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                    }else {
                                        $ccf_cartao_de_credito->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
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
                            $ccf_cartao_de_credito->where((new CcfCartaoDeCredito())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $ccf_cartao_de_credito->whereBetween((new CcfCartaoDeCredito())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $ccf_cartao_de_credito->where((new CcfCartaoDeCredito())->getTable().'.'.$key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $ccf_cartao_de_credito->where((new CcfCartaoDeCredito())->getTable().'.'.$key, $store[$key]);
                        }
                        else
                        {
                            $ccf_cartao_de_credito->where((new CcfCartaoDeCredito())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
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
                $ccf_cartao_de_credito = $ccf_cartao_de_credito->orderBy((new CcfCartaoDeCredito())->getTable().'.'.'id', 'DESC')->groupBy((new CcfCartaoDeCredito())->getTable().'.id')->limit(500)->get();
            }
            else
            {
                $ccf_cartao_de_credito = $ccf_cartao_de_credito->where(function($q) use ($r_auth) {
                    $q->where((new CcfCartaoDeCredito())->getTable().'.'.'r_auth', $r_auth)
                    ->orWhere((new CcfCartaoDeCredito())->getTable().'.'.'r_auth', 0);
                })->orderBy((new CcfCartaoDeCredito())->getTable().'.'.'id', 'DESC')->groupBy((new CcfCartaoDeCredito())->getTable().'.id')->limit(500)->get();
            }

            // # -
            $ccf_cartao_de_credito_count  = clone $ccf_cartao_de_credito;
            $ccf_cartao_de_credito_count  = $ccf_cartao_de_credito_count->count((new CcfCartaoDeCredito())->getTable().'.id');

            return view('ccf_cartao_de_credito.index', [
                'exibe_filtros' => 1,
                'ccf_cartao_de_credito' => $ccf_cartao_de_credito,
                'ccf_cartao_de_credito_count' => $ccf_cartao_de_credito_count,
                'controller_model'  => $controller_model, // # -

            ]);

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao aplicar filtros: " . $e->getMessage());

        }

        return Redirect::to('/ccf_cartao_de_credito');
    }

    public function create()
    {
        $user = Auth::user();

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de cadastro do módulo ccf_cartao_de_credito'));
        }

        return view('ccf_cartao_de_credito.add', [

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

            $ccf_cartao_de_credito = new CcfCartaoDeCredito();

            $store["valor_total_"] = ((!is_null($store["valor_total_"]) && !empty($store["valor_total_"])) ? $store["valor_total_"] : 0);
$store["nro_de_parcelas_"] = ((!is_null($store["nro_de_parcelas_"]) && !empty($store["nro_de_parcelas_"])) ? $store["nro_de_parcelas_"] : 0);
            try {
                $store["valor_da_parcela_"] = number_format($store["valor_total_"] / str_replace(',', '.', $store["nro_de_parcelas_"]), 2, ',', '.');
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            $store["visa_"] = (isset($store["visa_"]) && $store["visa_"] == "on");
$store["mastercard_"] = (isset($store["mastercard_"]) && $store["mastercard_"] == "on");
$store["diners_"] = (isset($store["diners_"]) && $store["diners_"] == "on");
$store["outros"] = (isset($store["outros"]) && $store["outros"] == "on");

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

            $ccf_cartao_de_credito->r_auth = $r_auth;

            $ccf_cartao_de_credito->fill($store);

            $ccf_cartao_de_credito->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($ccf_cartao_de_credito, $relacionamento, 'CcfCartaoDeCredito', 'ccf_cartao_de_credito');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($ccf_cartao_de_credito, $grids, 'ccf_cartao_de_credito');
            }

            try {

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Session::flash('flash_success', "Cadastro realizado com sucesso!");

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' ccf_cartao_de_credito: cadastrou ID: ' . $ccf_cartao_de_credito->id));
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

        return Redirect::to('/ccf_cartao_de_credito');

    }

    public function copy($id)
    {
        $user = Auth::user();

        $ccf_cartao_de_credito = CcfCartaoDeCredito::where('ID', $id)->first();

        if (env('IGNORE_COLUMNS')) {

            $hidden = env('IGNORE_COLUMNS');

            $ccf_cartao_de_credito = $ccf_cartao_de_credito->makeHidden(explode(',', $hidden));
        }

        $new = $ccf_cartao_de_credito->replicate();

        $new->push();

        Session::flash('flash_success', "Linha duplicada com sucesso!");

        return Redirect::to("/ccf_cartao_de_credito/$new->id/edit");
    }

    public function show($id)
    {
        $user = Auth::user();

        $ccf_cartao_de_credito = CcfCartaoDeCredito::find($id);

        if (!$ccf_cartao_de_credito) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/ccf_cartao_de_credito');
        }

        if (!Permissions::permissaoModerador($user) && $ccf_cartao_de_credito->r_auth != 0 && $ccf_cartao_de_credito->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/ccf_cartao_de_credito');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou o ID #' . $id . ' do módulo ccf_cartao_de_credito'));
        }

        if (isset($ccf_cartao_de_credito->Template) && $ccf_cartao_de_credito->Template->template && 1) {

            $url = $this->templateRepository::parseTemplate(['ccf_cartao_de_credito' => $ccf_cartao_de_credito]);

            return \Redirect::intended($url);

        }
        else
        {
            return view('ccf_cartao_de_credito.show', [
                'ccf_cartao_de_credito' => $ccf_cartao_de_credito,

            ]);
        }
    }

    public function modal($id)
    {
        $user = Auth::user();

        $ccf_cartao_de_credito = CcfCartaoDeCredito::find($id);

        return view('ccf_cartao_de_credito.modal', [
            'ccf_cartao_de_credito' => $ccf_cartao_de_credito,

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
                $ccf_cartao_de_credito = CcfCartaoDeCredito::where($subForm, $value);
                if(!empty($subForm2) and !empty($subForm2_value)){
                    $ccf_cartao_de_credito->where($subForm2,$subForm2_value);
                }
                if(!empty($subForm3) and !empty($subForm3_value)){
                    $ccf_cartao_de_credito->where($subForm3,$subForm3_value);
                }
                $ccf_cartao_de_credito = $ccf_cartao_de_credito->get();
            }
            else
            {
               $ccf_cartao_de_credito = CcfCartaoDeCredito::where($subForm, $value)->where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
               });
               if(!empty($subForm2) and !empty($subForm2_value)){
                   $ccf_cartao_de_credito->where($subForm2,$subForm2_value);
               }
               if(!empty($subForm3) and !empty($subForm3_value)){
                   $ccf_cartao_de_credito->where($subForm3,$subForm3_value);
               }
               $ccf_cartao_de_credito = $ccf_cartao_de_credito->get();
            }
        }
        else
        {
            if (Permissions::permissaoModerador($user))
            {
                $ccf_cartao_de_credito = CcfCartaoDeCredito::find($value);
            }
            else
            {
               $ccf_cartao_de_credito = CcfCartaoDeCredito::where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->find($value);
            }
        }

        return Response::json($ccf_cartao_de_credito);
    }

    public function pdf($id)
    {
        $user = Auth::user();

        $ccf_cartao_de_credito = CcfCartaoDeCredito::find($id);

        return view('ccf_cartao_de_credito.pdf', [
            'ccf_cartao_de_credito' => $ccf_cartao_de_credito,

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
                    $ccf_cartao_de_credito = CcfCartaoDeCredito::find($value['id']);
                }
                else
                {
                    $ccf_cartao_de_credito = new CcfCartaoDeCredito();
                }

                $ccf_cartao_de_credito->fill($value);

                $ccf_cartao_de_credito->save();

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

        return Redirect::to('/ccf_cartao_de_credito');
    }

    public function edit($id)
    {
        $user = Auth::user();

        $ccf_cartao_de_credito = CcfCartaoDeCredito::find($id);

        if (!$ccf_cartao_de_credito) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/ccf_cartao_de_credito');
        }

        if (!Permissions::permissaoModerador($user) && $ccf_cartao_de_credito->r_auth != 0 && $ccf_cartao_de_credito->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/ccf_cartao_de_credito');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo ccf_cartao_de_credito'));
        }

        return view('ccf_cartao_de_credito.edit', [
            'ccf_cartao_de_credito' => $ccf_cartao_de_credito,

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

            $ccf_cartao_de_credito = CcfCartaoDeCredito::find($store['id']);

            if (!$ccf_cartao_de_credito) {

                \Session::flash('flash_error', 'Registro não encontrado!');

                return Redirect::to('/ccf_cartao_de_credito');
            }

            if (!Permissions::permissaoModerador($user) && $ccf_cartao_de_credito->r_auth != 0 && $ccf_cartao_de_credito->r_auth != $user->id)
            {
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/ccf_cartao_de_credito');
            }

            $store["valor_total_"] = ((!is_null($store["valor_total_"]) && !empty($store["valor_total_"])) ? $store["valor_total_"] : 0);
$store["nro_de_parcelas_"] = ((!is_null($store["nro_de_parcelas_"]) && !empty($store["nro_de_parcelas_"])) ? $store["nro_de_parcelas_"] : 0);
            try {
                $store["valor_da_parcela_"] = number_format($store["valor_total_"] / str_replace(',', '.', $store["nro_de_parcelas_"]), 2, ',', '.');
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            $store["visa_"] = (isset($store["visa_"]) && $store["visa_"] == "on");
$store["mastercard_"] = (isset($store["mastercard_"]) && $store["mastercard_"] == "on");
$store["diners_"] = (isset($store["diners_"]) && $store["diners_"] == "on");
$store["outros"] = (isset($store["outros"]) && $store["outros"] == "on");

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

            $ccf_cartao_de_credito->r_auth = $r_auth;

            $ccf_cartao_de_credito->fill($store);

            $ccf_cartao_de_credito->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($ccf_cartao_de_credito, $relacionamento, 'CcfCartaoDeCredito', 'ccf_cartao_de_credito');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($ccf_cartao_de_credito, $grids, 'ccf_cartao_de_credito');
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
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $ccf_cartao_de_credito->id . ' do módulo ccf_cartao_de_credito'));
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

        return Redirect::to('/ccf_cartao_de_credito');
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

            $ccf_cartao_de_credito         = CcfCartaoDeCredito::find($store['id']);

            if(!$ccf_cartao_de_credito){
                return response()->json(['error'=>'Registro não encontrado!'],400);
            }

            if(!Permissions::permissaoModerador($user) && $ccf_cartao_de_credito->r_auth != 0 && $ccf_cartao_de_credito->r_auth != $user->id){
                return response()->json(['error'=>'Você não tem permissão para executar esta ação!'],400);
            }

            $ccf_cartao_de_credito->update([
                $store['item_column']   => $store['item_target_id'],
                'r_auth'                => $r_auth,
            ]);

            DB::commit();

            try {

            }catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $ccf_cartao_de_credito->id . ' do módulo ccf_cartao_de_credito'));
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
        $ccf_cartao_de_credito = CcfCartaoDeCredito::find($id);

        try {

        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        return $this->controllerRepository::destroy(new CcfCartaoDeCredito(), $id, 'ccf_cartao_de_credito');
    }

}
