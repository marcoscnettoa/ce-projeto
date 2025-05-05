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

use \App\Models\Vendedores;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

class VendedoresController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"Vendedores"},
    *     summary="Listar",
    *     path="/api/vendedores",
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

        $filter = Vendedores::getAllByApi(500);

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

            $vendedores = Vendedores::select('*');

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
                            $vendedores->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $vendedores->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $vendedores->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $vendedores->where($key, $store[$key]);
                        }
                        else
                        {
                            $vendedores->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $vendedores = $vendedores->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $vendedores = $vendedores->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
            }

            return view('vendedores.index', [
                'exibe_filtros' => 1,
                'vendedores' => $vendedores,

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

        return Redirect::to('/vendedores');
    }

    /**
    * @OA\Get(
    *     tags={"Vendedores"},
    *     summary="Retornar",
    *     path="/api/vendedores/{id}",
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

            $vendedores = Vendedores::find($id);

            if (!$vendedores) {
              throw new Exception("Vendedores não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $vendedores->r_auth != 0 && $vendedores->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($vendedores, 200);

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
    *   tags={"Vendedores"},
    *   summary="Cadastrar",
    *   path="/api/vendedores",
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
    *                     property="dados_do_vendedor",
    *                     description="Dados do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="codigo_do_vendedor",
    *                     description="Código do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome_do_vendedor",
    *                     description="Nome do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cpf_do_vendedor",
    *                     description="CPF do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="e_mail_do_vendedor",
    *                     description="E-mail do  vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone_1",
    *                     description="Telefone 1",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="observacao",
    *                     description="Observação",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="endereco_do_vendedor",
    *                     description="Endereço do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cep_do_vendedor",
    *                     description="CEP do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="endereco_d_vendedor",
    *                     description="Endereço d  Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="numero_do_endereco_do_vendedor",
    *                     description="Número do Endereço do vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="complemento_do_vendedor",
    *                     description="Complemento do vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="bairro_do_vendedor",
    *                     description="Bairro do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cidade_do_vendedor",
    *                     description="Cidade do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="estado_do_vendedor",
    *                     description="Estado do Vendedor",
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

            $vendedores = Vendedores::create($store);

            return response()->json($vendedores, 201);

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
    *   tags={"Vendedores"},
    *   summary="Atualizar",
    *   path="/api/vendedores/{id}",
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
    *                     property="dados_do_vendedor",
    *                     description="Dados do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="codigo_do_vendedor",
    *                     description="Código do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome_do_vendedor",
    *                     description="Nome do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cpf_do_vendedor",
    *                     description="CPF do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="e_mail_do_vendedor",
    *                     description="E-mail do  vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone_1",
    *                     description="Telefone 1",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="observacao",
    *                     description="Observação",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="endereco_do_vendedor",
    *                     description="Endereço do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cep_do_vendedor",
    *                     description="CEP do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="endereco_d_vendedor",
    *                     description="Endereço d  Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="numero_do_endereco_do_vendedor",
    *                     description="Número do Endereço do vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="complemento_do_vendedor",
    *                     description="Complemento do vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="bairro_do_vendedor",
    *                     description="Bairro do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cidade_do_vendedor",
    *                     description="Cidade do Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="estado_do_vendedor",
    *                     description="Estado do Vendedor",
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

            $vendedores = Vendedores::find($id);

            if (!$vendedores) {
              throw new Exception("Vendedores não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $vendedores->r_auth != 0 && $vendedores->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $vendedores->update($store);

            return response()->json($vendedores, 200);

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
    *     tags={"Vendedores"},
    *     summary="Deletar",
    *     path="/api/vendedores/{id}",
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

            $vendedores = Vendedores::find($id);

            if (!$vendedores) {
              throw new Exception("Vendedores não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $vendedores->r_auth != 0 && $vendedores->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $vendedores->delete();

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