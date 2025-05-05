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

use \App\Models\ContasAReceber;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;
use setasign\Fpdi\Fpdi;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\Filesystem\Filesystem;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;

use \App\Models\CadastroDeEmpresas;
use \App\Models\Clientes;
use \App\Models\Vendedores;
use \App\Models\FormasDePagamentos;

use App\Repositories\UploadRepository;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;

class ContasAReceberController extends Controller
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

        $contas_a_receber = ContasAReceber::getAll(500);
        $contas_a_receber_count  = ContasAReceber::getAllCount(); // # -

        $controller_model  = new ContasAReceber(); // # -

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo contas_a_receber'));
        }

        $cadastro_de_empresas_nome_fantas = CadastroDeEmpresas::list(10000, "nome_fantasia_empresa");

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

        return view('contas_a_receber.index', [
            'exibe_filtros' => 1,
            'contas_a_receber' => $contas_a_receber,
            'contas_a_receber_count' => $contas_a_receber_count,
            'controller_model'  => $controller_model, // # -
            'cadastro_de_empresas_nome_fantas' => $cadastro_de_empresas_nome_fantas,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,

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

            $contas_a_receber = ContasAReceber::with((new ContasAReceber())->filter_with)->select((new ContasAReceber())->getTable().'.*');

            $controller_model  = new ContasAReceber(); // # -

            // # Verificação / Filtro GRID para Inclusão
            if(isset($store['grid_fil'])){
                foreach($store['grid_fil'] as $GF_K => $GF){
                    if(in_array($GF_K,['operador','between'])){ continue; }
                    $exp_gf_k = explode('__', $GF_K, 2);
                    // ! Reforço*
                    if(count($exp_gf_k) == 2){
                        $contas_a_receber->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        if(count($GF)){
                            foreach($GF as $GF_C => $GF_V){
                                if(empty($GF_V)){ continue; }
                                if(isset($store['grid_fil']['operador'][$GF_K][$GF_C]) || isset($store['grid_fil']['between'][$GF_K][$GF_C])){
                                    if($store['grid_fil']['operador'][$GF_K][$GF_C] == 'contem'){
                                        $contas_a_receber->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                    }elseif($store['grid_fil']['operador'][$GF_K][$GF_C] == 'entre'){
                                        $contas_a_receber->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                    }else {
                                        $contas_a_receber->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                    }
                                }else {
                                    if(is_numeric($GF_V)){
                                        $contas_a_receber->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                    }else {
                                        $contas_a_receber->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
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
                            $contas_a_receber->where((new ContasAReceber())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $contas_a_receber->whereBetween((new ContasAReceber())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $contas_a_receber->where((new ContasAReceber())->getTable().'.'.$key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $contas_a_receber->where((new ContasAReceber())->getTable().'.'.$key, $store[$key]);
                        }
                        else
                        {
                            $contas_a_receber->where((new ContasAReceber())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
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
                $contas_a_receber = $contas_a_receber->orderBy((new ContasAReceber())->getTable().'.'.'id', 'DESC')->groupBy((new ContasAReceber())->getTable().'.id')->limit(500)->get();
            }
            else
            {
                $contas_a_receber = $contas_a_receber->where(function($q) use ($r_auth) {
                    $q->where((new ContasAReceber())->getTable().'.'.'r_auth', $r_auth)
                    ->orWhere((new ContasAReceber())->getTable().'.'.'r_auth', 0);
                })->orderBy((new ContasAReceber())->getTable().'.'.'id', 'DESC')->groupBy((new ContasAReceber())->getTable().'.id')->limit(500)->get();
            }

            // # -
            $contas_a_receber_count  = clone $contas_a_receber;
            $contas_a_receber_count  = $contas_a_receber_count->count((new ContasAReceber())->getTable().'.id');

            $cadastro_de_empresas_nome_fantas = CadastroDeEmpresas::list(10000, "nome_fantasia_empresa");

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

            return view('contas_a_receber.index', [
                'exibe_filtros' => 1,
                'contas_a_receber' => $contas_a_receber,
                'contas_a_receber_count' => $contas_a_receber_count,
                'controller_model'  => $controller_model, // # -
                'cadastro_de_empresas_nome_fantas' => $cadastro_de_empresas_nome_fantas,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,

            ]);

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao aplicar filtros: " . $e->getMessage());

        }

        return Redirect::to('/contas_a_receber');
    }

    public function create()
    {
        $user = Auth::user();

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de cadastro do módulo contas_a_receber'));
        }

        $cadastro_de_empresas_nome_fantas = CadastroDeEmpresas::list(10000, "nome_fantasia_empresa");

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

        return view('contas_a_receber.add', [
            'cadastro_de_empresas_nome_fantas' => $cadastro_de_empresas_nome_fantas,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,

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

            $contas_a_receber = new ContasAReceber();

if ($request->comprovante) {

if ($request->hasFile("comprovante")) {

    if (!in_array($request->comprovante->getClientOriginalExtension(), $this->upload)) {

        return back()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));

    }

    if ($request->comprovante->getSize() > $this->maxSize) {
        return back()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");

    }

    $file = base64_encode($request->comprovante->getClientOriginalName()) . "-" . uniqid().".".$request->comprovante->getClientOriginalExtension();

    if(env("FILESYSTEM_DRIVER") == "s3") {

        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("comprovante")));

    } else {

        $request->comprovante->move(public_path("images"), $file);

    }

    $store["comprovante"] = $file;

}
} else {
    $store["comprovante"] = null;

}

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

            $contas_a_receber->r_auth = $r_auth;

            $contas_a_receber->fill($store);

            $contas_a_receber->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($contas_a_receber, $relacionamento, 'ContasAReceber', 'contas_a_receber');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($contas_a_receber, $grids, 'contas_a_receber');
            }

            try {

                    DB::statement("INSERT INTO fluxo_de_caixa(data_do_recebimento, movimentacao, total_a_pagar, valor_recebido, data_atual, saldo_da_transacao, ghost_camp)

SELECT data_do_recebimento, 'Entrada', '0', valor_recebido, CURRENT_DATE(),valor_recebido, ' ===> '
FROM contas_a_receber
WHERE  id = '$contas_a_receber->id' AND status = 1");

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Session::flash('flash_success', "Cadastro realizado com sucesso!");

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' contas_a_receber: cadastrou ID: ' . $contas_a_receber->id));
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

        return Redirect::to('/contas_a_receber');

    }

    public function copy($id)
    {
        $user = Auth::user();

        $contas_a_receber = ContasAReceber::where('ID', $id)->first();

        if (env('IGNORE_COLUMNS')) {

            $hidden = env('IGNORE_COLUMNS');

            $contas_a_receber = $contas_a_receber->makeHidden(explode(',', $hidden));
        }

        $new = $contas_a_receber->replicate();

        $new->push();

        Session::flash('flash_success', "Linha duplicada com sucesso!");

        return Redirect::to("/contas_a_receber/$new->id/edit");
    }

    public function show($id)
    {
        $user = Auth::user();

        $contas_a_receber = ContasAReceber::find($id);

        if (!$contas_a_receber) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/contas_a_receber');
        }

        if (!Permissions::permissaoModerador($user) && $contas_a_receber->r_auth != 0 && $contas_a_receber->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/contas_a_receber');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou o ID #' . $id . ' do módulo contas_a_receber'));
        }

        $cadastro_de_empresas_nome_fantas = CadastroDeEmpresas::list(10000, "nome_fantasia_empresa");

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

        if (isset($contas_a_receber->Template) && $contas_a_receber->Template->template && 1) {

            $url = $this->templateRepository::parseTemplate(['contas_a_receber' => $contas_a_receber]);

            return \Redirect::intended($url);

        }
        else
        {
            return view('contas_a_receber.show', [
                'contas_a_receber' => $contas_a_receber,
                'cadastro_de_empresas_nome_fantas' => $cadastro_de_empresas_nome_fantas,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,

            ]);
        }
    }

    public function modal($id)
    {
        $user = Auth::user();

        $contas_a_receber = ContasAReceber::find($id);

        $cadastro_de_empresas_nome_fantas = CadastroDeEmpresas::list(10000, "nome_fantasia_empresa");

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

        return view('contas_a_receber.modal', [
            'contas_a_receber' => $contas_a_receber,
            'cadastro_de_empresas_nome_fantas' => $cadastro_de_empresas_nome_fantas,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,

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
                $contas_a_receber = ContasAReceber::where($subForm, $value);
                if(!empty($subForm2) and !empty($subForm2_value)){
                    $contas_a_receber->where($subForm2,$subForm2_value);
                }
                if(!empty($subForm3) and !empty($subForm3_value)){
                    $contas_a_receber->where($subForm3,$subForm3_value);
                }
                $contas_a_receber = $contas_a_receber->get();
            }
            else
            {
               $contas_a_receber = ContasAReceber::where($subForm, $value)->where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
               });
               if(!empty($subForm2) and !empty($subForm2_value)){
                   $contas_a_receber->where($subForm2,$subForm2_value);
               }
               if(!empty($subForm3) and !empty($subForm3_value)){
                   $contas_a_receber->where($subForm3,$subForm3_value);
               }
               $contas_a_receber = $contas_a_receber->get();
            }
        }
        else
        {
            if (Permissions::permissaoModerador($user))
            {
                $contas_a_receber = ContasAReceber::find($value);
            }
            else
            {
               $contas_a_receber = ContasAReceber::where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->find($value);
            }
        }

        return Response::json($contas_a_receber);
    }

    public function pdf($id)
    {
        $user = Auth::user();

        $contas_a_receber = ContasAReceber::find($id);

        $cadastro_de_empresas_nome_fantas = CadastroDeEmpresas::list(10000, "nome_fantasia_empresa");

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

        return view('contas_a_receber.pdf', [
            'contas_a_receber' => $contas_a_receber,
            'cadastro_de_empresas_nome_fantas' => $cadastro_de_empresas_nome_fantas,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,

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
                    $contas_a_receber = ContasAReceber::find($value['id']);
                }
                else
                {
                    $contas_a_receber = new ContasAReceber();
                }

                $contas_a_receber->fill($value);

                $contas_a_receber->save();

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

        return Redirect::to('/contas_a_receber');
    }

    public function edit($id)
    {
        $user = Auth::user();

        $contas_a_receber = ContasAReceber::find($id);

        if (!$contas_a_receber) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/contas_a_receber');
        }

        if (!Permissions::permissaoModerador($user) && $contas_a_receber->r_auth != 0 && $contas_a_receber->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/contas_a_receber');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo contas_a_receber'));
        }

        $cadastro_de_empresas_nome_fantas = CadastroDeEmpresas::list(10000, "nome_fantasia_empresa");

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

        return view('contas_a_receber.edit', [
            'contas_a_receber' => $contas_a_receber,
            'cadastro_de_empresas_nome_fantas' => $cadastro_de_empresas_nome_fantas,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,

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

            $contas_a_receber = ContasAReceber::find($store['id']);

            if (!$contas_a_receber) {

                \Session::flash('flash_error', 'Registro não encontrado!');

                return Redirect::to('/contas_a_receber');
            }

            if (!Permissions::permissaoModerador($user) && $contas_a_receber->r_auth != 0 && $contas_a_receber->r_auth != $user->id)
            {
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/contas_a_receber');
            }

if ($request->comprovante) {

if ($request->hasFile("comprovante")) {

    if (!in_array($request->comprovante->getClientOriginalExtension(), $this->upload)) {

        return back()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));

    }

    if ($request->comprovante->getSize() > $this->maxSize) {
        return back()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");

    }

    $file = base64_encode($request->comprovante->getClientOriginalName()) . "-" . uniqid().".".$request->comprovante->getClientOriginalExtension();

    if(env("FILESYSTEM_DRIVER") == "s3") {

        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("comprovante")));

    } else {

        $request->comprovante->move(public_path("images"), $file);

    }

    $store["comprovante"] = $file;

}
} else {
    $store["comprovante"] = null;

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

            $contas_a_receber->r_auth = $r_auth;

            $contas_a_receber->fill($store);

            $contas_a_receber->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($contas_a_receber, $relacionamento, 'ContasAReceber', 'contas_a_receber');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($contas_a_receber, $grids, 'contas_a_receber');
            }

            try {

                    DB::statement("INSERT INTO fluxo_de_caixa(data_do_recebimento, movimentacao, total_a_pagar, valor_recebido, data_atual, saldo_da_transacao, ghost_camp)

SELECT data_do_recebimento, 'Entrada', '0', valor_recebido, CURRENT_DATE(),valor_recebido, ' ===> '
FROM contas_a_receber
WHERE  id = '$contas_a_receber->id' AND status = 1");

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            // # -
            if(!\Request::get('modal-close')){
                Session::flash('flash_success', "Registro atualizado com sucesso!");
            }

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $contas_a_receber->id . ' do módulo contas_a_receber'));
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

        return Redirect::to('/contas_a_receber');
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

            $contas_a_receber         = ContasAReceber::find($store['id']);

            if(!$contas_a_receber){
                return response()->json(['error'=>'Registro não encontrado!'],400);
            }

            if(!Permissions::permissaoModerador($user) && $contas_a_receber->r_auth != 0 && $contas_a_receber->r_auth != $user->id){
                return response()->json(['error'=>'Você não tem permissão para executar esta ação!'],400);
            }

            $contas_a_receber->update([
                $store['item_column']   => $store['item_target_id'],
                'r_auth'                => $r_auth,
            ]);

            DB::commit();

            try {

                    DB::statement("INSERT INTO fluxo_de_caixa(data_do_recebimento, movimentacao, total_a_pagar, valor_recebido, data_atual, saldo_da_transacao, ghost_camp)

SELECT data_do_recebimento, 'Entrada', '0', valor_recebido, CURRENT_DATE(),valor_recebido, ' ===> '
FROM contas_a_receber
WHERE  id = '$contas_a_receber->id' AND status = 1");

            }catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $contas_a_receber->id . ' do módulo contas_a_receber'));
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
        $contas_a_receber = ContasAReceber::find($id);

        try {

                DB::statement("INSERT INTO fluxo_de_caixa(data_do_recebimento, movimentacao, total_a_pagar, valor_recebido, data_atual, saldo_da_transacao, ghost_camp)

SELECT data_do_recebimento, 'Entrada', '0', valor_recebido, CURRENT_DATE(),valor_recebido, ' ===> '
FROM contas_a_receber
WHERE  id = '$contas_a_receber->id' AND status = 1");

        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        return $this->controllerRepository::destroy(new ContasAReceber(), $id, 'contas_a_receber');
    }

}
