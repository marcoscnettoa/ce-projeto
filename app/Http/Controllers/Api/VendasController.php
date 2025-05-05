<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use Auth;
use Redirect;
use Session;
use Validator;
use Exception;
use Response;
use DB;
use Log;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use \App\Models\Vendas;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

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

class VendasController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"Vendas"},
    *     summary="Listar",
    *     path="/api/vendas",
    *     @OA\Parameter(
    *         name="Authorization",
    *         in="header",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             default="Bearer <token>",
    *             description="Bearer token para autorização"
    *         )
    *     ),
    *     @OA\Response(response="200", description=""),
    * ),
    * 
    */
    public function index(Request $request)
    {   
        $store = $request->all();

        $filter = Vendas::getAllByApi(500);

        if (!empty($store)) {

            foreach ($store as $key => $value) {

                if (gettype($value) == 'string') 
                {
                    $filter->where($key, "LIKE", "%" . $value . "%");
                }
                else
                {
                    $filter->where($key, $value);
                }
            }

        }

        $filter = $filter->get();

        return response()->json($filter, 200);
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

            $vendas = Vendas::select('*');

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
                            $vendas->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $vendas->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $vendas->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $vendas->where($key, $store[$key]);
                        }
                        else
                        {
                            $vendas->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $vendas = $vendas->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $vendas = $vendas->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
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

            return view('vendas.index', [
                'exibe_filtros' => 1,
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

        } catch (Exception $e) {

            return response()->json(
                array(
                    'type' => 'error',
                    'message' => $e->getMessage(),
                    'code' => 500
                ), 500
            );
        }

        return Redirect::to('/vendas');
    }

    /**
    * @OA\Get(
    *     tags={"Vendas"},
    *     summary="Retornar",
    *     path="/api/vendas/{id}",
    *     @OA\Parameter(
    *         name="Authorization",
    *         in="header",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             default="Bearer <token>",
    *             description="Bearer token para autorização"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *         )
    *     ),
    *     @OA\Response(response="200", description=""),
    * ),
    * 
    */
    public function show($id)
    {
        try {

            $user = Auth::user();

            $vendas = Vendas::find($id);

            if (!$vendas) {
              throw new Exception("Vendas não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $vendas->r_auth != 0 && $vendas->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($vendas, 200);

        } catch (Exception $e) {

            Log::error($e->getMessage());

            return response()->json(
                array(
                    'type' => 'error',
                    'message' => $e->getMessage(),
                    'code' => 500
                ), 500
            );

        }
    }

    /**
    * @OA\Post(
    *   tags={"Vendas"},
    *   summary="Cadastrar",
    *   path="/api/vendas",
    *     @OA\Parameter(
    *         name="Authorization",
    *         in="header",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             default="Bearer <token>",
    *             description="Bearer token para autorização"
    *         )
    *     ),
    *     @OA\RequestBody(
    *         description="Input data format",
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="tipo_de_venda",
    *                     description="Tipo de Venda",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="faturamento",
    *                     description="Faturamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="foi_faturado",
    *                     description="Foi Faturado",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="informacoes_da_venda_",
    *                     description="INFORMAÇÕES DA VENDA:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="id",
    *                     description="Id",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data",
    *                     description="Data",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cliente",
    *                     description="Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="vendedor",
    *                     description="Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="localizador",
    *                     description="Localizador",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="produto",
    *                     description="Produto",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="servico",
    *                     description="Serviço",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="fornecedor",
    *                     description="Fornecedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="companhia",
    *                     description="Companhia",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="trecho",
    *                     description="Trecho",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_embarque",
    *                     description="Data Embarque",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_retorno",
    *                     description="Data Retorno",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="passageiros",
    *                     description="Passageiros",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_tarifa",
    *                     description="Valor Tarifa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="tx_embarque",
    *                     description="Tx. Embarque",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="outras_taxas",
    *                     description="Outras Taxas",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="desconto",
    *                     description="Desconto",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="comissao",
    *                     description="Comissão",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_total",
    *                     description="Valor Total",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="observacoes_da_venda_",
    *                     description="OBSERVACÕES DA VENDA:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="formas_de_pagamento_",
    *                     description="FORMAS DE PAGAMENTO:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="forma_de_pgto",
    *                     description="Forma de Pgto",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="pgto_ao_fornecedor",
    *                     description="PGTO AO FORNECEDOR",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="dt_pgto_",
    *                     description="Dt. Pgto:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nr_documento_",
    *                     description="Nr. Documento:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_",
    *                     description="Valor:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="acrescimo_",
    *                     description="Acréscimo:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="desconto_",
    *                     description="Desconto:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="vlr_pago_",
    *                     description="Vlr Pago:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="observacoes_",
    *                     description="OBSERVAÇÕES:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="template",
    *                     description="Template",
    *                     type="string"
    *                 ),
    *             )
    *         )
    *     ),
    *   @OA\Response(response="201", description=""),
    * )
    */
    public function store(Request $request)
    {
        try {

            $store = $request->all();

            $vendas = Vendas::create($store);

            return response()->json($vendas, 201);

        } catch (Exception $e) {

            Log::error($e->getMessage());

            return response()->json(
                array(
                    'type' => 'error',
                    'message' => $e->getMessage(),
                    'code' => 500
                ), 500
            );

        }
    }

    /**
    * @OA\Put(
    *   tags={"Vendas"},
    *   summary="Atualizar",
    *   path="/api/vendas/{id}",
    *     @OA\Parameter(
    *         name="Authorization",
    *         in="header",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             default="Bearer <token>",
    *             description="Bearer token para autorização"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *         )
    *     ), 
    *     @OA\RequestBody(
    *         description="Input data format",
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="tipo_de_venda",
    *                     description="Tipo de Venda",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="faturamento",
    *                     description="Faturamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="foi_faturado",
    *                     description="Foi Faturado",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="informacoes_da_venda_",
    *                     description="INFORMAÇÕES DA VENDA:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="id",
    *                     description="Id",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data",
    *                     description="Data",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cliente",
    *                     description="Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="vendedor",
    *                     description="Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="localizador",
    *                     description="Localizador",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="produto",
    *                     description="Produto",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="servico",
    *                     description="Serviço",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="fornecedor",
    *                     description="Fornecedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="companhia",
    *                     description="Companhia",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="trecho",
    *                     description="Trecho",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_embarque",
    *                     description="Data Embarque",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_retorno",
    *                     description="Data Retorno",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="passageiros",
    *                     description="Passageiros",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_tarifa",
    *                     description="Valor Tarifa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="tx_embarque",
    *                     description="Tx. Embarque",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="outras_taxas",
    *                     description="Outras Taxas",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="desconto",
    *                     description="Desconto",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="comissao",
    *                     description="Comissão",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_total",
    *                     description="Valor Total",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="observacoes_da_venda_",
    *                     description="OBSERVACÕES DA VENDA:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="formas_de_pagamento_",
    *                     description="FORMAS DE PAGAMENTO:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="forma_de_pgto",
    *                     description="Forma de Pgto",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="pgto_ao_fornecedor",
    *                     description="PGTO AO FORNECEDOR",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="dt_pgto_",
    *                     description="Dt. Pgto:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nr_documento_",
    *                     description="Nr. Documento:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_",
    *                     description="Valor:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="acrescimo_",
    *                     description="Acréscimo:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="desconto_",
    *                     description="Desconto:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="vlr_pago_",
    *                     description="Vlr Pago:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="observacoes_",
    *                     description="OBSERVAÇÕES:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="template",
    *                     description="Template",
    *                     type="string"
    *                 ),
    *             )
    *         )
    *     ),
    *   @OA\Response(response="201", description=""),
    * )
    */
    public function update(Request $request, $id)
    {
        try {

            $user = Auth::user();

            $store = $request->all();

            $vendas = Vendas::find($id);

            if (!$vendas) {
              throw new Exception("Vendas não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $vendas->r_auth != 0 && $vendas->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $vendas->update($store);

            return response()->json($vendas, 200);

        } catch (Exception $e) {

            Log::error($e->getMessage());

            return response()->json(
                array(
                    'type' => 'error',
                    'message' => $e->getMessage(),
                    'code' => 500
                ), 500
            );
        }

    }

    /**
    * @OA\Delete(
    *     tags={"Vendas"},
    *     summary="Deletar",
    *     path="/api/vendas/{id}",
    *     @OA\Parameter(
    *         name="Authorization",
    *         in="header",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *             default="Bearer <token>",
    *             description="Bearer token para autorização"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         @OA\Schema(
    *             type="string",
    *         )
    *     ), 
    *     @OA\Response(response="200", description=""),
    * )
    */
    public function destroy(Request $request, $id)
    {
        try {

            $user = Auth::user();

            $vendas = Vendas::find($id);

            if (!$vendas) {
              throw new Exception("Vendas não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $vendas->r_auth != 0 && $vendas->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $vendas->delete();

            return response()->json(null, 204);

        } catch (Exception $e) {

            Log::error($e->getMessage());

            return response()->json(
                array(
                    'type' => 'error',
                    'message' => $e->getMessage(),
                    'code' => 500
                ), 500
            );
        }
    }
}