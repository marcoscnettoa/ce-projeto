<?php

namespace App\Http\Controllers\Api;

use App\Library\Load;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\Request;

use Auth;
use Redirect;
use Route;
use Session;
use Validator;
use Exception;
use Response;
use DB;
use Log;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use \App\Models\User;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

class SetupController
{

    public function __construct(
        Client $client,
        UploadRepository $uploadRepository,
        ControllerRepository $controllerRepository,
        TemplateRepository $templateRepository
    ) {
        $this->client               = $client;
        $this->upload               = $controllerRepository->upload;
        $this->maxSize              = $controllerRepository->maxSize;
        $this->uploadRepository     = $uploadRepository;
        $this->controllerRepository = $controllerRepository;
        $this->templateRepository   = $templateRepository;
    }

    /**
     * @OA\Get(
     *     tags={"Setup"},
     *     summary="Setup Inicial",
     *     path="/api/setup",
     *     @OA\Response(
     *         response="200",
     *         description="Recebe informações do setup inicial antes da autenticação",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                    property="hash_id",
     *                    type="string",
     *                    description="Hash ID do sistema/projeto"
     *                ),
     *                 @OA\Property(
     *                    property="name",
     *                    type="string",
     *                    description="Nome do sistema"
     *                ),
     *                 @OA\Property(
     *                    property="register",
     *                    type="int",
     *                    description="Usuário pode se registrar"
     *                ),
     *                 @OA\Property(
     *                    property="preview",
     *                    type="int",
     *                    description="Ambiente de homologação"
     *                ),
     *                 @OA\Property(
     *                    property="s3",
     *                    type="string",
     *                    description="Código repositório s3"
     *                ),
     *                 @OA\Property(
     *                    property="s3_url",
     *                    type="string",
     *                    description="Url repositório s3"
     *                )
     *             )
     *         )
     *     )
     * )
     */
    public function setup()
    {
        try {
            $Auth           = Auth::user();

            $SetupJson      = [];
            $Setup          = base_path('r_setup.json');

            if(file_exists($Setup)){
                $Setup      = file_get_contents($Setup);
                $SetupJson  = json_decode($Setup);

                if(json_last_error() !== $SetupJson and !empty($SetupJson)) {

                    $s3     = false;
                    if(ENV('FILEKEY')){
                        $s3 = [
                            'repository'    => ENV('FILEKEY'),
                            'url'           => (ENV('FILEKEY')?ENV('URLS3'):false),
                        ];
                    }

                    $RespJson = [
                        'hash_id'           =>  (ENV('APP_HASH_ID')?ENV('APP_HASH_ID'):null),
                        'name'              =>  (ENV('APP_NAME')?ENV('APP_NAME'):null),
                        'logo'              =>  (ENV('LOGO')?'https://dashboard.xxxxrxxxapps.com/images/'.ENV('LOGO'):null),
                        'favicon'           =>  (ENV('FAVICON')?ENV('FAVICON'):null),
                        'register'          =>  (ENV('ENV_ENABLE_CADASTRO')?1:0),
                        'preview'           =>  (ENV('APP_EDIT')?1:0),
                        's3'                =>  $s3,
                        //'builder'   =>  null,
                        //'skins'     =>  null,
                        //'api'       =>  null,
                        //'publish'   =>  null,
                    ];

                    // ! Autenticado
                    if($Auth){
                        // ! Configurações Build
                        $SetupJson              = $this->setup_config($SetupJson);
                        $RespJson['modules']    = (isset($SetupJson->modules)?$SetupJson->modules:null);
                    }

                    return response()->json($RespJson, 200);
                }
            }

            return response()->json([
                'error'                 => 'no_setup',
                'error_description'     => 'Erro no retorno do Setup. Não possuí Setup!'
            ], 401);

        }catch(\Exception $e){
            if($Auth){
                Log::error('Api\SetupController - auth_setup -| '. $e->getMessage());
            }else {
                Log::error('Api\SetupController - setup -| '. $e->getMessage());
            }
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Erro no retorno do Setup, tente novamente!'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     tags={"Autenticação"},
     *     summary="Autenticação Setup",
     *     path="/api/auth/setup",
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
     *     @OA\Response(
     *         response="200",
     *         description="Recebe informações do setup e configurações essenciais pela autenticação do usuário disponibilizado.",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                    property="hash_id",
     *                    type="string",
     *                    description="Hash ID do sistema/projeto"
     *                ),
     *                 @OA\Property(
     *                    property="name",
     *                    type="string",
     *                    description="Nome do sistema"
     *                ),
     *                 @OA\Property(
     *                    property="register",
     *                    type="int",
     *                    description="Usuário pode se registrar"
     *                ),
     *                 @OA\Property(
     *                    property="preview",
     *                    type="int",
     *                    description="Ambiente de homologação"
     *                ),
     *                 @OA\Property(
     *                    property="s3",
     *                    type="string",
     *                    description="Código repositório s3"
     *                ),
     *                 @OA\Property(
     *                    property="s3_url",
     *                    type="string",
     *                    description="Url repositório s3"
     *                )
     *             )
     *         )
     *     )
     * )
     */
    public function auth_setup(){
        return $this->setup();
    }

    // ! Configurações Build
    public function setup_config($SetupJson){

        $Auth                           = Auth::user();

        // :: Verificando Permissões - ( ! Auth User )
        $controllers                    = PermissionsController::essential_controls_permissions();

        $menu_parent                    = null;
        foreach($SetupJson->modules as $Key => $Module){

            // :: Remove os 'actions'
            unset($Module->actions);

            // :: ! - Tratamento de Referência -
            $s_route                    = mb_substr(Load::getCleanName($Module->title), 0, 32);
            if(isset($Module->route) && in_array($Module->route,['events','indicators','link_pagamento','users','profiles','reports','logs'])){
                $s_controlller          = Load::getClassName($Module->route, true);
                $s_role                 = 'App\\Http\\Controllers\\'.$s_controlller.'Controller';
            }else {
                $s_controlller          = Load::getClassName($Module->title, true);
                $s_role                 = 'App\\Http\\Controllers\\'.$s_controlller.'Controller';
            }
            // - ::

            $permissions                = [];
            foreach($controllers as $Ctr){
                if($Ctr['controller'] == $s_role && Permissions::permissaoUsuario($Auth,$Ctr['role'])){
                    array_push($permissions, $Ctr['action']);
                }
            }

            if(empty($permissions)){ unset($SetupJson->modules[$Key]); continue; }

            // :: Módulo Fixo
            $Module->fixed_module       = (isset($Module->fixed_module)?$Module->fixed_module:false);

            // :: Rota - URL
            $Module->route              = (isset($Module->route)?$Module->route:$s_route);

            // :: Hierarquia - Exibição Menu | Submenu
            if(isset($Module->menu) && $Module->menu == false){
                $menu_parent            = $Module->route;
            }elseif(!isset($Module->menu)){
                $menu_parent            = null;
            }
            if(isset($Module->menu) && $Module->menu == true && $menu_parent != null){
                $Module->menu_parent    = $menu_parent;
            }
            // - ::

            // :: Módulo -| Glyphicon
            if(!isset($Module->glyphicon)){
                $Module->glyphicon      = "glyphicon glyphicon-th-list";
            }

            // :: Módulo -| Fields - ( Campos Disponíveis )
            /*if(isset($Module->fields)){
                foreach($Module->fields as $Field){
                    // :: Módulo - Fields -| Glyphicon
                    if(!isset($Field->buttonGlyphicon)){
                        $Field->buttonGlyphicon = "glyphicon glyphicon-th-list";
                    }
                }
            }*/

            // :: Permissões
            $Module->permissions = $permissions;
        }

        // :: Reindexar -| Modules
        $SetupJson->modules = array_values($SetupJson->modules);

        return $SetupJson;
    }

}
