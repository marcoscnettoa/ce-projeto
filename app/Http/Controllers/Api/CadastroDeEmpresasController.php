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

use \App\Models\CadastroDeEmpresas;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

class CadastroDeEmpresasController
{
    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    /**
    * @OA\Get(
    *     tags={"CadastroDeEmpresas"},
    *     summary="Listar",
    *     path="/api/cadastro_de_empresas",
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

        $filter = CadastroDeEmpresas::getAllByApi(500);

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

            $cadastro_de_empresas = CadastroDeEmpresas::select('*');

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
                            $cadastro_de_empresas->where($key, "LIKE", "%" . $store[$key] . "%");
                        }
                        elseif ($operador[$key] == 'entre') {
                            $cadastro_de_empresas->whereBetween($key, [$store[$key], $between[$key]]);
                        }
                        else
                        {
                            $cadastro_de_empresas->where($key, $operador[$key], $store[$key]);
                        }
                    }
                    else
                    {
                        if (is_numeric($store[$key])) {
                            $cadastro_de_empresas->where($key, $store[$key]);
                        }
                        else
                        {
                            $cadastro_de_empresas->where($key, "LIKE", "%" . $store[$key] . "%");
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
                $cadastro_de_empresas = $cadastro_de_empresas->orderBy('id', 'DESC')->limit(500)->get();
            }
            else
            {
                $cadastro_de_empresas = $cadastro_de_empresas->where(function($q) use ($r_auth) {
                    $q->where('r_auth', $r_auth)
                    ->orWhere('r_auth', 0);
                })->orderBy('id', 'DESC')->limit(500)->get();
            }

            return view('cadastro_de_empresas.index', [
                'exibe_filtros' => 1,
                'cadastro_de_empresas' => $cadastro_de_empresas,

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

        return Redirect::to('/cadastro_de_empresas');
    }

    /**
    * @OA\Get(
    *     tags={"CadastroDeEmpresas"},
    *     summary="Retornar",
    *     path="/api/cadastro_de_empresas/{id}",
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

            $cadastro_de_empresas = CadastroDeEmpresas::find($id);

            if (!$cadastro_de_empresas) {
              throw new Exception("CadastroDeEmpresas não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $cadastro_de_empresas->r_auth != 0 && $cadastro_de_empresas->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($cadastro_de_empresas, 200);

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
    *   tags={"CadastroDeEmpresas"},
    *   summary="Cadastrar",
    *   path="/api/cadastro_de_empresas",
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
    *                     property="logotipo_empresa",
    *                     description="Logotipo Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="dados_da_empresa",
    *                     description="Dados da Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="codigo_empresa",
    *                     description="Codigo Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome_da_empresa",
    *                     description="Nome da Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="njuntacomercial_empresa",
    *                     description="NJuntaComercial Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="perfil_fiscal",
    *                     description="Perfil Fiscal",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome_fantasia_empresa",
    *                     description="Nome Fantasia  Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cnpj_da_empresa",
    *                     description="CNPJ da Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="inscricao_estadual",
    *                     description="Inscrição Estadual",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="email_empresa",
    *                     description="Email Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="dados_de_endereco",
    *                     description="Dados de Endereço",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cep_empresa",
    *                     description="CEP Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="endereco_empresa",
    *                     description="Endereço Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="numero_empresa",
    *                     description="Número Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="bairro_empresa",
    *                     description="Bairro Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="pais_empresa",
    *                     description="Pais Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="uf_empresa",
    *                     description="UF Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cidade_empresa",
    *                     description="Cidade Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="dados_para_contato",
    *                     description="Dados para Contato",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone_empresa",
    *                     description="Telefone Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="fax_empresa",
    *                     description="Fax Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="site_empresa",
    *                     description="Site Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nre_empresa",
    *                     description="NRE Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="segmento",
    *                     description="Segmento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="comercio",
    *                     description="Comércio",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="servico",
    *                     description="Serviço",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="industria",
    *                     description="Indústria",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="importador",
    *                     description="Importador",
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

if ($request->logotipo_empresa) {

if ($request->hasFile("logotipo_empresa")) {

    if (!in_array($request->logotipo_empresa->getClientOriginalExtension(), $this->upload)) {

        return back()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));

    }

    if ($request->logotipo_empresa->getSize() > $this->maxSize) {
        return back()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");

    }

    $file = base64_encode($request->logotipo_empresa->getClientOriginalName()) . "-" . uniqid().".".$request->logotipo_empresa->getClientOriginalExtension();

    if(env("FILESYSTEM_DRIVER") == "s3") {

        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("logotipo_empresa")));

    } else { 

        $request->logotipo_empresa->move(public_path("images"), $file);

    } 

    $store["logotipo_empresa"] = $file;

}
} else { 
    $store["logotipo_empresa"] = null;

} 
$store["comercio"] = (isset($store["comercio"]) && $store["comercio"] == "on");
$store["servico"] = (isset($store["servico"]) && $store["servico"] == "on");
$store["industria"] = (isset($store["industria"]) && $store["industria"] == "on");
$store["importador"] = (isset($store["importador"]) && $store["importador"] == "on");

            $cadastro_de_empresas = CadastroDeEmpresas::create($store);

            return response()->json($cadastro_de_empresas, 201);

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
    *   tags={"CadastroDeEmpresas"},
    *   summary="Atualizar",
    *   path="/api/cadastro_de_empresas/{id}",
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
    *                     property="logotipo_empresa",
    *                     description="Logotipo Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="dados_da_empresa",
    *                     description="Dados da Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="codigo_empresa",
    *                     description="Codigo Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome_da_empresa",
    *                     description="Nome da Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="njuntacomercial_empresa",
    *                     description="NJuntaComercial Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="perfil_fiscal",
    *                     description="Perfil Fiscal",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nome_fantasia_empresa",
    *                     description="Nome Fantasia  Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cnpj_da_empresa",
    *                     description="CNPJ da Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="inscricao_estadual",
    *                     description="Inscrição Estadual",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="email_empresa",
    *                     description="Email Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="dados_de_endereco",
    *                     description="Dados de Endereço",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cep_empresa",
    *                     description="CEP Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="endereco_empresa",
    *                     description="Endereço Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="numero_empresa",
    *                     description="Número Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="bairro_empresa",
    *                     description="Bairro Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="pais_empresa",
    *                     description="Pais Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="uf_empresa",
    *                     description="UF Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="cidade_empresa",
    *                     description="Cidade Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="dados_para_contato",
    *                     description="Dados para Contato",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="telefone_empresa",
    *                     description="Telefone Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="fax_empresa",
    *                     description="Fax Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="site_empresa",
    *                     description="Site Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="nre_empresa",
    *                     description="NRE Empresa",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="segmento",
    *                     description="Segmento",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="comercio",
    *                     description="Comércio",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="servico",
    *                     description="Serviço",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="industria",
    *                     description="Indústria",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="importador",
    *                     description="Importador",
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

            $cadastro_de_empresas = CadastroDeEmpresas::find($id);

if ($request->logotipo_empresa) {

if ($request->hasFile("logotipo_empresa")) {

    if (!in_array($request->logotipo_empresa->getClientOriginalExtension(), $this->upload)) {

        return back()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));

    }

    if ($request->logotipo_empresa->getSize() > $this->maxSize) {
        return back()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");

    }

    $file = base64_encode($request->logotipo_empresa->getClientOriginalName()) . "-" . uniqid().".".$request->logotipo_empresa->getClientOriginalExtension();

    if(env("FILESYSTEM_DRIVER") == "s3") {

        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("logotipo_empresa")));

    } else { 

        $request->logotipo_empresa->move(public_path("images"), $file);

    } 

    $store["logotipo_empresa"] = $file;

}
} else { 
    $store["logotipo_empresa"] = null;

} 
$store["comercio"] = (isset($store["comercio"]) && $store["comercio"] == "on");
$store["servico"] = (isset($store["servico"]) && $store["servico"] == "on");
$store["industria"] = (isset($store["industria"]) && $store["industria"] == "on");
$store["importador"] = (isset($store["importador"]) && $store["importador"] == "on");

            if (!$cadastro_de_empresas) {
              throw new Exception("CadastroDeEmpresas não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $cadastro_de_empresas->r_auth != 0 && $cadastro_de_empresas->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $cadastro_de_empresas->update($store);

            return response()->json($cadastro_de_empresas, 200);

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
    *     tags={"CadastroDeEmpresas"},
    *     summary="Deletar",
    *     path="/api/cadastro_de_empresas/{id}",
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

            $cadastro_de_empresas = CadastroDeEmpresas::find($id);

            if (!$cadastro_de_empresas) {
              throw new Exception("CadastroDeEmpresas não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($user) && $cadastro_de_empresas->r_auth != 0 && $cadastro_de_empresas->r_auth != $user->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $cadastro_de_empresas->delete();

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