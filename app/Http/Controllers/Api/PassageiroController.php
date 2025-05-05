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

use \App\Models\Passageiro;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

class PassageiroController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"Passageiro"},
    *     summary="Listar",
    *     path="/api/passageiro",
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

        $filter = Passageiro::getAllByApi(500);

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

            $passageiro = Passageiro::select('*');

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
                            $passageiro->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $passageiro->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $passageiro->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $passageiro->where($key, $store[$key]);
                        }
                        else
                        {
                            $passageiro->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $passageiro = $passageiro->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $passageiro = $passageiro->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
            }

            return view('passageiro.index', [
                'exibe_filtros' => 1,
                'passageiro' => $passageiro,

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

        return Redirect::to('/passageiro');
    }

    /**
    * @OA\Get(
    *     tags={"Passageiro"},
    *     summary="Retornar",
    *     path="/api/passageiro/{id}",
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

            $passageiro = Passageiro::find($id);

            if (!$passageiro) {
              throw new Exception("Passageiro não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $passageiro->r_auth != 0 && $passageiro->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($passageiro, 200);

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
    *   tags={"Passageiro"},
    *   summary="Cadastrar",
    *   path="/api/passageiro",
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
    *                     property="dados_do_passageiro",
    *                     description="Dados do Passageiro",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome",
    *                     description="Nome",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cpf",
    *                     description="CPF",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="e_mail",
    *                     description="E-mail",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone",
    *                     description="Telefone",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone_2",
    *                     description="Telefone 2",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="passaporte",
    *                     description="Passaporte",
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

            $passageiro = Passageiro::create($store);

            return response()->json($passageiro, 201);

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
    *   tags={"Passageiro"},
    *   summary="Atualizar",
    *   path="/api/passageiro/{id}",
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
    *                     property="dados_do_passageiro",
    *                     description="Dados do Passageiro",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome",
    *                     description="Nome",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cpf",
    *                     description="CPF",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="e_mail",
    *                     description="E-mail",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone",
    *                     description="Telefone",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone_2",
    *                     description="Telefone 2",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="passaporte",
    *                     description="Passaporte",
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

            $passageiro = Passageiro::find($id);

            if (!$passageiro) {
              throw new Exception("Passageiro não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $passageiro->r_auth != 0 && $passageiro->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $passageiro->update($store);

            return response()->json($passageiro, 200);

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
    *     tags={"Passageiro"},
    *     summary="Deletar",
    *     path="/api/passageiro/{id}",
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

            $passageiro = Passageiro::find($id);

            if (!$passageiro) {
              throw new Exception("Passageiro não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $passageiro->r_auth != 0 && $passageiro->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $passageiro->delete();

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