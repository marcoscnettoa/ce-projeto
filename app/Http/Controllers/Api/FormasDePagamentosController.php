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

use \App\Models\FormasDePagamentos;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

class FormasDePagamentosController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"FormasDePagamentos"},
    *     summary="Listar",
    *     path="/api/formas_de_pagamentos",
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

        $filter = FormasDePagamentos::getAllByApi(500);

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

            $formas_de_pagamentos = FormasDePagamentos::select('*');

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
                            $formas_de_pagamentos->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $formas_de_pagamentos->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $formas_de_pagamentos->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $formas_de_pagamentos->where($key, $store[$key]);
                        }
                        else
                        {
                            $formas_de_pagamentos->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $formas_de_pagamentos = $formas_de_pagamentos->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $formas_de_pagamentos = $formas_de_pagamentos->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
            }

            return view('formas_de_pagamentos.index', [
                'exibe_filtros' => 1,
                'formas_de_pagamentos' => $formas_de_pagamentos,

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

        return Redirect::to('/formas_de_pagamentos');
    }

    /**
    * @OA\Get(
    *     tags={"FormasDePagamentos"},
    *     summary="Retornar",
    *     path="/api/formas_de_pagamentos/{id}",
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

            $formas_de_pagamentos = FormasDePagamentos::find($id);

            if (!$formas_de_pagamentos) {
              throw new Exception("FormasDePagamentos não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $formas_de_pagamentos->r_auth != 0 && $formas_de_pagamentos->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($formas_de_pagamentos, 200);

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
    *   tags={"FormasDePagamentos"},
    *   summary="Cadastrar",
    *   path="/api/formas_de_pagamentos",
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
    *                     property="forma_de_pagamento",
    *                     description="Forma de pagamento",
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

            $formas_de_pagamentos = FormasDePagamentos::create($store);

            return response()->json($formas_de_pagamentos, 201);

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
    *   tags={"FormasDePagamentos"},
    *   summary="Atualizar",
    *   path="/api/formas_de_pagamentos/{id}",
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
    *                     property="forma_de_pagamento",
    *                     description="Forma de pagamento",
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

            $formas_de_pagamentos = FormasDePagamentos::find($id);

            if (!$formas_de_pagamentos) {
              throw new Exception("FormasDePagamentos não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $formas_de_pagamentos->r_auth != 0 && $formas_de_pagamentos->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $formas_de_pagamentos->update($store);

            return response()->json($formas_de_pagamentos, 200);

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
    *     tags={"FormasDePagamentos"},
    *     summary="Deletar",
    *     path="/api/formas_de_pagamentos/{id}",
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

            $formas_de_pagamentos = FormasDePagamentos::find($id);

            if (!$formas_de_pagamentos) {
              throw new Exception("FormasDePagamentos não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $formas_de_pagamentos->r_auth != 0 && $formas_de_pagamentos->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $formas_de_pagamentos->delete();

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