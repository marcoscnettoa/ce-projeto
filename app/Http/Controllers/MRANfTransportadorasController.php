<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Redirect;
use Session;
use Validator;
use Exception;
use Response;
use DB;
use PDF;
use Log;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use \App\Models\MRANfTransportadoras;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;
use setasign\Fpdi\Fpdi;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\Filesystem\Filesystem;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;

use App\Repositories\UploadRepository;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;

class MRANfTransportadorasController extends Controller
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

    public function index()
    {
        try {

            $user       = Auth::user();

            $MRANfTransportadoras  = MRANfTransportadoras::getAll(500);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_transportadoras'));
            }

            return view('mra_transportadoras.index', [
                'exibe_filtros'            => 0,
                'MRANfTransportadoras'     => $MRANfTransportadoras,
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    public function filter(Request $request)
    {
        /* ... */
    }

    public function create($id = null)
    {
        try {

            $user = Auth::user();

            $MRANfTransportadoras     = null;
            if(!is_null($id)){
                $MRANfTransportadoras = MRANfTransportadoras::find($id);
                if(!$MRANfTransportadoras){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_nota_fiscal/mra_transportadoras');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRANfTransportadoras->r_auth != 0 && $MRANfTransportadoras->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_nota_fiscal/mra_transportadoras');
            }

            if($user){
                // Edição
                if(!is_null($MRANfTransportadoras)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_transportadoras'));
                // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_transportadoras'));
                }
            }

            return view('mra_transportadoras.add_edit', [
                'exibe_filtros'            => 0,
                'MRANfTransportadoras'     => $MRANfTransportadoras
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'nome'     => 'required'
        ],[
            'nome'     => 'O campo "Nome" é obrigatório.'
        ]);
    }

    public function store(Request $request)
    {
        try{

            $user       = Auth::user();
            $r_auth     = NULL;
            $redirect   = false;

            if($user) {
                $r_auth = $user->id;
            }

            $store = $request->all();

            if(isset($store['redirect']) && $store['redirect']){
                if (isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) {
                    $redirect = str_replace('redirect=', '', $_SERVER["QUERY_STRING"]);
                }
            }

            if(isset($store['r_auth'])){
                $r_auth = $store['r_auth'];
            }

            $validator  = $this->validator($store);
            if($validator->fails()){
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            DB::beginTransaction();

            $MRANfTransportadoras                       = null;
            if(isset($store['id'])){
                $MRANfTransportadoras                   = MRANfTransportadoras::find($store['id']);
                if(!$MRANfTransportadoras){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_nota_fiscal/mra_transportadoras');
                }
            }
            $acao                                       = 'edit';
            if(is_null($MRANfTransportadoras)){
                $MRANfTransportadoras                   = new MRANfTransportadoras();
                $acao                                   = 'add';
            }

            $MRANfTransportadoras->cnpj                 = (isset($store['cnpj'])?str_replace(['_'],'',$store['cnpj']):null);
            $MRANfTransportadoras->cpf                  = (isset($store['cpf'])?str_replace(['_'],'',$store['cpf']):null);
            $MRANfTransportadoras->ie                   = (isset($store['ie'])?$store['ie']:null);
            $MRANfTransportadoras->nome                 = (isset($store['nome'])?$store['nome']:null);
            $MRANfTransportadoras->apelido              = (isset($store['apelido'])?$store['apelido']:null);
            $MRANfTransportadoras->cont_telefone        = (isset($store['cont_telefone'])?str_replace(['_'],'',$store['cont_telefone']):null);
            $MRANfTransportadoras->cont_email           = (isset($store['cont_email'])?$store['cont_email']:null);
            $MRANfTransportadoras->cont_emails_nf       = (isset($store['cont_emails_nf'])?$store['cont_emails_nf']:null);
            $MRANfTransportadoras->end_cep              = (isset($store['end_cep'])?str_replace(['_'],'',$store['end_cep']):null);
            $MRANfTransportadoras->end_rua              = (isset($store['end_rua'])?$store['end_rua']:null);
            $MRANfTransportadoras->end_numero           = (isset($store['end_numero'])?$store['end_numero']:null);
            $MRANfTransportadoras->end_bairro           = (isset($store['end_bairro'])?$store['end_bairro']:null);
            $MRANfTransportadoras->end_estado           = (isset($store['end_estado'])?$store['end_estado']:null);
            $MRANfTransportadoras->end_cidade           = (isset($store['end_cidade'])?$store['end_cidade']:null);
            $MRANfTransportadoras->status               = (isset($store['status'])?$store['status']:null);
            $MRANfTransportadoras->r_auth               = $r_auth;

            $MRANfTransportadoras->save();

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Transportadora atualizada com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_transportadoras|'.$acao.': atualizou ID: ' . $MRANfTransportadoras->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Transportadora cadastrada com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_transportadoras|'.$acao.': cadastrou ID: ' . $MRANfTransportadoras->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_transportadoras|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_nota_fiscal/mra_transportadoras');
    }

    public function copy($id)
    {
        /* ... */
    }

    public function show($id)
    {
        /* ... */
    }

    public function modal($id)
    {
        /* ... */
    }

    public function ajax($value, Request $request)
    {
        $value   = str_replace('__H2F__', '/', $value);
        $subForm = $request->get('subForm');
        $user    = Auth::user();
        if($subForm){
            if(Permissions::permissaoModerador($user)){
                $MRANfTransportadoras = MRANfTransportadoras::where($subForm, $value)->get();
            }else {
                $MRANfTransportadoras = MRANfTransportadoras::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRANfTransportadoras = MRANfTransportadoras::find($value);
            }else {
                $MRANfTransportadoras = MRANfTransportadoras::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRANfTransportadoras);
    }

    public function pdf($id)
    {
        /* ... */
    }

    public function importar(Request $request)
    {
        /* ... */
    }

    public function edit($id)
    {
        return $this->create($id);
    }

    public function update(Request $request)
    {
        return $this->store($request);
    }

    public function destroy($id)
    {
        $MRANfTransportadoras = MRANfTransportadoras::find($id);
        return $this->controllerRepository::destroy(new MRANfTransportadoras(), $id, 'mra_nota_fiscal/mra_transportadoras');
    }
}
