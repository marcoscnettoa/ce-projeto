<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Illuminate\Support\Facades\Storage;
use Redirect;
use Session;
use Exception;

use \App\Models\Profiles;
use \App\Models\Logs;
use \App\Models\Permissions;

class ProfilesController extends Controller
{
    public $imgUpload = array('jpg', 'jpeg', 'gif', 'png', 'bmp');

    public $maxSize = 5242880;

    public function index(Request $request)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user)) {
            $profiles = Profiles::orderBy('id', 'DESC')->limit(1000)->get();
        } else {
            $profiles = Profiles::where('r_auth', $user->id)->orWhere('default', 1)->orderBy('id', 'DESC')->limit(1000)->get();
        }

        Logs::cadastrar($user->id, ($user->name . ' visualizou a lista de perfis'));

        return view('profiles.index', ['profiles' => $profiles]);
    }

    public function create()
    {
        $user = Auth::user();

        return view('profiles.add');
    }

    public function store(Request $request)
    {
        try {

            $profiles = new Profiles();

            $profiles->name = $request->get('name');
            $profiles->moderator = $request->get('moderator');
            $profiles->administrator = $request->get('administrator');

            $profiles->r_auth = Auth::user()->id;

            $profiles->save();

            Session::flash('flash_success', "Perfil cadastrado com sucesso!");

            Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' cadastrou o perfil: ' . $profiles->name));

        } catch (Exception $e) {
            Session::flash('flash_error', "Erro ao cadastrar perfil!");
        }

        return Redirect::to('/profiles');
    }

    public function show($id)
    {
        $profiles = Profiles::find($id);

        return view('profiles.show', [
            'profiles' => $profiles,
        ]);
    }

    public function edit($id)
    {
        $profiles = Profiles::find($id);

        return view('profiles.edit', [
            'profiles' => $profiles,
        ]);
    }

    public function update(Request $request)
    {
        try {

            $profiles = Profiles::find($request->get('id'));

            $profiles->name = $request->get('name');
            $profiles->moderator = $request->get('moderator');
            $profiles->administrator = $request->get('administrator');

            $profiles->save();

            Session::flash('flash_success', "Perfil atualizado com sucesso!");

            Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' atualizou o perfil: ' . $profiles->name));

        } catch (Exception $e) {
            Session::flash('flash_error', "Erro ao atualizar perfil!");
        }

        return Redirect::to('/profiles');
    }

    public function destroy($id)
    {
        try {

            $profiles = Profiles::find($id)->delete();

            Session::flash('flash_success', "Perfil excluído com sucesso!");

            Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' excluiu o perfil ID: ' . $id));

        } catch (\Illuminate\Database\QueryException $e) {

            Session::flash('flash_error', 'Não é possível excluir este perfil!');

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao excluir perfil!");
        }

        return Redirect::to('/profiles');
    }

    public function defaultProfile(Request $request)
    {
        try {

            Profiles::where('default', 1)->update(['default' => 0]);

            $profile = Profiles::find($request->get('default'));

            $profile->default = 1;

            $profile->save();

            Session::flash('flash_success', "Perfil padrão atualizado com sucesso!");

            Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' informou um perfil padrão: ' . $profile->name));

        } catch (Exception $e) {
            Session::flash('flash_error', "Erro ao atualizado perfil padrão!");
        }

        return Redirect::to('/profiles');
    }

    public function perfil(Request $request)
    {
        $user = Auth::user();

        if ($request->isMethod('post')) {

            $data = $request->all();

            $user->name = $data['name'];
            $user->email = $data['email'];

            if (isset($data['profile_id']) && !empty($data['profile_id'])) {

                $profile = Profiles::find($data['profile_id']);

                if ($profile->administrator && (isset($user->profile) && !$user->profile->administrator)) {
                    Session::flash('flash_error', "Você não tem permissão para mudar para este perfil.");
                    return Redirect::to('/');
                } else {
                    $user->profile_id = $profile->id;
                }

            }

            $user->profession = $data['profession'];

            $user->username = $data['username'];

            /*if ($request->image) {

                if (!in_array($request->image->getClientOriginalExtension(), $this->imgUpload)) {
                    return back()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->imgUpload));
                }

                if ($request->image->getSize() > $this->maxSize) {
                    return back()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");
                }

                $img = time().'.'.$request->image->getClientOriginalExtension();

                $request->image->move(public_path('images'), $img);

                $user->image = $img;

            }*/
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

            if ($data['password']) {

                if (!preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@()$%^&*=_{}[\]:;\"'|\\<>,.\/~`±§+-]).{8,30}$/", $data['password'])) {
                    Session::flash('flash_error', "A senha deve ter de 8 a 30 caracteres e incluir ao menos um número, um símbolo, uma letra minúscula e uma maiúscula");
                    return Redirect::back();
                }

                $user->password = bcrypt($data['password']);
            }

            $user->save();

            Logs::cadastrar($user->id, ($user->name . ' atualizou o próprio perfil'));

            Session::flash('flash_success', "Perfil atualizado com sucesso!");

            return Redirect::back();
        }

        if (Permissions::permissaoModerador($user)) {
            $profiles = Profiles::pluck('name', 'id');
        } else {
            $profiles = Profiles::where('r_auth', $user->id)->orWhere('default', 1)->pluck('name', 'id');
        }

        return view('profiles.perfil', [
            'user' => $user,
            'profiles' => $profiles
        ]);
    }

}
