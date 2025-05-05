<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Laravel\Passport\Client;
use Laravel\Passport\PersonalAccessClient;
use Laravel\Passport\Events\RefreshTokenCreated;
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
use Illuminate\Support\Facades\Hash;
use \App\Models\User;

class AuthController
{
    protected function validator(array $data, $id = '')
    {
        return Validator::make($data, [
            'username' => "required",
            'password' => "required",
        ]);
    }

    /**
     * @OA\Post(
     *     tags={"Autenticação"},
     *     summary="Efetuar login",
     *     path="/api/auth/login",
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"username","password"},
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     description="Nome de usuário"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="Senha do usuário"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Login efetuado com sucesso",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="access_token",
     *                     type="string",
     *                     description="Access Token de autenticação"
     *                 ),
     *                 @OA\Property(
     *                    property="refresh_token",
     *                    type="string",
     *                    description="Refresh Token de autenticação"
     *                ),
     *                 @OA\Property(
     *                    property="expires_at",
     *                    type="int",
     *                    description="Expiração do access_token"
     *                ),
     *                 @OA\Property(
     *                    property="id",
     *                    type="int",
     *                    description="ID do usuário"
     *                ),
     *                 @OA\Property(
     *                    property="name",
     *                    type="string",
     *                    description="Nome do usuário"
     *                ),
     *                 @OA\Property(
     *                    property="username",
     *                    type="string",
     *                    description="Login de acesso do usuário"
     *                ),
     *                 @OA\Property(
     *                    property="email",
     *                    type="string",
     *                    description="E-mail do usuário"
     *                ),
     *                 @OA\Property(
     *                    property="image",
     *                    type="string",
     *                    description="Imagem de perfil do usuário"
     *                ),
     *                 @OA\Property(
     *                    property="created_at",
     *                    type="string",
     *                    description="Data de criação do usuário"
     *                )
     *             )
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        try {

            $this->create_personal_access_client();

            if($request->isJson()) {
                $payload = $request->json()->all();
            }else {
                $payload = $request->all();
            }

            $validator = $this->validator($payload);

            if ($validator->fails()) {
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            $credentials = [
                'username' => $payload['username'],
                'password' => $payload['password']
            ];

            if(!Auth::attempt($credentials)) {
                return response()->json([
                    'error'                 => 'invalid_credentials',
                    'error_description'     => 'Credenciais inválidas!'
                ], 401);
            }

            $user                   = Auth::user();
            // ! Revoga acessos anteriores, isso garantirar acesso único também
            // de acordo com a regra 1 acesso apenas por sessão
            $user->tokens()->delete();
            $createToken            = $user->createToken('TokenAPI');
            $createToken_expires_at = $createToken->token->expires_at->getTimestamp();
            $user->access_token     = $createToken->accessToken;
            $user->refresh_token    = password_hash($user->access_token,PASSWORD_BCRYPT);
            $user->save();

            return response()->json([
                'id'            => (int) $user->id,
                'name'          => $user->name,
                'username'      => $user->username,
                'email'         => $user->email,
                //'s3'            => (ENV('FILEKEY')?ENV('FILEKEY'):false),
                'image'         => $user->image,
                'created_at'    => $user->created_at,
                //'token' => $token,
                'access_token'  => $user->access_token,
                'refresh_token' => $user->refresh_token,
                'expires_at'    => $createToken_expires_at,
                'profiles'      => [
                    'id'            => (int) $user->profile_id,
                    'name'          => (($user->Profile and !is_null($user->Profile->name))?$user->Profile->name:null),
                    'administrator' => (($user->Profile and !is_null($user->Profile->administrator))?$user->Profile->administrator:0),
                    'moderator'     => (($user->Profile and !is_null($user->Profile->moderator))?$user->Profile->moderator:0),
                    'default'       => (($user->Profile and !is_null($user->Profile->default))?$user->Profile->default:0)
                ]
            ], 200);

        }catch(\Exception $e) {
            Log::error('Api\AuthController - login -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível efetuar autenticação, tente novamente!'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     tags={"Autenticação"},
     *     summary="Efetuar troca autenticação login",
     *     path="/api/auth/refresh/token",
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"refresh_token"},
     *                 @OA\Property(
     *                     property="refresh_token",
     *                     type="string",
     *                     description="Refresh Token recebido na Autenticação"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Renovar token autenticação/login",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="access_token",
     *                     type="string",
     *                     description="Access Token de autenticação"
     *                 ),
     *                 @OA\Property(
     *                    property="refresh_token",
     *                    type="string",
     *                    description="Refresh Token de autenticação"
     *                ),
     *                 @OA\Property(
     *                    property="expires_at",
     *                    type="int",
     *                    description="Expiração do access_token"
     *                ),
     *                 @OA\Property(
     *                    property="id",
     *                    type="int",
     *                    description="ID do usuário"
     *                ),
     *                 @OA\Property(
     *                    property="name",
     *                    type="string",
     *                    description="Nome do usuário"
     *                ),
     *                 @OA\Property(
     *                    property="username",
     *                    type="string",
     *                    description="Login de acesso do usuário"
     *                ),
     *                 @OA\Property(
     *                    property="email",
     *                    type="string",
     *                    description="E-mail do usuário"
     *                ),
     *                 @OA\Property(
     *                    property="image",
     *                    type="string",
     *                    description="Imagem de perfil do usuário"
     *                ),
     *                 @OA\Property(
     *                    property="created_at",
     *                    type="string",
     *                    description="Data de criação do usuário"
     *                )
     *             )
     *         )
     *     )
     * )
     */
    public function refresh_token(Request $request)
    {
        try {

            if($request->isJson()) {
                $payload = $request->json()->all();
            }else {
                $payload = $request->all();
            }

            $user = User::where('refresh_token', $payload['refresh_token'])->first();

            if (!$user) {
                return response()->json(['error' => 'Refresh Token do Usuário não encontrado ou sem permissão para executar essa ação! #001'], 404);
            }

            $user->tokens()->delete();
            $createToken            = $user->createToken('TokenAPI');
            $createToken_expires_at = $createToken->token->expires_at->getTimestamp();
            $user->access_token     = $createToken->accessToken;
            $user->refresh_token    = password_hash($user->access_token,PASSWORD_BCRYPT);
            $user->save();

            return response()->json([
                'id'            => (int) $user->id,
                'name'          => $user->name,
                'username'      => $user->username,
                'email'         => $user->email,
                //'s3'            => (ENV('FILEKEY')?ENV('FILEKEY'):false),
                'image'         => $user->image,
                'created_at'    => $user->created_at,
                //'token' => $token,
                'access_token'  => $user->access_token,
                'refresh_token' => $user->refresh_token,
                'expires_at'    => $createToken_expires_at,
                'profiles'      => [
                    'id'            => (int) $user->profile_id,
                    'name'          => (($user->Profile and !is_null($user->Profile->name))?$user->Profile->name:null),
                    'administrator' => (($user->Profile and !is_null($user->Profile->administrator))?$user->Profile->administrator:0),
                    'moderator'     => (($user->Profile and !is_null($user->Profile->moderator))?$user->Profile->moderator:0),
                    'default'       => (($user->Profile and !is_null($user->Profile->default))?$user->Profile->default:0)
                ]
            ], 200);

        }catch(\Exception $e) {
            Log::error('Api\AuthController - refresh_token -| '. $e->getMessage());
            return response()->json([
                'error'                 => 'exception_error',
                'error_description'     => 'Não foi possível efetuar renovação do token, tente novamente!'
            ], 500);
        }
    }

    // :: 1º Acesso - Quando não existir o ( Client -| Personal Access Client )
    // :: Cria Automaticamente! Sem a necessidade de utilizar o Terminal
    public function create_personal_access_client(){
        $PassportClient = Client::where('name','Personal Access Client')->first();
        if(!$PassportClient){
            Client::truncate();
            PersonalAccessClient::truncate();

            $Client                          = new Client();
            $Client->name                    = 'Personal Access Client';
            $Client->secret                  = Str::random(40);
            $Client->redirect                = '';
            $Client->personal_access_client  = true;
            $Client->password_client         = false;
            $Client->revoked                 = false;
            $Client->save();

            $PersonalAccessClient            = new PersonalAccessClient();
            $PersonalAccessClient->client_id = $Client->id;
            $PersonalAccessClient->save();
        }
    }

}
