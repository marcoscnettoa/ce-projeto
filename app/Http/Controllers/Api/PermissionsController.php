<?php

namespace App\Http\Controllers\Api;

use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\UploadRepository;
use Carbon\Carbon;
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

use \App\Models\Profiles;
use \App\Models\User;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

class PermissionsController
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

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ]);
    }

    public function permissions(Request $request, $id, $type)
    {
        try {

            $User           = User::find($id);
            if(!$User) {
                return response()->json([
                    'error'                 => 'permissions_user_not_found',
                    'error_description'     => 'Não foi possível encontrar as permissões do usuário solicitado!'
                ], 404);
            }

            if($type == 'user'){
                $Permissions    = Permissions::whereNull('profile_id')->where('user_id', $id)->pluck('role', 'id');
            }elseif($type == 'profile'){
                $Permissions    = Permissions::where('profile_id', $id)->pluck('role', 'id');
            }
            $Permissions_arr    = $Permissions->toArray();

            $controllers        = $this::essential_controls_permissions();
            foreach($controllers as $Key => $Ctr){
                unset($controllers[$Key]['controller'],$controllers[$Key]['action']);
                $controllers[$Key]['description'] = $this::action_description_translation($Ctr['action']);
                $controllers[$Key]['authorized']  = (in_array($Ctr['role'],$Permissions_arr)?true:false);
            }

            sort($controllers);

            return response()->json($controllers, 200);

        }catch(\Exception $e){
            if($type == 'user'){
                Log::error('Api\PermissionsController - permissions_user -| '. $e->getMessage());
                return response()->json([
                    'error'                 => 'exception_error',
                    'error_description'     => 'Não foi possível listar as permissões do usuário, tente novamente!'
                ], 500);
            }elseif($type == 'profile'){
                Log::error('Api\PermissionsController - permissions_profile -| '. $e->getMessage());
                return response()->json([
                    'error'                 => 'exception_error',
                    'error_description'     => 'Não foi possível listar as permissões do perfil de acesso, tente novamente!'
                ], 500);
            }
        }
    }


    public function permissions_edit(Request $request, $id, $type)
    {
        try {

            $Auth           = Auth::user();

            if($request->isJson()) {
                $store      = $request->json()->all();
            }else {
                $store      = $request->all();
            }

            if($type == 'user'){
                $User           = User::find($id);
                if(!$User) {
                    return response()->json([
                        'error'                 => 'permissions_user_not_found',
                        'error_description'     => 'Não foi possível encontrar o usuário solicitado!'
                    ], 404);
                }
            }elseif($type == 'profile'){
                $Profiles           = Profiles::find($id);
                if(!$Profiles) {
                    return response()->json([
                        'error'                 => 'permissions_profile_not_found',
                        'error_description'     => 'Não foi possível encontrar o perfil de acesso solicitado!'
                    ], 404);
                }
            }

            if(is_array($store) && count($store)){
                foreach($store as $Per){
                    if(!isset($Per['role']) || !isset($Per['authorized'])){ continue; }

                    $Permissions     = Permissions::where('role',$Per['role']);
                    if($type == 'user') {
                        $Permissions = $Permissions->whereNull('profile_id')->where('user_id',$User->id);
                    }elseif($type == 'profile') {
                        $Permissions = $Permissions->where('profile_id',$id);
                    }
                    $Permissions     = $Permissions->first();

                    if($Per['authorized']){
                        if(!$Permissions){
                            Permissions::create([
                                'user_id'    => ($type=='user'?$id:null),
                                'profile_id' => ($type=='profile'?$id:null),
                                'role'       => $Per['role'],
                                'authorized' => true,
                                'r_auth'     => $Auth->id,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                        }
                    }else {
                        if($Permissions){ $Permissions->delete(); }
                    }
                }
            }else {
                return response()->json([
                    'error'                 => 'permissions_user_empty_permissions',
                    'error_description'     => 'Não foi passado as lista de permissões do usuário!'
                ], 404);
            }

            if($type == 'user'){
                return response()->json(['message'=>'Permissões do usuário atualizado com sucesso!'], 200);
            }elseif($type == 'profile') {
                return response()->json(['message'=>'Permissões do perfil de acesso atualizado com sucesso!'], 200);
            }

        }catch(\Exception $e){
            if($type == 'user'){
                Log::error('Api\PermissionsController - permissions_user_edit -| '. $e->getMessage());
                return response()->json([
                    'error'                 => 'exception_error',
                    'error_description'     => 'Não foi possível alterar as permissões do usuário, tente novamente!'
                ], 500);
            }elseif($type == 'profile'){
                Log::error('Api\PermissionsController - permissions_profile_edit -| '. $e->getMessage());
                return response()->json([
                    'error'                 => 'exception_error',
                    'error_description'     => 'Não foi possível alterar as permissões do perfil de acesso, tente novamente!'
                ], 500);
            }
        }
    }

    /**
     * @OA\Get(
     *     tags={"Permissão de Acesso"},
     *     summary="Listar permissões do usuário",
     *     path="/api/permissions/user/{id}",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="int",
     *          )
     *     ),
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
     *     @OA\Response(response="200", description="")
     * )
     */
    public function permissions_user(Request $request, $id)
    {
        return $this->permissions($request,$id,'user');
    }

    /**
     * @OA\Put(
     *     tags={"Permissão de Acesso"},
     *     summary="Alterar permissões do usuário",
     *     path="/api/permissions/user/{id}",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
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
     *         required=true,
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="role",
     *                         type="string",
     *                         description="Função/Role do Controller"
     *                     ),
     *                     @OA\Property(
     *                         property="authorized",
     *                         type="boolean",
     *                         description="Indica se está autorizado ou não"
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     */
    public function permissions_user_edit(Request $request, $id)
    {
        return $this->permissions_edit($request,$id,'user');
    }

    /**
     * @OA\Get(
     *     tags={"Permissão de Acesso"},
     *     summary="Listar permissões do perfil de acesso",
     *     path="/api/permissions/profile/{profile_id}",
     *     @OA\Parameter(
     *         name="profile_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="int",
     *         )
     *     ),
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
     *     @OA\Response(response="200", description="")
     * )
     */
    public function permissions_profile(Request $request, $profile_id)
    {
        return $this->permissions($request,$profile_id,'profile');
    }

    /**
     * @OA\Put(
     *     tags={"Permissão de Acesso"},
     *     summary="Alterar permissões do perfil de acesso",
     *     path="/api/permissions/profile/{profile_id}",
     *     @OA\Parameter(
     *         name="profile_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="int",
     *         )
     *     ),
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
     *         required=true,
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="role",
     *                         type="string",
     *                         description="Função/Role do Controller"
     *                     ),
     *                     @OA\Property(
     *                         property="authorized",
     *                         type="boolean",
     *                         description="Indica se está autorizado ou não"
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     */
    public function permissions_profile_edit(Request $request, $profile_id)
    {
        return $this->permissions_edit($request,$profile_id,'profile');
    }

    // Controllers Essenciais de Permissões do ( Auth / Usuário )
    public static function essential_controls_permissions(){
        try {
            $controllers        = [];
            foreach(Route::getRoutes()->getRoutes() as $Key => $route){
                $action         = $route->getAction();
                if(array_key_exists('controller', $action))
                {
                    $controller_action_exp = explode('@',$action['controller']);
                    if(count($controller_action_exp) != 2){ continue; }

                    if(
                        strpos($action['controller'], '@edit')   ==  false &&  // ! update
                        strpos($action['controller'], '@create') ==  false &&  // ! store
                        strpos($action['controller'], 'Auth')    === false &&
                        strpos($action['controller'], 'Api')     === false &&
                        !in_array($action['controller'],[
                            'App\Http\Controllers\HomeController@index',
                            'App\Http\Controllers\HomeController@swagger',
                            'Laravel\Sanctum\Http\Controllers\CsrfCookieController@show',
                            'Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController',
                            'Spatie\LaravelIgnition\Http\Controllers\HealthCheckController',
                            'Spatie\LaravelIgnition\Http\Controllers\UpdateConfigController',
                        ])
                    ){

                        $ca_controller                       = $controller_action_exp[0];
                        $ca_action                           = $controller_action_exp[1];

                        $controllers[$Key]['group']          = str_replace(['App\\Http\\Controllers\\','Controller'],'',$ca_controller);
                        $controllers[$Key]['role']           = $action['controller'];
                        $controllers[$Key]['controller']     = $ca_controller;
                        $controllers[$Key]['action']         = $ca_action;
                    }
                }
            }
            return $controllers;
        }catch(\Exception $e){
            Log::error('Api\PermissionsController - essential_controls_permissions -| '. $e->getMessage());
            return [];
        }
    }

    // :: Tradução - action / func. / ações
    public static function action_description_translation($action){
        $actions = [
            'get'       => 'Buscar',
            'index'     => 'Listar',
            'create'    => 'Criar',
            'store'     => 'Cadastrar',
            'show'      => 'Visualizar',
            'edit'      => 'Editar',
            'update'    => 'Atualizar',
            'destroy'   => 'Deletar',
            'modal'     => 'Modal',
            'pdf'       => 'Gerar PDF',
            'ajax'      => 'Auto Completar',
            'importar'  => 'Importar',
            'filter'    => 'Utilizar Filtros de Pesquisa',
            'copy'      => 'Duplicar Linha'
        ];

        if(array_key_exists($action,$actions)){
            return $actions[$action];
        }else {
            return $action;
        }
    }
}
