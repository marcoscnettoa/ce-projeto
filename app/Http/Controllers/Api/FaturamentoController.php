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

use \App\Models\Faturamento;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

use \App\Models\Clientes; 
use \App\Models\Templates; 

class FaturamentoController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"Faturamento"},
    *     summary="Listar",
    *     path="/api/faturamento",
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

        $filter = Faturamento::getAllByApi(500);

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

            $faturamento = Faturamento::select('*');

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
                            $faturamento->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $faturamento->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $faturamento->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $faturamento->where($key, $store[$key]);
                        }
                        else
                        {
                            $faturamento->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $faturamento = $faturamento->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $faturamento = $faturamento->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
            }

            $clientes_nome_do_cliente = Clientes::list(10000, "nome_do_cliente");

        $templates_nome_do_template = Templates::list(10000, "nome_do_template");

            return view('faturamento.index', [
                'exibe_filtros' => 1,
                'faturamento' => $faturamento,
                'clientes_nome_do_cliente' => $clientes_nome_do_cliente,
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

        return Redirect::to('/faturamento');
    }

    /**
    * @OA\Get(
    *     tags={"Faturamento"},
    *     summary="Retornar",
    *     path="/api/faturamento/{id}",
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

            $faturamento = Faturamento::find($id);

            if (!$faturamento) {
              throw new Exception("Faturamento não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $faturamento->r_auth != 0 && $faturamento->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($faturamento, 200);

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
    *   tags={"Faturamento"},
    *   summary="Cadastrar",
    *   path="/api/faturamento",
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
    *                     property="n_da_fatura",
    *                     description="N° da Fatura",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_da_fatura",
    *                     description="Data da Fatura",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_de_vencimento",
    *                     description="Data de Vencimento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="lancamento",
    *                     description="Lançamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cliente",
    *                     description="Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_inicial",
    *                     description="Data Inicial",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_final",
    *                     description="Data Final",
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

            $faturamento = Faturamento::create($store);

            return response()->json($faturamento, 201);

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
    *   tags={"Faturamento"},
    *   summary="Atualizar",
    *   path="/api/faturamento/{id}",
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
    *                     property="n_da_fatura",
    *                     description="N° da Fatura",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_da_fatura",
    *                     description="Data da Fatura",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_de_vencimento",
    *                     description="Data de Vencimento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="lancamento",
    *                     description="Lançamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cliente",
    *                     description="Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_inicial",
    *                     description="Data Inicial",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_final",
    *                     description="Data Final",
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

            $faturamento = Faturamento::find($id);

            if (!$faturamento) {
              throw new Exception("Faturamento não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $faturamento->r_auth != 0 && $faturamento->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $faturamento->update($store);

            return response()->json($faturamento, 200);

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
    *     tags={"Faturamento"},
    *     summary="Deletar",
    *     path="/api/faturamento/{id}",
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

            $faturamento = Faturamento::find($id);

            if (!$faturamento) {
              throw new Exception("Faturamento não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $faturamento->r_auth != 0 && $faturamento->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $faturamento->delete();

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