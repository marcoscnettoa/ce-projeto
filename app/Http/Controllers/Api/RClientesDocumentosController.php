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

use \App\Models\RClientesDocumentos;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

use \App\Models\Documentos; 

class RClientesDocumentosController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"RClientesDocumentos"},
    *     summary="Listar",
    *     path="/api/r_clientes_documentos",
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

        $filter = RClientesDocumentos::getAllByApi(500);

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

            $r_clientes_documentos = RClientesDocumentos::select('*');

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
                            $r_clientes_documentos->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $r_clientes_documentos->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $r_clientes_documentos->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $r_clientes_documentos->where($key, $store[$key]);
                        }
                        else
                        {
                            $r_clientes_documentos->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $r_clientes_documentos = $r_clientes_documentos->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $r_clientes_documentos = $r_clientes_documentos->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
            }

            return view('r_clientes_documentos.index', [
                'exibe_filtros' => 1,
                'r_clientes_documentos' => $r_clientes_documentos,

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

        return Redirect::to('/r_clientes_documentos');
    }

    /**
    * @OA\Get(
    *     tags={"RClientesDocumentos"},
    *     summary="Retornar",
    *     path="/api/r_clientes_documentos/{id}",
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

            $r_clientes_documentos = RClientesDocumentos::find($id);

            if (!$r_clientes_documentos) {
              throw new Exception("RClientesDocumentos não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $r_clientes_documentos->r_auth != 0 && $r_clientes_documentos->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($r_clientes_documentos, 200);

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
    *   tags={"RClientesDocumentos"},
    *   summary="Cadastrar",
    *   path="/api/r_clientes_documentos",
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
    *                     property="documentos",
    *                     description="Documentos",
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

            $r_clientes_documentos = RClientesDocumentos::create($store);

            return response()->json($r_clientes_documentos, 201);

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
    *   tags={"RClientesDocumentos"},
    *   summary="Atualizar",
    *   path="/api/r_clientes_documentos/{id}",
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
    *                     property="documentos",
    *                     description="Documentos",
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

            $r_clientes_documentos = RClientesDocumentos::find($id);

            if (!$r_clientes_documentos) {
              throw new Exception("RClientesDocumentos não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $r_clientes_documentos->r_auth != 0 && $r_clientes_documentos->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $r_clientes_documentos->update($store);

            return response()->json($r_clientes_documentos, 200);

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
    *     tags={"RClientesDocumentos"},
    *     summary="Deletar",
    *     path="/api/r_clientes_documentos/{id}",
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

            $r_clientes_documentos = RClientesDocumentos::find($id);

            if (!$r_clientes_documentos) {
              throw new Exception("RClientesDocumentos não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $r_clientes_documentos->r_auth != 0 && $r_clientes_documentos->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $r_clientes_documentos->delete();

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