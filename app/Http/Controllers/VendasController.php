<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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

use \App\Models\Vendas;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;
use setasign\Fpdi\Fpdi;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\Filesystem\Filesystem;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;

use \App\Models\Clientes;
use \App\Models\Vendedores;
use \App\Models\Produtos;
use \App\Models\Servicos;
use \App\Models\Fornecedores;
use \App\Models\Companhias;
use \App\Models\Trechos;
use \App\Models\Passageiro;
use \App\Models\FormasDePagamentos;
use \App\Models\Templates;

use App\Repositories\UploadRepository;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;

class VendasController extends Controller
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

        $vendas = Vendas::getAll(500);
        $vendas_count  = Vendas::getAllCount(); // # -

        $controller_model  = new Vendas(); // # -

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo vendas'));
        }


        $RV_tarifa['valor_tarifa']          = Vendas::sum('valor_tarifa');
        $RV_tarifa['tx_embarque']           = Vendas::sum('tx_embarque');
        $RV_tarifa['outras_taxas']          = Vendas::sum('outras_taxas');
        $RV_tarifa['desconto']              = Vendas::sum('desconto');
        $RV_tarifa['comissao']              = Vendas::sum('comissao');
        $RV_tarifa['incentivo']             = Vendas::sum('incentivo');
        $RV_tarifa['valor_total_sem_taxa']  = Vendas::selectRaw('SUM(COALESCE(valor_tarifa,0) + COALESCE(comissao,0) + COALESCE(incentivo,0) - COALESCE(desconto,0)) AS total')->first()->total;
        $RV_tarifa['valor_total']           = Vendas::selectRaw('SUM(COALESCE(valor_tarifa,0) + COALESCE(tx_embarque,0) + COALESCE(outras_taxas,0) + COALESCE(comissao,0) + COALESCE(incentivo,0) - COALESCE(desconto,0)) AS total')->first()->total;
        $RV_tarifa['saldo_a_pagar']         = Vendas::selectRaw('SUM(CAST(REPLACE(REPLACE(saldo_a_pagar, ".", ""), ",", ".") AS DECIMAL(10,2))) AS total')->first()->total;
        //$RV_tarifa['vlr_pago_']             = Vendas::selectRaw('SUM(COALESCE(valor_tarifa,0) + COALESCE(acrescimo_,0) - COALESCE(desconto_,0)) AS total')->first()->total;
        $RV_tarifa['vlr_pago_']             = Vendas::selectRaw('SUM(COALESCE(valor_tarifa,0) + COALESCE(tx_embarque,0) + COALESCE(outras_taxas,0) + COALESCE(comissao,0) + COALESCE(incentivo,0) - COALESCE(desconto,0)) AS total')->first()->total;
        $RV_tarifa['valor_a_faturar']       = Vendas::selectRaw('SUM(COALESCE(valor_tarifa,0) + COALESCE(tx_embarque,0) + COALESCE(comissao,0) - COALESCE(desconto,0)) AS total')->where('tipo_de_venda',2)->whereNull('faturamento')->first()->total;

        $clientes_nome_do_cliente           = Clientes::list(10000, "nome_do_cliente");
        $vendedores_nome_do_vendedor        = Vendedores::list(10000, "nome_do_vendedor");
        $produtos_produto                   = Produtos::list(10000, "produto");
        $servicos_servico                   = Servicos::list(10000, "servico");
        $fornecedores_fornecedor            = Fornecedores::list(10000, "fornecedor");
        $companhias_companhia               = Companhias::list(10000, "companhia");
        $trechos_trechos                    = Trechos::list(10000, "trechos");
        $passageiro_nome                    = Passageiro::list(10000, "nome");
        $formas_de_pagamentos_forma_de_pa   = FormasDePagamentos::list(10000, "forma_de_pagamento");
        $templates_nome_do_template         = Templates::list(10000, "nome_do_template");

        return view('vendas.index', [
            'exibe_filtros'                     => 1,
            'vendas'                            => $vendas,
            'vendas_count'                      => $vendas_count, // # -
            'controller_model'                  => $controller_model, // # -
            'clientes_nome_do_cliente'          => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor'       => $vendedores_nome_do_vendedor,
            'produtos_produto'                  => $produtos_produto,
            'servicos_servico'                  => $servicos_servico,
            'fornecedores_fornecedor'           => $fornecedores_fornecedor,
            'companhias_companhia'              => $companhias_companhia,
            'trechos_trechos'                   => $trechos_trechos,
            'passageiro_nome'                   => $passageiro_nome,
            'formas_de_pagamentos_forma_de_pa'  => $formas_de_pagamentos_forma_de_pa,
            'templates_nome_do_template'        => $templates_nome_do_template,
            'RV_tarifa'                         => $RV_tarifa,

        ]);
    }

    public function filter(Request $request)
    {
        try {

            $user               = Auth::user();
            if ($user) {
                $r_auth = $user->id;
            }
            $store              = $request->all();
            $store              = array_filter($store);

            //$vendas             = Vendas::with((new Vendas())->filter_with)->select((new Vendas())->getTable().'.*');
            $vendas             = Vendas::with([
                'Cliente',
                'Cliente.Documentos',
                'Vendedor',
                'Produto',
                'Servico',
                'Fornecedor',
                'Companhia',
                'Trecho',
                'Template',
                'VendasGridPassageiros',
                'VendasGridPassageiros.Passageiros',
                'VendasGridPagamentos',
                'VendasGridPagamentos.FormaDePagamento'
            ])->select((new Vendas())->getTable().'.*');

            // # Relatório
            $RV_tarifa['valor_tarifa']          = (new Vendas)->newQuery();
            $RV_tarifa['tx_embarque']           = (new Vendas)->newQuery();
            $RV_tarifa['outras_taxas']          = (new Vendas)->newQuery();
            $RV_tarifa['desconto']              = (new Vendas)->newQuery();
            $RV_tarifa['comissao']              = (new Vendas)->newQuery();
            $RV_tarifa['incentivo']             = (new Vendas)->newQuery();
            $RV_tarifa['valor_total_sem_taxa']  = Vendas::selectRaw('SUM(COALESCE(valor_tarifa,0) + COALESCE(comissao,0) + COALESCE(incentivo,0) - COALESCE(desconto,0)) AS total');
            $RV_tarifa['valor_total']           = Vendas::selectRaw('SUM(COALESCE(valor_tarifa,0) + COALESCE(tx_embarque,0) + COALESCE(outras_taxas,0) + COALESCE(comissao,0) + COALESCE(incentivo,0) - COALESCE(desconto,0)) AS total');
            $RV_tarifa['saldo_a_pagar']         = Vendas::selectRaw('SUM(CAST(REPLACE(REPLACE(saldo_a_pagar, ".", ""), ",", ".") AS DECIMAL(10,2))) AS total')->first()->total;
            //$RV_tarifa['vlr_pago_']             = Vendas::selectRaw('SUM(COALESCE(valor_tarifa,0) + COALESCE(acrescimo_,0) - COALESCE(desconto_,0)) AS total');
            $RV_tarifa['vlr_pago_']             = Vendas::selectRaw('SUM(COALESCE(valor_tarifa,0) + COALESCE(tx_embarque,0) + COALESCE(outras_taxas,0) + COALESCE(comissao,0) + COALESCE(incentivo,0) - COALESCE(desconto,0)) AS total');
            $RV_tarifa['valor_a_faturar']       = Vendas::selectRaw('SUM(COALESCE(valor_tarifa,0) + COALESCE(tx_embarque,0) + COALESCE(comissao,0) - COALESCE(desconto,0)) AS total')->where('tipo_de_venda',2)->whereNull('faturamento');
            // - #

            $controller_model                   = new Vendas(); // # -

            // # Verificação / Filtro GRID para Inclusão
            if(isset($store['grid_fil'])){
                foreach($store['grid_fil'] as $GF_K => $GF){
                    if(in_array($GF_K,['operador','between'])){ continue; }
                    $exp_gf_k = explode('__', $GF_K, 2);
                    // ! Reforço*
                    if(count($exp_gf_k) == 2){
                        $vendas->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        $RV_tarifa['valor_tarifa']->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        $RV_tarifa['tx_embarque']->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        $RV_tarifa['outras_taxas']->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        $RV_tarifa['desconto']->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        $RV_tarifa['comissao']->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        $RV_tarifa['incentivo']->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        $RV_tarifa['valor_total_sem_taxa']->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        $RV_tarifa['valor_total']->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        $RV_tarifa['vlr_pago_']->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        $RV_tarifa['valor_a_faturar']->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                        if(count($GF)){
                            foreach($GF as $GF_C => $GF_V){
                                if(empty($GF_V)){ continue; }
                                if(isset($store['grid_fil']['operador'][$GF_K][$GF_C]) || isset($store['grid_fil']['between'][$GF_K][$GF_C])){
                                    if($store['grid_fil']['operador'][$GF_K][$GF_C] == 'contem'){
                                        $vendas->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        $RV_tarifa['valor_tarifa']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        $RV_tarifa['tx_embarque']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        $RV_tarifa['outras_taxas']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        $RV_tarifa['desconto']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        $RV_tarifa['comissao']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        $RV_tarifa['incentivo']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        $RV_tarifa['valor_total_sem_taxa']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        $RV_tarifa['valor_total']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        $RV_tarifa['vlr_pago_']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        $RV_tarifa['valor_a_faturar']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                    }elseif($store['grid_fil']['operador'][$GF_K][$GF_C] == 'entre'){
                                        $vendas->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                        $RV_tarifa['valor_tarifa']->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                        $RV_tarifa['tx_embarque']->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                        $RV_tarifa['outras_taxas']->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                        $RV_tarifa['desconto']->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                        $RV_tarifa['comissao']->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                        $RV_tarifa['incentivo']->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                        $RV_tarifa['valor_total_sem_taxa']->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                        $RV_tarifa['valor_total']->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                        $RV_tarifa['vlr_pago_']->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                        $RV_tarifa['valor_a_faturar']->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$store['grid_fil']['between'][$GF_K][$GF_C]]);
                                    }else {
                                        $vendas->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                        $RV_tarifa['valor_tarifa']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                        $RV_tarifa['tx_embarque']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                        $RV_tarifa['outras_taxas']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                        $RV_tarifa['desconto']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                        $RV_tarifa['comissao']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                        $RV_tarifa['incentivo']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                        $RV_tarifa['valor_total_sem_taxa']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                        $RV_tarifa['valor_total']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                        $RV_tarifa['vlr_pago_']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                        $RV_tarifa['valor_a_faturar']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $store['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                    }
                                }else {
                                    if(is_numeric($GF_V)){
                                        $vendas->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        $RV_tarifa['valor_tarifa']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        $RV_tarifa['tx_embarque']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        $RV_tarifa['outras_taxas']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        $RV_tarifa['desconto']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        $RV_tarifa['comissao']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        $RV_tarifa['incentivo']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        $RV_tarifa['valor_total_sem_taxa']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        $RV_tarifa['valor_total']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        $RV_tarifa['vlr_pago_']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        $RV_tarifa['valor_a_faturar']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                    }else {
                                        if(gettype($GF_V) == 'array'){
                                            $GF_V = array_filter($GF_V);
                                            $vendas->whereIn($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                            $RV_tarifa['valor_tarifa']->whereIn($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                            $RV_tarifa['tx_embarque']->whereIn($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                            $RV_tarifa['outras_taxas']->whereIn($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                            $RV_tarifa['desconto']->whereIn($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                            $RV_tarifa['comissao']->whereIn($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                            $RV_tarifa['incentivo']->whereIn($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                            $RV_tarifa['valor_total_sem_taxa']->whereIn($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                            $RV_tarifa['valor_total']->whereIn($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                            $RV_tarifa['vlr_pago_']->whereIn($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                            $RV_tarifa['valor_a_faturar']->whereIn($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        }else {
                                            $vendas->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                            $RV_tarifa['valor_tarifa']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                            $RV_tarifa['tx_embarque']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                            $RV_tarifa['outras_taxas']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                            $RV_tarifa['desconto']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                            $RV_tarifa['comissao']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                            $RV_tarifa['incentivo']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                            $RV_tarifa['valor_total_sem_taxa']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                            $RV_tarifa['valor_total']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                            $RV_tarifa['vlr_pago_']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                            $RV_tarifa['valor_a_faturar']->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        }
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
                            $vendas->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                            $RV_tarifa['valor_tarifa']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                            $RV_tarifa['tx_embarque']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                            $RV_tarifa['outras_taxas']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                            $RV_tarifa['desconto']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                            $RV_tarifa['comissao']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                            $RV_tarifa['incentivo']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                            $RV_tarifa['valor_total_sem_taxa']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                            $RV_tarifa['valor_total']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                            $RV_tarifa['vlr_pago_']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                            $RV_tarifa['valor_a_faturar']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $vendas->whereBetween((new Vendas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                            $RV_tarifa['valor_tarifa']->whereBetween((new Vendas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                            $RV_tarifa['tx_embarque']->whereBetween((new Vendas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                            $RV_tarifa['outras_taxas']->whereBetween((new Vendas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                            $RV_tarifa['desconto']->whereBetween((new Vendas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                            $RV_tarifa['comissao']->whereBetween((new Vendas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                            $RV_tarifa['incentivo']->whereBetween((new Vendas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                            $RV_tarifa['valor_total_sem_taxa']->whereBetween((new Vendas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                            $RV_tarifa['valor_total']->whereBetween((new Vendas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                            $RV_tarifa['vlr_pago_']->whereBetween((new Vendas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                            $RV_tarifa['valor_a_faturar']->whereBetween((new Vendas())->getTable().'.'.$key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $vendas->where((new Vendas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                            $RV_tarifa['valor_tarifa']->where((new Vendas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                            $RV_tarifa['tx_embarque']->where((new Vendas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                            $RV_tarifa['outras_taxas']->where((new Vendas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                            $RV_tarifa['desconto']->where((new Vendas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                            $RV_tarifa['comissao']->where((new Vendas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                            $RV_tarifa['incentivo']->where((new Vendas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                            $RV_tarifa['valor_total_sem_taxa']->where((new Vendas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                            $RV_tarifa['valor_total']->where((new Vendas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                            $RV_tarifa['vlr_pago_']->where((new Vendas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                            $RV_tarifa['valor_a_faturar']->where((new Vendas())->getTable().'.'.$key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $vendas->where((new Vendas())->getTable().'.'.$key, $store[$key]);
                            $RV_tarifa['valor_tarifa']->where((new Vendas())->getTable().'.'.$key, $store[$key]);
                            $RV_tarifa['tx_embarque']->where((new Vendas())->getTable().'.'.$key, $store[$key]);
                            $RV_tarifa['outras_taxas']->where((new Vendas())->getTable().'.'.$key, $store[$key]);
                            $RV_tarifa['desconto']->where((new Vendas())->getTable().'.'.$key, $store[$key]);
                            $RV_tarifa['comissao']->where((new Vendas())->getTable().'.'.$key, $store[$key]);
                            $RV_tarifa['incentivo']->where((new Vendas())->getTable().'.'.$key, $store[$key]);
                            $RV_tarifa['valor_total_sem_taxa']->where((new Vendas())->getTable().'.'.$key, $store[$key]);
                            $RV_tarifa['valor_total']->where((new Vendas())->getTable().'.'.$key, $store[$key]);
                            $RV_tarifa['vlr_pago_']->where((new Vendas())->getTable().'.'.$key, $store[$key]);
                            $RV_tarifa['valor_a_faturar']->where((new Vendas())->getTable().'.'.$key, $store[$key]);
                        }
                        else
                        {
                            if(gettype($store[$key]) == 'array'){
                                $store[$key] = array_filter($store[$key]);
                                $vendas->whereIn((new Vendas())->getTable().'.'.$key, $store[$key]);
                                $RV_tarifa['valor_tarifa']->whereIn((new Vendas())->getTable().'.'.$key, $store[$key]);
                                $RV_tarifa['tx_embarque']->whereIn((new Vendas())->getTable().'.'.$key, $store[$key]);
                                $RV_tarifa['outras_taxas']->whereIn((new Vendas())->getTable().'.'.$key, $store[$key]);
                                $RV_tarifa['desconto']->whereIn((new Vendas())->getTable().'.'.$key, $store[$key]);
                                $RV_tarifa['comissao']->whereIn((new Vendas())->getTable().'.'.$key, $store[$key]);
                                $RV_tarifa['incentivo']->whereIn((new Vendas())->getTable().'.'.$key, $store[$key]);
                                $RV_tarifa['valor_total_sem_taxa']->whereIn((new Vendas())->getTable().'.'.$key, $store[$key]);
                                $RV_tarifa['valor_total']->whereIn((new Vendas())->getTable().'.'.$key, $store[$key]);
                                $RV_tarifa['vlr_pago_']->whereIn((new Vendas())->getTable().'.'.$key, $store[$key]);
                                $RV_tarifa['valor_a_faturar']->whereIn((new Vendas())->getTable().'.'.$key, $store[$key]);
                            }else {
                                $vendas->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                                $RV_tarifa['valor_tarifa']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                                $RV_tarifa['tx_embarque']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                                $RV_tarifa['outras_taxas']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                                $RV_tarifa['desconto']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                                $RV_tarifa['comissao']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                                $RV_tarifa['incentivo']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                                $RV_tarifa['valor_total_sem_taxa']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                                $RV_tarifa['valor_total']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                                $RV_tarifa['vlr_pago_']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                                $RV_tarifa['valor_a_faturar']->where((new Vendas())->getTable().'.'.$key, "LIKE", "%" . $store[$key] . "%");
                            }
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
                $vendas = $vendas->orderBy((new Vendas())->getTable().'.'.'id', 'DESC')->groupBy((new Vendas())->getTable().'.id')->limit(500)->get();
            }
            else
            {
                $vendas = $vendas->where(function($q) use ($r_auth) {
                    $q->where((new Vendas())->getTable().'.'.'r_auth', $r_auth)
                    ->orWhere((new Vendas())->getTable().'.'.'r_auth', 0);
                })->orderBy((new Vendas())->getTable().'.'.'id', 'DESC')->groupBy((new Vendas())->getTable().'.id')->limit(500)->get();
            }

            // # -
            $vendas_count  = clone $vendas;
            $vendas_count  = $vendas_count->count((new Vendas())->getTable().'.id');

            // # Relatório -
            $RV_tarifa['valor_tarifa']          = $RV_tarifa['valor_tarifa']->sum('valor_tarifa');
            $RV_tarifa['tx_embarque']           = $RV_tarifa['tx_embarque']->sum('tx_embarque');
            $RV_tarifa['outras_taxas']          = $RV_tarifa['outras_taxas']->sum('outras_taxas');
            $RV_tarifa['desconto']              = $RV_tarifa['desconto']->sum('desconto');
            $RV_tarifa['comissao']              = $RV_tarifa['comissao']->sum('comissao');
            $RV_tarifa['incentivo']             = $RV_tarifa['incentivo']->sum('incentivo');
            $RV_tarifa['valor_total_sem_taxa']  = $RV_tarifa['valor_total_sem_taxa']->first()->total;
            $RV_tarifa['valor_total']           = $RV_tarifa['valor_total']->first()->total;
            $RV_tarifa['vlr_pago_']             = $RV_tarifa['vlr_pago_']->first()->total;
            $RV_tarifa['valor_a_faturar']       = $RV_tarifa['valor_a_faturar']->first()->total;
            // - #

            $clientes_nome_do_cliente           = Clientes::list(10000, "nome_do_cliente");
            $vendedores_nome_do_vendedor        = Vendedores::list(10000, "nome_do_vendedor");
            $produtos_produto                   = Produtos::list(10000, "produto");
            $servicos_servico                   = Servicos::list(10000, "servico");
            $fornecedores_fornecedor            = Fornecedores::list(10000, "fornecedor");
            $companhias_companhia               = Companhias::list(10000, "companhia");
            $trechos_trechos                    = Trechos::list(10000, "trechos");
            $passageiro_nome                    = Passageiro::list(10000, "nome");
            $formas_de_pagamentos_forma_de_pa   = FormasDePagamentos::list(10000, "forma_de_pagamento");
            $templates_nome_do_template         = Templates::list(10000, "nome_do_template");

            return view('vendas.index', [
                'exibe_filtros'                     => 1,
                'vendas'                            => $vendas,
                'vendas_count'                      => $vendas_count, // # -
                'controller_model'                  => $controller_model, // # -
                'clientes_nome_do_cliente'          => $clientes_nome_do_cliente,
                'vendedores_nome_do_vendedor'       => $vendedores_nome_do_vendedor,
                'produtos_produto'                  => $produtos_produto,
                'servicos_servico'                  => $servicos_servico,
                'fornecedores_fornecedor'           => $fornecedores_fornecedor,
                'companhias_companhia'              => $companhias_companhia,
                'trechos_trechos'                   => $trechos_trechos,
                'passageiro_nome'                   => $passageiro_nome,
                'formas_de_pagamentos_forma_de_pa'  => $formas_de_pagamentos_forma_de_pa,
                'templates_nome_do_template'        => $templates_nome_do_template,
                'RV_tarifa'                         => $RV_tarifa,
            ]);

        }catch (Exception $e) {
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao aplicar filtros: " . $e->getMessage());
        }

        return Redirect::to('/vendas');
    }

    public function create()
    {

        $user = Auth::user();

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de cadastro do módulo vendas'));
        }

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $produtos_produto = Produtos::list(10000, "produto");

        $servicos_servico = Servicos::list(10000, "servico");

        $fornecedores_fornecedor = Fornecedores::list(10000, "fornecedor");

        $companhias_companhia = Companhias::list(10000, "companhia");

        $trechos_trechos = Trechos::list(10000, "trechos");

        $passageiro_nome = Passageiro::list(10000, "nome");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

        // # -
        return view('vendas.add', [
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'produtos_produto' => $produtos_produto,
            'servicos_servico' => $servicos_servico,
            'fornecedores_fornecedor' => $fornecedores_fornecedor,
            'companhias_companhia' => $companhias_companhia,
            'trechos_trechos' => $trechos_trechos,
            'passageiro_nome' => $passageiro_nome,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,
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

            $vendas = new Vendas();

            $store["valor_tarifa"] = ((!is_null($store["valor_tarifa"]) && !empty($store["valor_tarifa"])) ? $store["valor_tarifa"] : 0);
$store["tx_embarque"] = ((!is_null($store["tx_embarque"]) && !empty($store["tx_embarque"])) ? $store["tx_embarque"] : 0);
$store["outras_taxas"] = ((!is_null($store["outras_taxas"]) && !empty($store["outras_taxas"])) ? $store["outras_taxas"] : 0);
$store["desconto"] = ((!is_null($store["desconto"]) && !empty($store["desconto"])) ? $store["desconto"] : 0);
$store["comissao"] = ((!is_null($store["comissao"]) && !empty($store["comissao"])) ? $store["comissao"] : 0);
            try {
                $store["valor_total"] = number_format($store["valor_tarifa"] + $store["tx_embarque"] + $store["outras_taxas"] - $store["desconto"] + $store["comissao"], 2, ',', '.');
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

//$store["valor_"] = ((!is_null($store["valor_"]) && !empty($store["valor_"])) ? $store["valor_"] : 0);
//$store["acrescimo_"] = ((!is_null($store["acrescimo_"]) && !empty($store["acrescimo_"])) ? $store["acrescimo_"] : 0);
//$store["desconto_"] = ((!is_null($store["desconto_"]) && !empty($store["desconto_"])) ? $store["desconto_"] : 0);
$store["incentivo"] = ((!is_null($store["incentivo"]) && !empty($store["incentivo"])) ? $store["incentivo"] : 0);
            try {
                //$store["vlr_pago_"] = $store["valor_"] + $store["acrescimo_"] - $store["desconto_"];
                //$store["vlr_pago_"] = $store["acrescimo_"] - $store["desconto_"];
            } catch (Exception $e) {
                Log::info($e->getMessage());
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

            $vendas->r_auth = $r_auth;

            $vendas->fill($store);

            $vendas->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($vendas, $relacionamento, 'Vendas', 'vendas');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($vendas, $grids, 'vendas');
            }

            try {

                    DB::statement("UPDATE
vendas
SET
faturamento  = NULL,
foi_faturado = NULL,
template 	 = 2
WHERE
tipo_de_venda = 1");
        DB::statement("UPDATE
vendas
SET
template 	 = NULL
WHERE
tipo_de_venda = 2");

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Session::flash('flash_success', "Cadastro realizado com sucesso!");

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' vendas: cadastrou ID: ' . $vendas->id));
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

        return Redirect::to('/vendas');

    }

    public function copy($id)
    {
        $user = Auth::user();

        $vendas = Vendas::where('ID', $id)->first();

        if (env('IGNORE_COLUMNS')) {

            $hidden = env('IGNORE_COLUMNS');

            $vendas = $vendas->makeHidden(explode(',', $hidden));
        }

        $new = $vendas->replicate();

        $new->push();

        Session::flash('flash_success', "Linha duplicada com sucesso!");

        return Redirect::to("/vendas/$new->id/edit");
    }

    public function show($id)
    {
        $user = Auth::user();

        $vendas = Vendas::find($id);

        if (!$vendas) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/vendas');
        }

        if (!Permissions::permissaoModerador($user) && $vendas->r_auth != 0 && $vendas->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/vendas');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou o ID #' . $id . ' do módulo vendas'));
        }

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $produtos_produto = Produtos::list(10000, "produto");

        $servicos_servico = Servicos::list(10000, "servico");

        $fornecedores_fornecedor = Fornecedores::list(10000, "fornecedor");

        $companhias_companhia = Companhias::list(10000, "companhia");

        $trechos_trechos = Trechos::list(10000, "trechos");

        $passageiro_nome = Passageiro::list(10000, "nome");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

        if (isset($vendas->Template) && $vendas->Template->template && 1) {

            $url = $this->templateRepository::parseTemplate(['vendas' => $vendas]);

            return \Redirect::intended($url);

        }
        else
        {
            return view('vendas.show', [
                'vendas' => $vendas,
                'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'produtos_produto' => $produtos_produto,
            'servicos_servico' => $servicos_servico,
            'fornecedores_fornecedor' => $fornecedores_fornecedor,
            'companhias_companhia' => $companhias_companhia,
            'trechos_trechos' => $trechos_trechos,
            'passageiro_nome' => $passageiro_nome,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,
            'templates_nome_do_template' => $templates_nome_do_template,

            ]);
        }
    }

    public function modal($id)
    {
        $user = Auth::user();

        $vendas = Vendas::find($id);

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $produtos_produto = Produtos::list(10000, "produto");

        $servicos_servico = Servicos::list(10000, "servico");

        $fornecedores_fornecedor = Fornecedores::list(10000, "fornecedor");

        $companhias_companhia = Companhias::list(10000, "companhia");

        $trechos_trechos = Trechos::list(10000, "trechos");

        $passageiro_nome = Passageiro::list(10000, "nome");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

        return view('vendas.modal', [
            'vendas' => $vendas,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'produtos_produto' => $produtos_produto,
            'servicos_servico' => $servicos_servico,
            'fornecedores_fornecedor' => $fornecedores_fornecedor,
            'companhias_companhia' => $companhias_companhia,
            'trechos_trechos' => $trechos_trechos,
            'passageiro_nome' => $passageiro_nome,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,
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
                $vendas = Vendas::where($subForm, $value);
                if(!empty($subForm2) and !empty($subForm2_value)){
                    $vendas->where($subForm2,$subForm2_value);
                }
                if(!empty($subForm3) and !empty($subForm3_value)){
                    $vendas->where($subForm3,$subForm3_value);
                }
                $vendas = $vendas->get();
            }
            else
            {
               $vendas = Vendas::where($subForm, $value)->where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
               });
               if(!empty($subForm2) and !empty($subForm2_value)){
                   $vendas->where($subForm2,$subForm2_value);
               }
               if(!empty($subForm3) and !empty($subForm3_value)){
                   $vendas->where($subForm3,$subForm3_value);
               }
               $vendas = $vendas->get();
            }
        }
        else
        {
            if (Permissions::permissaoModerador($user))
            {
                $vendas = Vendas::find($value);
            }
            else
            {
               $vendas = Vendas::where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->find($value);
            }
        }

        return Response::json($vendas);
    }

    public function pdf($id)
    {
        $user = Auth::user();

        $vendas = Vendas::find($id);

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $produtos_produto = Produtos::list(10000, "produto");

        $servicos_servico = Servicos::list(10000, "servico");

        $fornecedores_fornecedor = Fornecedores::list(10000, "fornecedor");

        $companhias_companhia = Companhias::list(10000, "companhia");

        $trechos_trechos = Trechos::list(10000, "trechos");

        $passageiro_nome = Passageiro::list(10000, "nome");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

        return view('vendas.pdf', [
            'vendas' => $vendas,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'produtos_produto' => $produtos_produto,
            'servicos_servico' => $servicos_servico,
            'fornecedores_fornecedor' => $fornecedores_fornecedor,
            'companhias_companhia' => $companhias_companhia,
            'trechos_trechos' => $trechos_trechos,
            'passageiro_nome' => $passageiro_nome,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,
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
                    $vendas = Vendas::find($value['id']);
                }
                else
                {
                    $vendas = new Vendas();
                }

                $vendas->fill($value);

                $vendas->save();

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

        return Redirect::to('/vendas');
    }

    public function edit($id)
    {
        $user = Auth::user();

        $vendas = Vendas::find($id);

        if (!$vendas) {

            \Session::flash('flash_error', 'Registro não encontrado!');

            return Redirect::to('/vendas');
        }

        if (!Permissions::permissaoModerador($user) && $vendas->r_auth != 0 && $vendas->r_auth != $user->id)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return Redirect::to('/vendas');
        }

        if ($user) {
            Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo vendas'));
        }

        $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $vendedores_nome_do_vendedor = Vendedores::list(10000, "nome_do_vendedor");

        $produtos_produto = Produtos::list(10000, "produto");

        $servicos_servico = Servicos::list(10000, "servico");

        $fornecedores_fornecedor = Fornecedores::list(10000, "fornecedor");

        $companhias_companhia = Companhias::list(10000, "companhia");

        $trechos_trechos = Trechos::list(10000, "trechos");

        $passageiro_nome = Passageiro::list(10000, "nome");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

        return view('vendas.edit', [
            'vendas' => $vendas,
            'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
            'vendedores_nome_do_vendedor' => $vendedores_nome_do_vendedor,
            'produtos_produto' => $produtos_produto,
            'servicos_servico' => $servicos_servico,
            'fornecedores_fornecedor' => $fornecedores_fornecedor,
            'companhias_companhia' => $companhias_companhia,
            'trechos_trechos' => $trechos_trechos,
            'passageiro_nome' => $passageiro_nome,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,
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

            $vendas = Vendas::find($store['id']);

            if (!$vendas) {

                \Session::flash('flash_error', 'Registro não encontrado!');

                return Redirect::to('/vendas');
            }

            if (!Permissions::permissaoModerador($user) && $vendas->r_auth != 0 && $vendas->r_auth != $user->id)
            {
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/vendas');
            }

            $store["valor_tarifa"] = ((!is_null($store["valor_tarifa"]) && !empty($store["valor_tarifa"])) ? $store["valor_tarifa"] : 0);
$store["tx_embarque"] = ((!is_null($store["tx_embarque"]) && !empty($store["tx_embarque"])) ? $store["tx_embarque"] : 0);
$store["outras_taxas"] = ((!is_null($store["outras_taxas"]) && !empty($store["outras_taxas"])) ? $store["outras_taxas"] : 0);
$store["desconto"] = ((!is_null($store["desconto"]) && !empty($store["desconto"])) ? $store["desconto"] : 0);
$store["comissao"] = ((!is_null($store["comissao"]) && !empty($store["comissao"])) ? $store["comissao"] : 0);
            try {
                $store["valor_total"] = number_format($store["valor_tarifa"] + $store["tx_embarque"] + $store["outras_taxas"] - $store["desconto"] + $store["comissao"], 2, ',', '.');
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

//$store["valor_"] = ((!is_null($store["valor_"]) && !empty($store["valor_"])) ? $store["valor_"] : 0);
//$store["acrescimo_"] = ((!is_null($store["acrescimo_"]) && !empty($store["acrescimo_"])) ? $store["acrescimo_"] : 0);
//$store["desconto_"] = ((!is_null($store["desconto_"]) && !empty($store["desconto_"])) ? $store["desconto_"] : 0);
$store["incentivo"] = ((!is_null($store["incentivo"]) && !empty($store["incentivo"])) ? $store["incentivo"] : 0);
            try {
                //$store["vlr_pago_"] = $store["valor_"] + $store["acrescimo_"] - $store["desconto_"];
                //$store["vlr_pago_"] = $store["acrescimo_"] - $store["desconto_"];
            } catch (Exception $e) {
                Log::info($e->getMessage());
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

            $vendas->r_auth = $r_auth;

            $vendas->fill($store);

            $vendas->save();

            if (!empty($relacionamento)) {
                $this->controllerRepository::insertRelationship($vendas, $relacionamento, 'Vendas', 'vendas');
            }

            if ($grids) {
                $this->controllerRepository::saveGrids($vendas, $grids, 'vendas');
            }

            try {

                    DB::statement("UPDATE
vendas
SET
faturamento  = NULL,
foi_faturado = NULL,
template 	 = 2
WHERE
tipo_de_venda = 1");
        DB::statement("UPDATE
vendas
SET
template 	 = NULL
WHERE
tipo_de_venda = 2");

            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            // # -
            if(!\Request::get('modal-close')){
                Session::flash('flash_success', "Registro atualizado com sucesso!");
            }

            if ($user) {
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $vendas->id . ' do módulo vendas'));
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

        return Redirect::to('/vendas');
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

            $vendas         = Vendas::find($store['id']);

            if(!$vendas){
                return response()->json(['error'=>'Registro não encontrado!'],400);
            }

            if(!Permissions::permissaoModerador($user) && $vendas->r_auth != 0 && $vendas->r_auth != $user->id){
                return response()->json(['error'=>'Você não tem permissão para executar esta ação!'],400);
            }

            $vendas->update([
                $store['item_column']   => $store['item_target_id'],
                'r_auth'                => $r_auth,
            ]);

            DB::commit();

            try {

                    DB::statement("UPDATE
vendas
SET
faturamento  = NULL,
foi_faturado = NULL,
template 	 = 2
WHERE
tipo_de_venda = 1");
        DB::statement("UPDATE
vendas
SET
template 	 = NULL
WHERE
tipo_de_venda = 2");

            }catch (Exception $e) {
                Log::info($e->getMessage());
            }

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' atualizou ID #' . $vendas->id . ' do módulo vendas'));
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
        $vendas = Vendas::find($id);

        try {

        } catch (Exception $e) {
            Log::info($e->getMessage());
        }

        return $this->controllerRepository::destroy(new Vendas(), $id, 'vendas');
    }

    public function vendasFaturamentoClienteDTinicialDTfinal(Request $request){
        try {

            $store                  = $request->all();

            //$store['data_inicial']  = Carbon::createFromFormat('d/m/Y',$store['data_inicial'])->format('Y-m-d');
            //$store['data_final']    = Carbon::createFromFormat('d/m/Y',$store['data_final'])->format('Y-m-d');

            $Vendas                 = Vendas::with(['Cliente','Fornecedor','Produto','Servico','Trecho','VendasGridPassageiros','VendasGridPassageiros.Passageiros','VendasGridPagamentos.FormaDePagamento'])
                                      ->where("tipo_de_venda",2)
                                      ->where("cliente",$store['cliente'])
                                      //->whereBetween("data", [$store['data_inicial'],$store['data_final']])
                                      ->orderBy('data','DESC')
                                      ->get();

            foreach($Vendas as $V){
                $V->id_f            = $V->id; //str_pad($V->id,7,'0',STR_PAD_LEFT); // venda id - formatação
                $V->faturamento_id  = (!empty($V->faturamento)?(int) $V->faturamento:null); // id
                $V->data_f          = (!empty($V->data)?date('d/m/Y',strtotime($V->data)):'---');
                $V->data_embarque_f = (!empty($V->data_embarque)?date('d/m/Y',strtotime($V->data_embarque)):'---');
                $V->data_retorno_f  = (!empty($V->data_retorno)?date('d/m/Y',strtotime($V->data_retorno)):'---');
                $V->valor_total     = (!empty($V->valor_total)?$V->valor_total:'0,00');
                $V->comissao        = (!empty($V->comissao)?number_format($V->comissao,2,',','.'):'0,00');
            }

            return response()->json([
                'data'  => $Vendas
            ], 200);

        }catch(\Exception $e){
            Log::info($e->getMessage());
            return response()->json([
                'error' => 'Ocorreu um erro!'
            ], 500);
        }
    }

}
