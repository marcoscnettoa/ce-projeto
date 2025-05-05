<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage; // # -
use Illuminate\Http\Request;

use Auth;
use Redirect;
use Session;
use Exception;

use \App\Models\User;
use \App\Models\Profiles;
use \App\Models\Logs;
use \App\Models\Permissions;

use \GuzzleHttp\Client;
use App\Repositories\UploadRepository;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;

class UsersController extends Controller
{
    // # -
    public function __construct(
        Client $client,
        UploadRepository $uploadRepository,
        ControllerRepository $controllerRepository,
        TemplateRepository $templateRepository
    ) {
        $this->client   = $client;
        $this->upload   = $controllerRepository->upload;
        $this->maxSize  = $controllerRepository->maxSize;

        $this->uploadRepository     = $uploadRepository;
        $this->controllerRepository = $controllerRepository;
        $this->templateRepository = $templateRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user)) {
            $users = User::orderBy('id', 'DESC')->limit(1000)->get();
        } else {
            $users = User::where('r_auth', $user->id)->orderBy('id', 'DESC')->limit(1000)->get();
        }

        Logs::cadastrar($user->id, ($user->name . ' visualizou a lista de usuários'));

        return view('users.index', [
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user)) {
            $profiles = Profiles::pluck('name', 'id');
        } else {
            $profiles = Profiles::where('r_auth', $user->id)->orWhere('default', 1)->pluck('name', 'id');
        }

        $profile_id = Profiles::returnDefault();

        return view('users.add', [
            'profiles' => $profiles,
            'profile_id' => $profile_id,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $data = $request->all();

            $user = new User();

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->profile_id = $data['profile_id'];
            $user->profession = $data['profession'];
            $user->username = $data['username'];

            if ($data['password']) {

                if (!preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@()$%^&*=_{}[\]:;\"'|\\<>,.\/~`±§+-]).{8,30}$/", $data['password'])) {
                    Session::flash('flash_error', "A senha deve ter de 8 a 30 caracteres e incluir ao menos um número, um símbolo, uma letra minúscula e uma maiúscula");
                    return Redirect::back();
                }

                $user->password = bcrypt($data['password']);
            }

            $count = User::count();

            if ($count >= 1000) {

                Session::flash('flash_error', "Você atingiu o limite de 1000 usuários! Por favor, contate o suporte.");

                return Redirect::back();
            }

            $user->r_auth = Auth::user()->id;

            // - #
            if($request->image) {

                if($request->hasFile("image")) {
                    if(!in_array($request->image->getClientOriginalExtension(),  ['jpg', 'jpeg', 'gif', 'png', 'bmp'])) {
                        return back()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));
                    }
                    if($request->image->getSize() > $this->maxSize) {
                        return back()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");
                    }

                    $file = base64_encode($request->image->getClientOriginalName()) . "-" . uniqid().".".$request->image->getClientOriginalExtension();

                    if(env("FILESYSTEM_DRIVER") == "s3"){
                        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("image")));
                    }else {
                        $request->image->move(public_path("images"), $file);
                    }

                    $user->image    = $file;
                }
            }
            // - #

            $user->save();

            Session::flash('flash_success', "Usuário cadastrado com sucesso!");

            Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' cadastrou o usuário: ' . $user->name));

        } catch (Exception $e) {
            Session::flash('flash_error', "Erro ao cadastrar usuário!");
        }

        return Redirect::to('/users');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user)) {
            $profiles = Profiles::pluck('name', 'id');
        } else {
            $profiles = Profiles::where('r_auth', $user->id)->orWhere('default', 1)->pluck('name', 'id');
        }

        $user = User::find($id);

        return view('users.show', [
            'user' => $user,
            'profiles' => $profiles
        ]);
    }

    public function modal($id)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user)) {
            $profiles = Profiles::pluck('name', 'id');
        } else {
            $profiles = Profiles::where('r_auth', $user->id)->orWhere('default', 1)->pluck('name', 'id');
        }

        $user = User::find($id);

        return view('users.modal', [
            'user' => $user,
            'profiles' => $profiles
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user)) {
            $profiles = Profiles::pluck('name', 'id');
        } else {
            $profiles = Profiles::where('r_auth', $user->id)->orWhere('default', 1)->pluck('name', 'id');
        }

        $user = User::find($id);

        return view('users.edit', [
            'user' => $user,
            'profiles' => $profiles
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Perfil  $perfil
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {

            $data = $request->all();

            $user = User::find($data['id']);

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->profile_id = $data['profile_id'];
            $user->profession = $data['profession'];
            $user->username = $data['username'];

            if ($data['password']) {

                if (!preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@()$%^&*=_{}[\]:;\"'|\\<>,.\/~`±§+-]).{8,30}$/", $data['password'])) {
                    Session::flash('flash_error', "A senha deve ter de 8 a 30 caracteres e incluir ao menos um número, um símbolo, uma letra minúscula e uma maiúscula");
                    return Redirect::back();
                }

                $user->password = bcrypt($data['password']);
            }

            // - #
            if($request->image) {

                if($request->hasFile("image")) {
                    if(!in_array($request->image->getClientOriginalExtension(),  ['jpg', 'jpeg', 'gif', 'png', 'bmp'])) {
                        return back()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));
                    }
                    if($request->image->getSize() > $this->maxSize) {
                        return back()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");
                    }

                    $file = base64_encode($request->image->getClientOriginalName()) . "-" . uniqid().".".$request->image->getClientOriginalExtension();

                    if(env("FILESYSTEM_DRIVER") == "s3"){
                        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("image")));
                    }else {
                        $request->image->move(public_path("images"), $file);
                    }

                    $user->image    = $file;
                }
            }
            // - #

            $user->save();

            Session::flash('flash_success', "Usuário atualizado com sucesso!");

            Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' atualizou o usuário: ' . $user->name));

        } catch (Exception $e) {
            Session::flash('flash_error', "Erro ao atualizar usuário!");
        }

        return Redirect::to('/users');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $user = Auth::user();

            User::find($id)->delete();

            Session::flash('flash_success', "Usuário excluído com sucesso");

            Logs::cadastrar($user->id, ($user->name . ' excluiu o usuário ID: ' . $id));

        } catch (\Illuminate\Database\QueryException $e) {

            Session::flash('flash_error', 'Não é possível excluir este usuário!');

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao excluir usuário!");
        }

        return Redirect::to('/users');
    }
}
