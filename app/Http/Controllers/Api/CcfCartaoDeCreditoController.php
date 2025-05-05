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

use \App\Models\CcfCartaoDeCredito;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

class CcfCartaoDeCreditoController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"CcfCartaoDeCredito"},
    *     summary="Listar",
    *     path="/api/ccf_cartao_de_credito",
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

        $filter = CcfCartaoDeCredito::getAllByApi(500);

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

            $ccf_cartao_de_credito = CcfCartaoDeCredito::select('*');

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
                            $ccf_cartao_de_credito->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $ccf_cartao_de_credito->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $ccf_cartao_de_credito->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $ccf_cartao_de_credito->where($key, $store[$key]);
                        }
                        else
                        {
                            $ccf_cartao_de_credito->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $ccf_cartao_de_credito = $ccf_cartao_de_credito->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $ccf_cartao_de_credito = $ccf_cartao_de_credito->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
            }

            return view('ccf_cartao_de_credito.index', [
                'exibe_filtros' => 1,
                'ccf_cartao_de_credito' => $ccf_cartao_de_credito,

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

        return Redirect::to('/ccf_cartao_de_credito');
    }

    /**
    * @OA\Get(
    *     tags={"CcfCartaoDeCredito"},
    *     summary="Retornar",
    *     path="/api/ccf_cartao_de_credito/{id}",
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

            $ccf_cartao_de_credito = CcfCartaoDeCredito::find($id);

            if (!$ccf_cartao_de_credito) {
              throw new Exception("CcfCartaoDeCredito não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $ccf_cartao_de_credito->r_auth != 0 && $ccf_cartao_de_credito->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($ccf_cartao_de_credito, 200);

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
    *   tags={"CcfCartaoDeCredito"},
    *   summary="Cadastrar",
    *   path="/api/ccf_cartao_de_credito",
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
    *                     property="bandeira_do_cartao_",
    *                     description="BANDEIRA DO CARTÃO:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="visa_",
    *                     description="Visa:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="mastercard_",
    *                     description="Mastercard:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="diners_",
    *                     description="Diners:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="outros",
    *                     description="Outros",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="dados_do_cartao_",
    *                     description="DADOS DO CARTÃO:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="numero_do_cartao_",
    *                     description="Número do Cartão:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="codigo_de_verificacao_",
    *                     description="Código de Verificação:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_de_validade_cartao_",
    *                     description="Data de Validade Cartão:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome_do_titular_",
    *                     description="Nome do Titular:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cpf_",
    *                     description="CPF:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nro_do_telefone_do_responsavel_",
    *                     description="Nro do Telefone do Responsável:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valores_e_condicoes_",
    *                     description="VALORES E CONDIÇÕES:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_total_",
    *                     description="Valor Total:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nro_de_parcelas_",
    *                     description="Nro de Parcelas:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_da_parcela_",
    *                     description="Valor da Parcela:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="importante_",
    *                     description="IMPORTANTE:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="esta_autorizacao_destina_se_ao_pagamento_em_nome_de_",
    *                     description="Esta autorização destina-se ao pagamento em nome de:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nro_telefone_passageiro_",
    *                     description="Nro. Telefone Passageiro:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cia_aerea_",
    *                     description="Cia Aérea:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_de_embarque_",
    *                     description="Data de Embarque:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="destino_",
    *                     description="Destino:",
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

            $store["visa_"] = (isset($store["visa_"]) && $store["visa_"] == "on");
$store["mastercard_"] = (isset($store["mastercard_"]) && $store["mastercard_"] == "on");
$store["diners_"] = (isset($store["diners_"]) && $store["diners_"] == "on");
$store["outros"] = (isset($store["outros"]) && $store["outros"] == "on");

            $ccf_cartao_de_credito = CcfCartaoDeCredito::create($store);

            return response()->json($ccf_cartao_de_credito, 201);

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
    *   tags={"CcfCartaoDeCredito"},
    *   summary="Atualizar",
    *   path="/api/ccf_cartao_de_credito/{id}",
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
    *                     property="bandeira_do_cartao_",
    *                     description="BANDEIRA DO CARTÃO:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="visa_",
    *                     description="Visa:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="mastercard_",
    *                     description="Mastercard:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="diners_",
    *                     description="Diners:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="outros",
    *                     description="Outros",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="dados_do_cartao_",
    *                     description="DADOS DO CARTÃO:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="numero_do_cartao_",
    *                     description="Número do Cartão:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="codigo_de_verificacao_",
    *                     description="Código de Verificação:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_de_validade_cartao_",
    *                     description="Data de Validade Cartão:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome_do_titular_",
    *                     description="Nome do Titular:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cpf_",
    *                     description="CPF:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nro_do_telefone_do_responsavel_",
    *                     description="Nro do Telefone do Responsável:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valores_e_condicoes_",
    *                     description="VALORES E CONDIÇÕES:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_total_",
    *                     description="Valor Total:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nro_de_parcelas_",
    *                     description="Nro de Parcelas:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_da_parcela_",
    *                     description="Valor da Parcela:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="importante_",
    *                     description="IMPORTANTE:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="esta_autorizacao_destina_se_ao_pagamento_em_nome_de_",
    *                     description="Esta autorização destina-se ao pagamento em nome de:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nro_telefone_passageiro_",
    *                     description="Nro. Telefone Passageiro:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cia_aerea_",
    *                     description="Cia Aérea:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_de_embarque_",
    *                     description="Data de Embarque:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="destino_",
    *                     description="Destino:",
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

            $ccf_cartao_de_credito = CcfCartaoDeCredito::find($id);

            $store["visa_"] = (isset($store["visa_"]) && $store["visa_"] == "on");
$store["mastercard_"] = (isset($store["mastercard_"]) && $store["mastercard_"] == "on");
$store["diners_"] = (isset($store["diners_"]) && $store["diners_"] == "on");
$store["outros"] = (isset($store["outros"]) && $store["outros"] == "on");

            if (!$ccf_cartao_de_credito) {
              throw new Exception("CcfCartaoDeCredito não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $ccf_cartao_de_credito->r_auth != 0 && $ccf_cartao_de_credito->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $ccf_cartao_de_credito->update($store);

            return response()->json($ccf_cartao_de_credito, 200);

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
    *     tags={"CcfCartaoDeCredito"},
    *     summary="Deletar",
    *     path="/api/ccf_cartao_de_credito/{id}",
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

            $ccf_cartao_de_credito = CcfCartaoDeCredito::find($id);

            if (!$ccf_cartao_de_credito) {
              throw new Exception("CcfCartaoDeCredito não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $ccf_cartao_de_credito->r_auth != 0 && $ccf_cartao_de_credito->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $ccf_cartao_de_credito->delete();

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