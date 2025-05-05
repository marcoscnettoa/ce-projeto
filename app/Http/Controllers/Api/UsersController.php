<?php

namespace App\Http\Controllers\Api;

use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\UploadRepository;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Password;
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

use \App\Models\User;
use \App\Models\Logs;
use \App\Models\Permissions;
use \App\Models\Profiles;
use \GuzzleHttp\Client;

class UsersController
{

    use SendsPasswordResetEmails, ResetsPasswords {
        ResetsPasswords::credentials insteadof SendsPasswordResetEmails;
        ResetsPasswords::broker insteadof SendsPasswordResetEmails;
        ResetsPasswords::credentials as ResetsPasswords_credentials;
        ResetsPasswords::broker as ResetsPasswords_broker;
    }

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
        $username_required          = true;
        $password_required          = true;
        $profile_id_required        = true;
        $email_required             = true;

        // ! update
        if($id != ''){
            if(!isset($data['name'])){       $name_required         = false; }
            if(!isset($data['username'])){   $username_required     = false; }
            if(!isset($data['password'])){   $password_required     = false; }
            if(!isset($data['profile_id'])){ $profile_id_required   = false; }
            if(!isset($data['email'])){      $email_required        = false; }
        }

        return Validator::make($data, [
            'name'         => ($name_required?'required':''),
            'username'     => ($username_required?'required':''),
            'password'     => ($password_required?'required':''),
            'profile_id'   => ($profile_id_required?'required':''),
            'email'        => ($email_required?'required':''),
        ]);
    }

    protected function validator_password_recovery(array $data){
        return Validator::make($data, [
            'email'        => 'required',
        ]);
    }

    protected function validator_password_reset(array $data){
        return Validator::make($data, [
            'token'                     => 'required',
            'email'                     => 'required|email',
            'password'                  => 'required|confirmed|min:8'
        ]);
    }

    /**
     * @OA\Get(
     *     tags={"User"},
     *     summary="Listar",
     *     path="/api/users",
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
    public function index(Request $request)
    {
        try {

            if($request->isJson()){
                $store  = $request->json()->all();
            }else {
                $store  = $request->all();
            }

            $Filter     = User::getAllByApi(500);

            if(!empty($store)){
                foreach($store as $key => $value){
                    if(gettype($value) == 'string'){
                        $Filter->where($key, "LIKE", "%" . $value . "%");
                    }else {
                        $Filter->where($key, $value);
                    }
                }
            }

            $Filter = $Filter->get();

            return response()->json($Filter, 200);

        }catch(\Exception $e){
            Log::error('Api\UsersController - index -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível listar os usuários, tente novamente!'
            ], 500);
        }

    }

    /**
     * @OA\Get(
     *     tags={"User"},
     *     summary="Retornar",
     *     path="/api/users/{id}",
     *     @OA\Parameter(
     *         name="id",
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
     * ),
     *
     */
    public function show($id)
    {
        try {
            $Auth   = Auth::user();

            $User   = User::find($id);

            if (!$User) {
                throw new Exception("Usuário não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($Auth)) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            return response()->json($User, 200);

        }catch(\Exception $e) {
            Log::error('Api\UsersController - show -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível exibir os dados do usuários, tente novamente!'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *   tags={"User"},
     *   summary="Cadastrar",
     *   path="/api/users",
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
     *                 required={"name","username","email","password"},
     *                 @OA\Property(
     *                     property="name",
     *                     description="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     description="username",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="profile_id",
     *                     description="profile_id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="profession",
     *                     description="profession",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     description="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     description="image",
     *                     type="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="r_auth",
     *                     description="r_auth",
     *                     type="integer"
     *                 ),
     *             )
     *         )
     *     ),
     *   @OA\Response(response="201", description="")
     * )
     */
    public function store(Request $request)
    {
        try {

            $Auth               = Auth::user();

            if($request->isJson()) {
                $store          = $request->json()->all();
            }else {
                $store          = $request->all();
            }

            $validator          = $this->validator($store);
            if($validator->fails()){
                return response()->json(
                    array('type' => 'error', 'message' => $validator->errors(), 'code' => 400), 400
                );
            }

            $User               = new User();

            if(!isset($store['r_auth'])){
                $store['r_auth'] = $Auth->id;
            }

            $User->fill($store);

            if (isset($store['password'])) {
                if (!preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@()$%^&*=_{}[\]:;\"'|\\<>,.\/~`±§+-]).{8,30}$/", $store['password'])) {
                    return response()->json(
                        array(
                            'type' => 'error',
                            'message' => 'A senha deve ter de 8 a 30 caracteres e incluir ao menos um número, um símbolo, uma letra minúscula e uma maiúscula',
                            'code' => 400
                        ), 400
                    );
                }
                $User->password = bcrypt($store['password']);
            }

            if($request->image) {
                if($request->hasFile("image")) {
                    if(!in_array($request->image->getClientOriginalExtension(),  ['jpg', 'jpeg', 'gif', 'png', 'bmp'])) {
                        return response()->json(
                            array(
                                'type' => 'error',
                                'message' => 'Tipo de arquivo não permitido! Extensões permitidas: '. implode(", ", $this->upload),
                                'code' => 400
                            ), 400
                        );
                    }
                    if($request->image->getSize() > $this->maxSize) {
                        return response()->json(
                            array(
                                'type' => 'error',
                                'message' => 'Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB',
                                'code' => 400
                            ), 400
                        );
                    }
                    $file           = base64_encode($request->image->getClientOriginalName()) . "-" . uniqid().".".$request->image->getClientOriginalExtension();
                    if(env("FILESYSTEM_DRIVER") == "s3"){
                        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("image")));
                    }else {
                        $request->image->move(public_path("images"), $file);
                    }
                    $User->image   = $file;
                }
            }

            $User->save();
            $User->refresh();

            return response()->json($User, 201);

        }catch (Exception $e){
            Log::error('Api\UsersController - store -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível salvar o usuário, tente novamente!'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *   tags={"User"},
     *   summary="Registrar usuário tela inicial",
     *   path="/api/users/register",
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name","username","email","password"},
     *                 @OA\Property(
     *                     property="name",
     *                     description="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     description="username",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="password",
     *                     type="string"
     *                 )
     *                 @OA\Property(
     *                     property="email",
     *                     description="email",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *   @OA\Response(response="201", description="")
     * )
     */
    public function store_register(Request $request)
    {
        try {

            if($request->isJson()) {
                $store  = $request->json()->all();
            }else {
                $store  = $request->all();
            }

            $store['profile_id'] = Profiles::returnDefault();

            $validator  = $this->validator($store);
            if($validator->fails()){
                return response()->json(
                    array('type' => 'error', 'message' => $validator->errors(), 'code' => 400), 400
                );
            }

            $User       = new User();

            $User->fill($store);

            if(isset($store['password'])) {
                if (!preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@()$%^&*=_{}[\]:;\"'|\\<>,.\/~`±§+-]).{8,30}$/", $store['password'])) {
                    return response()->json(
                        array(
                            'type' => 'error',
                            'message' => 'A senha deve ter de 8 a 30 caracteres e incluir ao menos um número, um símbolo, uma letra minúscula e uma maiúscula',
                            'code' => 400
                        ), 400
                    );
                }
                $User->password = bcrypt($store['password']);
            }

            $User->save();
            $User->refresh();

            return response()->json($User, 201);

        }catch(\Exception $e) {
            Log::error('Api\UsersController - store_register -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível efetuar o cadastro, tente novamente!'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *      tags={"User"},
     *      summary="Atualizar",
     *      path="/api/users/{id}",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="int",
     *          )
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
     *                  required={"name","username","email","password"},
     *                  @OA\Property(
     *                      property="name",
     *                      description="name",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="username",
     *                      description="username",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="password",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="profile_id",
     *                      description="profile_id",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="profession",
     *                      description="profession",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="email",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="image",
     *                      description="image",
     *                      type="binary"
     *                  ),
     *                  @OA\Property(
     *                      property="r_auth",
     *                      description="r_auth",
     *                      type="integer"
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(response="200", description="")
     * )
     */
    public function update(Request $request, $id)
    {
        try {

            $Auth      = Auth::user();

            if($request->isJson()) {
                $store = $request->json()->all();
            }else {
                $store = $request->all();
            }

            $validator  = $this->validator($store, $id);
            if($validator->fails()){
                return response()->json(
                    array('type' => 'error', 'message' => $validator->errors(), 'code' => 400), 400
                );
            }

            $User       = User::find($id);

            if(!isset($store['r_auth'])){
                $store['r_auth'] = $Auth->id;
            }

            $User->fill($store);

            if (isset($store['password'])) {
                if (!preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@()$%^&*=_{}[\]:;\"'|\\<>,.\/~`±§+-]).{8,30}$/", $store['password'])) {
                    return response()->json(
                        array(
                            'type' => 'error',
                            'message' => 'A senha deve ter de 8 a 30 caracteres e incluir ao menos um número, um símbolo, uma letra minúscula e uma maiúscula',
                            'code' => 400
                        ), 400
                    );
                }
                $User->password = bcrypt($store['password']);
            }

            if($request->image) {
                if($request->hasFile("image")) {
                    if(!in_array($request->image->getClientOriginalExtension(),  ['jpg', 'jpeg', 'gif', 'png', 'bmp'])) {
                        return response()->json(
                            array(
                                'type' => 'error',
                                'message' => 'Tipo de arquivo não permitido! Extensões permitidas: '. implode(", ", $this->upload),
                                'code' => 400
                            ), 400
                        );
                    }
                    if($request->image->getSize() > $this->maxSize) {
                        return response()->json(
                            array(
                                'type' => 'error',
                                'message' => 'Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB',
                                'code' => 400
                            ), 400
                        );
                    }
                    $file           = base64_encode($request->image->getClientOriginalName()) . "-" . uniqid().".".$request->image->getClientOriginalExtension();
                    if(env("FILESYSTEM_DRIVER") == "s3"){
                        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("image")));
                    }else {
                        $request->image->move(public_path("images"), $file);
                    }
                    $User->image   = $file;
                }
            }

            $User->save();

            return response()->json($User, 201);

        }catch(Exception $e){
            Log::error('Api\UsersController - update -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível atualizar o usuário, tente novamente!'
            ], 500);
        }

    }

    /**
     * @OA\Delete(
     *     tags={"User"},
     *     summary="Deletar",
     *     path="/api/users/{id}",
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
    public function destroy($id)
    {
        try {

            $Auth   = Auth::user();

            $User   = User::find($id);

            if (!$User) {
                throw new Exception("Usuário não encontrado!", 404);
            }

            if (!Permissions::permissaoModerador($Auth) && $User->r_auth != 0 && $User->r_auth != $Auth->id) {
                throw new Exception('Você não tem permissão para executar esta ação!');
            }

            $User->delete();

            return response()->json(null, 204);

        }catch(\Exception $e){
            Log::error('Api\UsersController - destroy -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível excluir o usuário, tente novamente!'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     tags={"User"},
     *     summary="Recuperar acesso com e-mail",
     *     path="/api/users/password/recovery",
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email"},
     *                 @OA\Property(
     *                     property="email",
     *                     description="email",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *   @OA\Response(response="200", description="")
     * )
     */
    public function password_recovery(Request $request){
        try {

            if($request->isJson()) {
                $store = $request->json()->all();
            }else {
                $store = $request->all();
            }

            $validator  = $this->validator_password_recovery($store);
            if($validator->fails()){
                return response()->json(['type' => 'error', 'message' => $validator->errors(), 'code' => 400], 400);
            }

            $response = $this->broker()->sendResetLink(
                $request->only('email')
            );

            return $response == \Illuminate\Auth\Passwords\PasswordBroker::RESET_LINK_SENT
                ? response()->json(['message' => 'Link redefinição enviado com sucesso!'], 200)
                : response()->json(['type' => 'error', 'message' => 'Não foi possível enviar o link de redefinição', 'code' => 400], 400);

        }catch(\Exception $e){
            Log::error('Api\UsersController - password_recovery -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível enviar redefinição de senha, tente novamente!'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *      tags={"User"},
     *      summary="Alterar senha de acesso",
     *      path="/api/users/password/reset",
     *      @OA\RequestBody(
     *          description="Input data format",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  required={"token","email","password","password_confirmation"},
     *                  @OA\Property(
     *                      property="token",
     *                      description="token",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="email",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="password",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password_confirmation",
     *                      description="password_confirmation",
     *                      type="string"
     *                  ),
     *              )
     *          )
     *      ),
     *   @OA\Response(response="200", description="")
     * )
     */
    public function password_reset(Request $request){
        try {

            if($request->isJson()) {
                $store = $request->json()->all();
            }else {
                $store = $request->all();
            }

            $validator  = $this->validator_password_reset($store);
            if($validator->fails()){
                return response()->json(
                    array('type' => 'error', 'message' => $validator->errors(), 'code' => 400), 400
                );
            }

            if(!preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@()$%^&*=_{}[\]:;\"'|\\<>,.\/~`±§+-]).{8,30}$/", $store['password'])) {
                return response()->json(
                    [
                        'type' => 'error',
                        'message' => 'A senha deve ter de 8 a 30 caracteres e incluir ao menos um número, um símbolo, uma letra minúscula e uma maiúscula',
                        'code' => 400
                    ], 400
                );
            }

            $response = $this->ResetsPasswords_broker()->reset(
                $this->ResetsPasswords_credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
            );

            if($response == Password::PASSWORD_RESET) {
                return response()->json(['message' => 'Senha redefinida com sucesso!'], 200);
            }else {
                return response()->json(['type' => 'error', 'message' => trans($response), 'code' => 400], 400);
            }

        }catch(\Exception $e){
            Log::error('Api\UsersController - password_reset -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível redefinir a senha, tente novamente!'
            ], 500);
        }
    }
}
