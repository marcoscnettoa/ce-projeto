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

use \App\Models\Clientes;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

use \App\Models\Documentos; 

class ClientesController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"Clientes"},
    *     summary="Listar",
    *     path="/api/clientes",
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

        $filter = Clientes::getAllByApi(500);

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

            $clientes = Clientes::select('*');

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
                            $clientes->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $clientes->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $clientes->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $clientes->where($key, $store[$key]);
                        }
                        else
                        {
                            $clientes->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $clientes = $clientes->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $clientes = $clientes->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
            }

            return view('clientes.index', [
                'exibe_filtros' => 1,
                'clientes' => $clientes,

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

        return Redirect::to('/clientes');
    }

    /**
    * @OA\Get(
    *     tags={"Clientes"},
    *     summary="Retornar",
    *     path="/api/clientes/{id}",
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

            $clientes = Clientes::find($id);

            if (!$clientes) {
              throw new Exception("Clientes não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $clientes->r_auth != 0 && $clientes->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($clientes, 200);

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
    *   tags={"Clientes"},
    *   summary="Cadastrar",
    *   path="/api/clientes",
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
    *                     property="dados_do_cliente",
    *                     description="Dados do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="codigo_do_cliente",
    *                     description="Código do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cnpj_do_cliente",
    *                     description="CNPJ do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cpf_do_cliente",
    *                     description="CPF do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="inscricao_estadual_rg",
    *                     description="Inscrição Estadual/RG",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome_do_cliente",
    *                     description="Nome do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="fantasia",
    *                     description="Fantasia",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="e_mail",
    *                     description="E-mail",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="observacao",
    *                     description="Observação",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone_1",
    *                     description="Telefone 1",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone_2",
    *                     description="Telefone 2",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="endereco_do_cliente",
    *                     description="Endereço do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cep_do_cliente",
    *                     description="CEP do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="endereco",
    *                     description="Endereço",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="numero_",
    *                     description="Número:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="complemento",
    *                     description="Complemento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="bairro",
    *                     description="Bairro",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cidade",
    *                     description="Cidade",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="estado",
    *                     description="Estado",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="ponto_de_referencia",
    *                     description="Ponto de Referência",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="anexos",
    *                     description="Anexos",
    *                     type="string"
    *                 ),
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

            $clientes = Clientes::create($store);

            return response()->json($clientes, 201);

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
    *   tags={"Clientes"},
    *   summary="Atualizar",
    *   path="/api/clientes/{id}",
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
    *                     property="dados_do_cliente",
    *                     description="Dados do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="codigo_do_cliente",
    *                     description="Código do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cnpj_do_cliente",
    *                     description="CNPJ do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cpf_do_cliente",
    *                     description="CPF do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="inscricao_estadual_rg",
    *                     description="Inscrição Estadual/RG",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome_do_cliente",
    *                     description="Nome do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="fantasia",
    *                     description="Fantasia",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="e_mail",
    *                     description="E-mail",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="observacao",
    *                     description="Observação",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone_1",
    *                     description="Telefone 1",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone_2",
    *                     description="Telefone 2",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="endereco_do_cliente",
    *                     description="Endereço do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cep_do_cliente",
    *                     description="CEP do Cliente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="endereco",
    *                     description="Endereço",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="numero_",
    *                     description="Número:",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="complemento",
    *                     description="Complemento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="bairro",
    *                     description="Bairro",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cidade",
    *                     description="Cidade",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="estado",
    *                     description="Estado",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="ponto_de_referencia",
    *                     description="Ponto de Referência",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="anexos",
    *                     description="Anexos",
    *                     type="string"
    *                 ),
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

            $clientes = Clientes::find($id);

            if (!$clientes) {
              throw new Exception("Clientes não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $clientes->r_auth != 0 && $clientes->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $clientes->update($store);

            return response()->json($clientes, 200);

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
    *     tags={"Clientes"},
    *     summary="Deletar",
    *     path="/api/clientes/{id}",
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

            $clientes = Clientes::find($id);

            if (!$clientes) {
              throw new Exception("Clientes não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $clientes->r_auth != 0 && $clientes->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $clientes->delete();

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