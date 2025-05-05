<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\UploadRepository;
use Carbon\Carbon;
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

use \App\Models\Profiles;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;

class ProfilesController
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
        $name_required              = true;

        // ! update
        if($id != ''){
            if(!isset($data['name'])){       $name_required         = false; }
        }

        return Validator::make($data, [
            'name'         => ($name_required?'required':''),
        ]);
    }

    /**
     * @OA\Get(
     *     tags={"Profiles"},
     *     summary="Listar",
     *     path="/api/profiles",
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
        if($request->isJson()) {
            $store = $request->json()->all();
        }else {
            $store = $request->all();
        }

        $Filter = Profiles::getAllByApi(500);

        if(!empty($store)){
            foreach ($store as $key => $value) {
                if(gettype($value) == 'string'){
                    $Filter->where($key, "LIKE", "%" . $value . "%");
                }else{
                    $Filter->where($key, $value);
                }
            }
        }

        $Filter = $Filter->get();

        return response()->json($Filter, 200);
    }

    /**
     * @OA\Get(
     *     tags={"Profiles"},
     *     summary="Retornar",
     *     path="/api/profiles/{id}",
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
     *     @OA\Response(response="200", description=""),
     * ),
     *
     */
    public function show($id)
    {
        try{

            $Auth       = Auth::user();

            $Profiles   = Profiles::find($id);

            if(!$Profiles){
                throw new Exception("Perfil não encontrado!", 404);
            }

            if(!Permissions::permissaoModerador($Auth)){
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($Profiles, 200);

        }catch(\Exception $e){
            Log::error('Api\ProfilesController - show -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível exibir os dados do perfil de acesso, tente novamente!'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *   tags={"Profiles"},
     *   summary="Cadastrar",
     *   path="/api/profiles",
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
     *                 required={"name"},
     *                 @OA\Property(
     *                     property="name",
     *                     description="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="moderator",
     *                     description="moderator",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="administrator",
     *                     description="administrator",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="default",
     *                     description="default",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="r_auth",
     *                     description="r_auth",
     *                     type="integer"
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

            $Auth               = Auth::user();

            if($request->isJson()) {
                $store = $request->json()->all();
            }else {
                $store = $request->all();
            }

            $validator          = $this->validator($store);
            if($validator->fails()){
                return response()->json(
                    array('type' => 'error', 'message' => $validator->errors(), 'code' => 400), 400
                );
            }

            if(!isset($store['r_auth'])){
                $store['r_auth'] = $Auth->id;
            }

            $profiles = Profiles::create($store);
            $profiles->refresh();

            return response()->json($profiles, 201);

        }catch(Exception $e){
            Log::error('Api\ProfilesController - store -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível salvar os dados do perfil de acesso, tente novamente!'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *      tags={"Profiles"},
     *      summary="Atualizar",
     *      path="/api/profiles/{id}",
     *      @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(
     *               type="int",
     *           )
     *      ),
     *      @OA\Parameter(
     *          name="Authorization",
     *          in="header",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="Bearer <token>",
     *              description="Bearer token para autorização"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          description="Input data format",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"name"},
     *                  @OA\Property(
     *                      property="name",
     *                      description="name",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="moderator",
     *                      description="moderator",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="administrator",
     *                      description="administrator",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="default",
     *                      description="default",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="r_auth",
     *                      description="r_auth",
     *                      type="integer"
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(response="200", description=""),
     * )
     */
    public function update(Request $request, $id)
    {
        try {

            $Auth               = Auth::user();

            if($request->isJson()) {
                $store = $request->json()->all();
            }else {
                $store = $request->all();
            }

            $profiles           = Profiles::find($id);

            if (!$profiles) {
                throw new Exception("Pessoas não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($Auth) && $profiles->r_auth != 0 && $profiles->r_auth != $Auth->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $validator          = $this->validator($store, $id);
            if($validator->fails()){
                return response()->json(
                    array('type' => 'error', 'message' => $validator->errors(), 'code' => 400), 400
                );
            }

            if(!isset($store['r_auth'])){
                $store['r_auth'] = $Auth->id;
            }

            $profiles->update($store);

            return response()->json($profiles, 201);

        }catch(Exception $e){
            Log::error('Api\ProfilesController - update -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível alterar os dados do perfil de acesso, tente novamente!'
            ], 500);
        }

    }

    /**
     * @OA\Delete(
     *     tags={"Profiles"},
     *     summary="Deletar",
     *     path="/api/profiles/{id}",
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
     *     @OA\Response(response="200", description=""),
     * )
     */
    public function destroy($id)
    {
        try {

            $Auth     = Auth::user();

            $profiles = Profiles::find($id);

            if (!$profiles) {
                throw new Exception("Perfil não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($Auth) && $profiles->r_auth != 0 && $profiles->r_auth != $Auth->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $profiles->delete();

            return response()->json(null, 204);

        }catch(Exception $e){
            Log::error('Api\ProfilesController - destroy -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível excluir os dados do perfil de acesso, tente novamente!'
            ], 500);
        }
    }


    /**
     * @OA\Put(
     *   tags={"Profiles"},
     *   summary="Atualizar perfil de acesso padrão",
     *   path="/api/profiles/default",
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
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"profile_id"},
     *                 @OA\Property(
     *                     property="profile_id",
     *                     description="profile_id",
     *                     type="int"
     *                 ),
     *             )
     *         )
     *     ),
     *   @OA\Response(response="200", description=""),
     * )
     */
    public function profile_default(Request $request)
    {
        try {

            $Auth           = Auth::user();

            if($request->isJson()) {
                $store      = $request->json()->all();
            }else {
                $store      = $request->all();
            }

            if(!isset($store['profile_id'])){
                return response()->json([
                    'error'                 => 'profile_id_not_found',
                    'error_description'     => 'Não foi informado profile_id padrão!'
                ], 404);
            }

            $Profiles = Profiles::find($store['profile_id']);
            if(!$Profiles){
                return response()->json([
                    'error'                 => 'profile_id_not_found',
                    'error_description'     => 'Não foi encontrado o profile informado, não está disponível!'
                ], 404);
            }

            Profiles::where('default', 1)->update(['default' => 0]);

            $Profiles->default = 1;
            $Profiles->r_auth  = $Auth->id;
            $Profiles->save();

            return response()->json(['message'=>'Perfil de acesso padrão atualizado com sucesso!'], 200);

        }catch(\Exception $e){
            Log::error('Api\ProfilesController - profile_default -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível definir perfil de acesso padrão, tente novamente!'
            ], 500);
        }
    }
}
