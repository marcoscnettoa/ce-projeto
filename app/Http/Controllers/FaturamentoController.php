<?php

namespace App\Http\Controllers;

use App\Models\Vendas;
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

use \App\Models\Faturamento;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;
use setasign\Fpdi\Fpdi;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\Filesystem\Filesystem;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;

use \App\Models\Clientes;
use \App\Models\Templates;

use App\Repositories\UploadRepository;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;

class FaturamentoController extends Controller
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

        $faturamento = Faturamento::getAll(500);
        $faturamento_count  = Faturamento::getAllCount(); // # -

        $controller_model  = new Faturamento(); // # -

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo faturamento'));
        }

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

        return view('faturamento.index', [
            'exibe_filtros' => 1,
            'faturamento' => $faturamento,
            'faturamento_count' => $faturamento_count,
            'controller_model'  => $controller_model, // # -
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'templates_nome_do_template' => $templates_nome_do_template,

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

            $faturamento = Faturamento::with((new Faturamento())->filter_with)->select((new Faturamento())->getTable().'.*');

            $controller_model  = new Faturamento(); // # -

            // # Verificação / Filtro GRID para Inclusão
            if(isset($store['grid_fil'])){
                foreach($store['grid_fil'] as $GF_K => $GF){
                    if(in_array($GF_K,['operador','between'])){ continue; }
                    $exp_gf_k = explode('__', $GF_K, 2);
                    // ! Reforço*
                    if(count($exp_gf_k) == 2){
                        $faturamento->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        if(count($GF)){
                            foreach($GF as $GF_C => $GF_V){
                                if(empty($GF_V)){ continue; }
                                if(isset($store['grid_fil']['operador'][$GF_K][$GF_C]) || isset($store['grid_fil']['between'][$GF_K][$GF_C])){
                                    if($store['grid_fil']['operador'][$GF_K][$GF_C] == 'contem'){
                                        $faturamento->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                    }elseif($store['grid_fil']['operador'][$GF_K][$GF_C] == 'entre'){
                                        $faturamento->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                    }else {
                                        $faturamento->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                    }
                                }else {
                                    if(is_numeric($GF_V)){
                                        $faturamento->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                    }else {
                                        $faturamento->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
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
                            $faturamento->where((new Faturamento())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $faturamento->whereBetween((new Faturamento())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $faturamento->where((new Faturamento())->getTable().'.'.$key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $faturamento->where((new Faturamento())->getTable().'.'.$key, $store[$key]);
                        }
                        else
                        {
                            $faturamento->where((new Faturamento())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
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
                $faturamento = $faturamento->orderBy((new Faturamento())->getTable().'.'.'id', 'DESC')->groupBy((new Faturamento())->getTable().'.id')->limit(500)->get();
            }
            else
            {
                $faturamento = $faturamento->where(function($q) use ($r_auth) {
                    $q->where((new Faturamento())->getTable().'.'.'r_auth', $r_auth)
                    ->orWhere((new Faturamento())->getTable().'.'.'r_auth', 0);
                })->orderBy((new Faturamento())->getTable().'.'.'id', 'DESC')->groupBy((new Faturamento())->getTable().'.id')->limit(500)->get();
            }

            // # -
            $faturamento_count  = clone $faturamento;
            $faturamento_count  = $faturamento_count->count((new Faturamento())->getTable().'.id');

            $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

            return view('faturamento.index', [
                'exibe_filtros' => 1,
                'faturamento' => $faturamento,
                'faturamento_count' => $faturamento_count,
                'controller_model'  => $controller_model, // # -
                'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'templates_nome_do_template' => $templates_nome_do_template,

            ]);

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao aplicar filtros: " . $e->getMessage());

        }

        return Redirect::to('/faturamento');
    }

    public function create()
    {
        $user = Auth::user();

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de cadastro do módulo faturamento'));
        }

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

        return view('faturamento.add', [
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'templates_nome_do_template' => $templates_nome_do_template,

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

            // # -
            $store_venda_id = ((isset($store['venda_id']) and !empty($store['venda_id']))?$store['venda_id']:null);
            unset($store['venda_id']);
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

            $faturamento = new Faturamento();

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

            $faturamento->r_auth = $r_auth;

            $faturamento->fill($store);

            $faturamento->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($faturamento, $relacionamento, 'Faturamento', 'faturamento');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($faturamento, $grids, 'faturamento');
            }

            // # Lista - Vendas ( Faturamento -| Cliente / Data Inicial / Data  Final )
            $faturamento->n_da_fatura   = $faturamento->id; //str_pad($faturamento->id,7,'0',STR_PAD_LEFT);
            $faturamento->save();
            Vendas::where('tipo_de_venda',2)->where('faturamento',$faturamento->id) // str_pad($faturamento->id,7,'0',STR_PAD_LEFT)
                    ->update([
                        'faturamento'   =>  null,
                        'foi_faturado'  =>  null
                    ]);

            if($store_venda_id != null){
                foreach($store_venda_id as $v_id){
                    $Vendas = Vendas::find($v_id);
                    if($Vendas){
                        $Vendas->faturamento    = $faturamento->id; //str_pad($faturamento->id,7,'0',STR_PAD_LEFT);
                        $Vendas->foi_faturado   = 1;
                        $Vendas->save();
                    }
                }
            }

            /*try {
                    DB::statement("UPDATE
                    vendas
                    SET
                    faturamento  = NULL,
                    foi_faturado = NULL
                    WHERE
                    tipo_de_venda = 2 AND
                    faturamento   = LPAD($faturamento->id,7,'0')");
                    DB::statement("UPDATE
                    vendas
                    SET
                    faturamento  = LPAD($faturamento->id,7,'0'),
                    foi_faturado = 1
                    WHERE
                    tipo_de_venda = 2 AND
                    $faturamento->cliente IS NOT NULL AND
                    $faturamento->data_inicial IS NOT NULL AND
                    $faturamento->data_final IS NOT NULL AND
                    vendas.faturamento IS NULL AND
                    (vendas.foi_faturado IS NULL || vendas.foi_faturado = '0') AND
                    vendas.cliente = $faturamento->cliente AND
                    DATE_FORMAT(vendas.data, '%Y-%m-%d') BETWEEN '$faturamento->data_inicial' AND '$faturamento->data_final'");
                    DB::statement("UPDATE
                    faturamento
                    SET
                    template 	     = 3,
                    n_da_fatura   = LPAD($faturamento->id,7,'0')
                    WHERE
                    id = $faturamento->id");
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }*/

            Session::flash('flash_success', "Cadastro realizado com sucesso!");

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' faturamento: cadastrou ID: ' . $faturamento->id));
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

        return Redirect::to('/faturamento/'.$faturamento->id.'/edit');

    }

    public function copy($id)
    {
        $user = Auth::user();

        $faturamento = Faturamento::where('ID', $id)->first();

        if (env('IGNORE_COLUMNS')) {

            $hidden = env('IGNORE_COLUMNS');

            $faturamento = $faturamento->makeHidden(explode(',', $hidden));
        }

        $new = $faturamento->replicate();

        $new->push();

        Session::flash('flash_success', "Linha duplicada com sucesso!");

        return Redirect::to("/faturamento/$new->id/edit");
    }

    public function show($id)
    {
        $user = Auth::user();

        $faturamento = Faturamento::find($id);

        if (!$faturamento) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/faturamento');
        }

        if (!Permissions::permissaoModerador($user) && $faturamento->r_auth != 0 && $faturamento->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/faturamento');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou o ID #' . $id . ' do módulo faturamento'));
        }

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

        if (isset($faturamento->Template) && $faturamento->Template->template && 1) {

            $url = $this->templateRepository::parseTemplate(['faturamento' => $faturamento]);

            return \Redirect::intended($url);

        }
        else
        {
            return view('faturamento.show', [
                'faturamento' => $faturamento,
                'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'templates_nome_do_template' => $templates_nome_do_template,

            ]);
        }
    }

    public function modal($id)
    {
        $user = Auth::user();

        $faturamento = Faturamento::find($id);

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

        return view('faturamento.modal', [
            'faturamento' => $faturamento,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'templates_nome_do_template' => $templates_nome_do_template,

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
                $faturamento = Faturamento::where($subForm, $value);
                if(!empty($subForm2) and !empty($subForm2_value)){
                    $faturamento->where($subForm2,$subForm2_value);
                }
                if(!empty($subForm3) and !empty($subForm3_value)){
                    $faturamento->where($subForm3,$subForm3_value);
                }
                $faturamento = $faturamento->get();
            }
            else
            {
               $faturamento = Faturamento::where($subForm, $value)->where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
               });
               if(!empty($subForm2) and !empty($subForm2_value)){
                   $faturamento->where($subForm2,$subForm2_value);
               }
               if(!empty($subForm3) and !empty($subForm3_value)){
                   $faturamento->where($subForm3,$subForm3_value);
               }
               $faturamento = $faturamento->get();
            }
        }
        else
        {
            if (Permissions::permissaoModerador($user))
            {
                $faturamento = Faturamento::find($value);
            }
            else
            {
               $faturamento = Faturamento::where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->find($value);
            }
        }

        return Response::json($faturamento);
    }

    public function pdf($id)
    {
        $user = Auth::user();

        $faturamento = Faturamento::find($id);

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

        return view('faturamento.pdf', [
            'faturamento' => $faturamento,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'templates_nome_do_template' => $templates_nome_do_template,

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
                    $faturamento = Faturamento::find($value['id']);
                }
                else
                {
                    $faturamento = new Faturamento();
                }

                $faturamento->fill($value);

                $faturamento->save();

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

        return Redirect::to('/faturamento');
    }

    public function edit($id)
    {
        $user = Auth::user();

        $faturamento = Faturamento::find($id);

        if (!$faturamento) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/faturamento');
        }

        if (!Permissions::permissaoModerador($user) && $faturamento->r_auth != 0 && $faturamento->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/faturamento');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo faturamento'));
        }

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

        return view('faturamento.edit', [
            'faturamento' => $faturamento,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'templates_nome_do_template' => $templates_nome_do_template,

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

            // # -
            $store_venda_id = ((isset($store['venda_id']) and !empty($store['venda_id']))?$store['venda_id']:null);
            unset($store['venda_id']);
            // - #

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

            $faturamento = Faturamento::find($store['id']);

            if (!$faturamento) {

                \Session::flash('flash_error', 'Registro não encontrado!');

                return Redirect::to('/faturamento');
            }

            if (!Permissions::permissaoModerador($user) && $faturamento->r_auth != 0 && $faturamento->r_auth != $user->id)
            {
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/faturamento');
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

            $faturamento->r_auth = $r_auth;

            $faturamento->fill($store);

            $faturamento->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($faturamento, $relacionamento, 'Faturamento', 'faturamento');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($faturamento, $grids, 'faturamento');
            }


            // # Lista - Vendas ( Faturamento -| Cliente / Data Inicial / Data  Final )
            Vendas::where('tipo_de_venda',2)->where('faturamento',$faturamento->id) // str_pad($faturamento->id,7,'0',STR_PAD_LEFT)
                    ->update([
                        'faturamento'   =>  null,
                        'foi_faturado'  =>  null
                    ]);

            if($store_venda_id != null){
                foreach($store_venda_id as $v_id){
                    $Vendas = Vendas::find($v_id);
                    if($Vendas){
                        $Vendas->faturamento    = $faturamento->id; //str_pad($faturamento->id,7,'0',STR_PAD_LEFT);
                        $Vendas->foi_faturado   = 1;
                        $Vendas->save();
                    }
                }
            }

            // - #

            /*try {
                DB::statement("UPDATE
                vendas
                SET
                faturamento  = NULL,
                foi_faturado = NULL
                WHERE
                tipo_de_venda = 2 AND
                faturamento   = LPAD($faturamento->id,7,'0')");
                DB::statement("UPDATE
                vendas
                SET
                faturamento 	= LPAD($faturamento->id,7,'0'),
                foi_faturado    = 1
                WHERE
                tipo_de_venda = 2 AND
                $faturamento->cliente IS NOT NULL AND
                $faturamento->data_inicial IS NOT NULL AND
                $faturamento->data_final IS NOT NULL AND
                vendas.faturamento IS NULL AND
                (vendas.foi_faturado IS NULL || vendas.foi_faturado = '0') AND
                vendas.cliente = $faturamento->cliente AND
                DATE_FORMAT(vendas.data, '%Y-%m-%d') BETWEEN '$faturamento->data_inicial' AND '$faturamento->data_final'");
                DB::statement("UPDATE
                faturamento
                SET
                template 	     = 3,
                n_da_fatura   = LPAD($faturamento->id,7,'0')
                WHERE
                id = $faturamento->id");
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }*/

            // # -
            if(!\Request::get('modal-close')){
                Session::flash('flash_success', "Registro atualizado com sucesso!");
            }

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $faturamento->id . ' do módulo faturamento'));
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

        return Redirect::to('/faturamento/'.$faturamento->id.'/edit');
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

            $faturamento         = Faturamento::find($store['id']);

            if(!$faturamento){
                return response()->json(['error'=>'Registro não encontrado!'],400);
            }

            if(!Permissions::permissaoModerador($user) && $faturamento->r_auth != 0 && $faturamento->r_auth != $user->id){
                return response()->json(['error'=>'Você não tem permissão para executar esta ação!'],400);
            }

            $faturamento->update([
                $store['item_column']   => $store['item_target_id'],
                'r_auth'                => $r_auth,
            ]);

            DB::commit();

            try {

                    DB::statement("UPDATE
vendas
SET
faturamento  = NULL,
foi_faturado = NULL
WHERE
tipo_de_venda = 2 AND
faturamento   = LPAD($faturamento->id,7,'0')");
        DB::statement("UPDATE
vendas
SET
faturamento 	= LPAD($faturamento->id,7,'0'),
foi_faturado    = 1
WHERE
tipo_de_venda = 2 AND
$faturamento->cliente IS NOT NULL AND
$faturamento->data_inicial IS NOT NULL AND
$faturamento->data_final IS NOT NULL AND
vendas.faturamento IS NULL AND
(vendas.foi_faturado IS NULL || vendas.foi_faturado = '0') AND
vendas.cliente = $faturamento->cliente AND
DATE_FORMAT(vendas.data, '%Y-%m-%d') BETWEEN '$faturamento->data_inicial' AND '$faturamento->data_final'");
        DB::statement("UPDATE
faturamento
SET
template 	     = 3,
n_da_fatura   = LPAD($faturamento->id,7,'0')
WHERE
id = $faturamento->id");

            }catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $faturamento->id . ' do módulo faturamento'));
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
        $faturamento = Faturamento::find($id);

        try {
                Vendas::where('tipo_de_venda', 2)->where('faturamento', $faturamento->id)->update([ // str_pad($faturamento->id,7,'0',STR_PAD_LEFT)
                    'faturamento'   => null,
                    'foi_faturado'  => null
                ]);

                /*DB::statement("UPDATE
                vendas
                SET
                faturamento   = NULL,
                foi_faturado  = NULL
                WHERE
                tipo_de_venda = 2 AND
                faturamento   = LPAD($faturamento->id,7,'0')");*/

        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        return $this->controllerRepository::destroy(new Faturamento(), $id, 'faturamento');
    }

}
