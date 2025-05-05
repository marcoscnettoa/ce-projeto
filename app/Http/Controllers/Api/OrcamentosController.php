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

use \App\Models\Orcamentos;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

class OrcamentosController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"Orcamentos"},
    *     summary="Listar",
    *     path="/api/orcamentos",
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

        $filter = Orcamentos::getAllByApi(500);

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

            $orcamentos = Orcamentos::select('*');

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
                            $orcamentos->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $orcamentos->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $orcamentos->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $orcamentos->where($key, $store[$key]);
                        }
                        else
                        {
                            $orcamentos->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $orcamentos = $orcamentos->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $orcamentos = $orcamentos->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
            }

            return view('orcamentos.index', [
                'exibe_filtros' => 1,
                'orcamentos' => $orcamentos,

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

        return Redirect::to('/orcamentos');
    }

    /**
    * @OA\Get(
    *     tags={"Orcamentos"},
    *     summary="Retornar",
    *     path="/api/orcamentos/{id}",
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

            $orcamentos = Orcamentos::find($id);

            if (!$orcamentos) {
              throw new Exception("Orcamentos não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $orcamentos->r_auth != 0 && $orcamentos->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($orcamentos, 200);

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
    *   tags={"Orcamentos"},
    *   summary="Cadastrar",
    *   path="/api/orcamentos",
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
    *                     property="numero",
    *                     description="Número",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data",
    *                     description="Data",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="clilente",
    *                     description="Clilente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="vendedor",
    *                     description="Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="produto",
    *                     description="Produto",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="periodo_de",
    *                     description="Período de",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="a",
    *                     description="A",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="viajantes",
    *                     description="Viajantes",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="criancas",
    *                     description="Crianças",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="idade",
    *                     description="Idade",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="malas_despachadas",
    *                     description="MALAS DESPACHADAS",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="inclui",
    *                     description="Inclui",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nao_inclui",
    *                     description="Não inclui",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_por_passageiro",
    *                     description="VALOR POR PASSAGEIRO",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="passageiros",
    *                     description="Passageiros",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="quantidade",
    *                     description="Quantidade",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_por_pessoa",
    *                     description="Valor por pessoa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_total",
    *                     description="Valor total",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="outros_servicos",
    *                     description="OUTROS SERVIÇOS",
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

            $orcamentos = Orcamentos::create($store);

            return response()->json($orcamentos, 201);

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
    *   tags={"Orcamentos"},
    *   summary="Atualizar",
    *   path="/api/orcamentos/{id}",
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
    *                     property="numero",
    *                     description="Número",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data",
    *                     description="Data",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="clilente",
    *                     description="Clilente",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="vendedor",
    *                     description="Vendedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="produto",
    *                     description="Produto",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="periodo_de",
    *                     description="Período de",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="a",
    *                     description="A",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="viajantes",
    *                     description="Viajantes",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="criancas",
    *                     description="Crianças",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="idade",
    *                     description="Idade",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="malas_despachadas",
    *                     description="MALAS DESPACHADAS",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="inclui",
    *                     description="Inclui",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nao_inclui",
    *                     description="Não inclui",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_por_passageiro",
    *                     description="VALOR POR PASSAGEIRO",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="passageiros",
    *                     description="Passageiros",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="quantidade",
    *                     description="Quantidade",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_por_pessoa",
    *                     description="Valor por pessoa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_total",
    *                     description="Valor total",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="outros_servicos",
    *                     description="OUTROS SERVIÇOS",
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

            $orcamentos = Orcamentos::find($id);

            if (!$orcamentos) {
              throw new Exception("Orcamentos não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $orcamentos->r_auth != 0 && $orcamentos->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $orcamentos->update($store);

            return response()->json($orcamentos, 200);

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
    *     tags={"Orcamentos"},
    *     summary="Deletar",
    *     path="/api/orcamentos/{id}",
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

            $orcamentos = Orcamentos::find($id);

            if (!$orcamentos) {
              throw new Exception("Orcamentos não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $orcamentos->r_auth != 0 && $orcamentos->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $orcamentos->delete();

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