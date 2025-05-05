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

use \App\Models\ContasAPagar;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

use \App\Models\CadastroDeEmpresas; 
use \App\Models\Fornecedores; 
use \App\Models\FormasDePagamentos; 

class ContasAPagarController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"ContasAPagar"},
    *     summary="Listar",
    *     path="/api/contas_a_pagar",
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

        $filter = ContasAPagar::getAllByApi(500);

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

            $contas_a_pagar = ContasAPagar::select('*');

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
                            $contas_a_pagar->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $contas_a_pagar->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $contas_a_pagar->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $contas_a_pagar->where($key, $store[$key]);
                        }
                        else
                        {
                            $contas_a_pagar->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $contas_a_pagar = $contas_a_pagar->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $contas_a_pagar = $contas_a_pagar->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
            }

            $cadastro_de_empresas_nome_fantas = CadastroDeEmpresas::list(10000, "nome_fantasia_empresa");

        $fornecedores_fornecedor = Fornecedores::list(10000, "fornecedor");

        $formas_de_pagamentos_forma_de_pa = FormasDePagamentos::list(10000, "forma_de_pagamento");

            return view('contas_a_pagar.index', [
                'exibe_filtros' => 1,
                'contas_a_pagar' => $contas_a_pagar,
                'cadastro_de_empresas_nome_fantas' => $cadastro_de_empresas_nome_fantas,
            'fornecedores_fornecedor' => $fornecedores_fornecedor,
            'formas_de_pagamentos_forma_de_pa' => $formas_de_pagamentos_forma_de_pa,

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

        return Redirect::to('/contas_a_pagar');
    }

    /**
    * @OA\Get(
    *     tags={"ContasAPagar"},
    *     summary="Retornar",
    *     path="/api/contas_a_pagar/{id}",
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

            $contas_a_pagar = ContasAPagar::find($id);

            if (!$contas_a_pagar) {
              throw new Exception("ContasAPagar não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $contas_a_pagar->r_auth != 0 && $contas_a_pagar->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($contas_a_pagar, 200);

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
    *   tags={"ContasAPagar"},
    *   summary="Cadastrar",
    *   path="/api/contas_a_pagar",
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
    *                     property="referente_a",
    *                     description="Referente à",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="fornecedor",
    *                     description="Fornecedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="tipo_de_documento",
    *                     description="Tipo de documento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="n_do_documento",
    *                     description="N° do documento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="portador",
    *                     description="Portador",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="descricao_do_pagamento",
    *                     description="Descrição do Pagamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="n_da_ordem_de_compra",
    *                     description="N° da ordem de compra",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_do_vencimento",
    *                     description="Data do Vencimento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_a_pagar",
    *                     description="Valor à Pagar",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="tipo_do_valor",
    *                     description="Tipo do valor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="parcelas",
    *                     description="Parcelas",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="n_de_parcelas",
    *                     description="N° de Parcelas",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="primeira_data",
    *                     description="Primeira Data",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="recepcao",
    *                     description="Recepção",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_do_pagamento",
    *                     description="Data do Pagamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_pago",
    *                     description="Valor Pago",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="forma_de_pagamento",
    *                     description="Forma de pagamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="status",
    *                     description="Status",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="comprovante_de_pagamento",
    *                     description="Comprovante de Pagamento",
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

if ($request->comprovante_de_pagamento) {

if ($request->hasFile("comprovante_de_pagamento")) {

    if (!in_array($request->comprovante_de_pagamento->getClientOriginalExtension(), $this->upload)) {

        return back()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));

    }

    if ($request->comprovante_de_pagamento->getSize() > $this->maxSize) {
        return back()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");

    }

    $file = base64_encode($request->comprovante_de_pagamento->getClientOriginalName()) . "-" . uniqid().".".$request->comprovante_de_pagamento->getClientOriginalExtension();

    if(env("FILESYSTEM_DRIVER") == "s3") {

        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("comprovante_de_pagamento")));

    } else { 

        $request->comprovante_de_pagamento->move(public_path("images"), $file);

    } 

    $store["comprovante_de_pagamento"] = $file;

}
} else { 
    $store["comprovante_de_pagamento"] = null;

} 

            $contas_a_pagar = ContasAPagar::create($store);

            return response()->json($contas_a_pagar, 201);

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
    *   tags={"ContasAPagar"},
    *   summary="Atualizar",
    *   path="/api/contas_a_pagar/{id}",
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
    *                     property="referente_a",
    *                     description="Referente à",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="fornecedor",
    *                     description="Fornecedor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="tipo_de_documento",
    *                     description="Tipo de documento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="n_do_documento",
    *                     description="N° do documento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="portador",
    *                     description="Portador",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="descricao_do_pagamento",
    *                     description="Descrição do Pagamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="n_da_ordem_de_compra",
    *                     description="N° da ordem de compra",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_do_vencimento",
    *                     description="Data do Vencimento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_a_pagar",
    *                     description="Valor à Pagar",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="tipo_do_valor",
    *                     description="Tipo do valor",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="parcelas",
    *                     description="Parcelas",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="n_de_parcelas",
    *                     description="N° de Parcelas",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="primeira_data",
    *                     description="Primeira Data",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="recepcao",
    *                     description="Recepção",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="data_do_pagamento",
    *                     description="Data do Pagamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="valor_pago",
    *                     description="Valor Pago",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="forma_de_pagamento",
    *                     description="Forma de pagamento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="status",
    *                     description="Status",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="comprovante_de_pagamento",
    *                     description="Comprovante de Pagamento",
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

            $contas_a_pagar = ContasAPagar::find($id);

if ($request->comprovante_de_pagamento) {

if ($request->hasFile("comprovante_de_pagamento")) {

    if (!in_array($request->comprovante_de_pagamento->getClientOriginalExtension(), $this->upload)) {

        return back()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));

    }

    if ($request->comprovante_de_pagamento->getSize() > $this->maxSize) {
        return back()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");

    }

    $file = base64_encode($request->comprovante_de_pagamento->getClientOriginalName()) . "-" . uniqid().".".$request->comprovante_de_pagamento->getClientOriginalExtension();

    if(env("FILESYSTEM_DRIVER") == "s3") {

        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("comprovante_de_pagamento")));

    } else { 

        $request->comprovante_de_pagamento->move(public_path("images"), $file);

    } 

    $store["comprovante_de_pagamento"] = $file;

}
} else { 
    $store["comprovante_de_pagamento"] = null;

} 

            if (!$contas_a_pagar) {
              throw new Exception("ContasAPagar não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $contas_a_pagar->r_auth != 0 && $contas_a_pagar->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $contas_a_pagar->update($store);

            return response()->json($contas_a_pagar, 200);

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
    *     tags={"ContasAPagar"},
    *     summary="Deletar",
    *     path="/api/contas_a_pagar/{id}",
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

            $contas_a_pagar = ContasAPagar::find($id);

            if (!$contas_a_pagar) {
              throw new Exception("ContasAPagar não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $contas_a_pagar->r_auth != 0 && $contas_a_pagar->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $contas_a_pagar->delete();

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