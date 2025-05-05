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

use \App\Models\FluxoDeCaixa;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

class FluxoDeCaixaController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"FluxoDeCaixa"},
    *     summary="Listar",
    *     path="/api/fluxo_de_caixa",
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

        $filter = FluxoDeCaixa::getAllByApi(500);

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

            $fluxo_de_caixa = FluxoDeCaixa::select('*');

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
                            $fluxo_de_caixa->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $fluxo_de_caixa->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $fluxo_de_caixa->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $fluxo_de_caixa->where($key, $store[$key]);
                        }
                        else
                        {
                            $fluxo_de_caixa->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $fluxo_de_caixa = $fluxo_de_caixa->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $fluxo_de_caixa = $fluxo_de_caixa->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
            }

            return view('fluxo_de_caixa.index', [
                'exibe_filtros' => 1,
                'fluxo_de_caixa' => $fluxo_de_caixa,

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

        return Redirect::to('/fluxo_de_caixa');
    }

    /**
    * @OA\Get(
    *     tags={"FluxoDeCaixa"},
    *     summary="Retornar",
    *     path="/api/fluxo_de_caixa/{id}",
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

            $fluxo_de_caixa = FluxoDeCaixa::find($id);

            if (!$fluxo_de_caixa) {
              throw new Exception("FluxoDeCaixa não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $fluxo_de_caixa->r_auth != 0 && $fluxo_de_caixa->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($fluxo_de_caixa, 200);

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
    *   tags={"FluxoDeCaixa"},
    *   summary="Cadastrar",
    *   path="/api/fluxo_de_caixa",
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
    *                     property="saldo_inicial",
    *                     description="Saldo Inicial",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="movimentacao",
    *                     description="Movimentação",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="recebimentos",
    *                     description="Recebimentos",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_do_recebimento",
    *                     description="Data do Recebimento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_recebido",
    *                     description="Valor Recebido",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="pagamentos",
    *                     description="Pagamentos",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_do_pagamento",
    *                     description="Data do Pagamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="total_a_pagar",
    *                     description="Total a Pagar",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="total",
    *                     description="Total",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_atual",
    *                     description="Data Atual",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="ghost_camp",
    *                     description="Ghost camp",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="saldo_da_transacao",
    *                     description="Saldo da Transação",
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

            $fluxo_de_caixa = FluxoDeCaixa::create($store);

            return response()->json($fluxo_de_caixa, 201);

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
    *   tags={"FluxoDeCaixa"},
    *   summary="Atualizar",
    *   path="/api/fluxo_de_caixa/{id}",
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
    *                     property="saldo_inicial",
    *                     description="Saldo Inicial",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="movimentacao",
    *                     description="Movimentação",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="recebimentos",
    *                     description="Recebimentos",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_do_recebimento",
    *                     description="Data do Recebimento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_recebido",
    *                     description="Valor Recebido",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="pagamentos",
    *                     description="Pagamentos",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_do_pagamento",
    *                     description="Data do Pagamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="total_a_pagar",
    *                     description="Total a Pagar",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="total",
    *                     description="Total",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_atual",
    *                     description="Data Atual",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="ghost_camp",
    *                     description="Ghost camp",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="saldo_da_transacao",
    *                     description="Saldo da Transação",
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

            $fluxo_de_caixa = FluxoDeCaixa::find($id);

            if (!$fluxo_de_caixa) {
              throw new Exception("FluxoDeCaixa não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $fluxo_de_caixa->r_auth != 0 && $fluxo_de_caixa->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $fluxo_de_caixa->update($store);

            return response()->json($fluxo_de_caixa, 200);

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
    *     tags={"FluxoDeCaixa"},
    *     summary="Deletar",
    *     path="/api/fluxo_de_caixa/{id}",
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

            $fluxo_de_caixa = FluxoDeCaixa::find($id);

            if (!$fluxo_de_caixa) {
              throw new Exception("FluxoDeCaixa não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $fluxo_de_caixa->r_auth != 0 && $fluxo_de_caixa->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $fluxo_de_caixa->delete();

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