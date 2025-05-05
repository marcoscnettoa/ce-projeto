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

use \App\Models\MRAGraficos;
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

class MRAGraficosController extends Controller
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

            $MRAGraficos  = MRAGraficos::find(1);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de atualização do módulo mra_graficos'));
            }

            return view('mra_graficos.add_edit', [
                'exibe_filtros' => 0,
                'MRAGraficos'     => $MRAGraficos,
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

    public function create()
    {
        /* ... */
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [

        ],[

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

            $store      = $request->all();

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

            $MRAGraficos                      = MRAGraficos::find(1); // ! Fixo - No momento Liberado apenas para 1 Gráfico*
            $acao                             = 'edit';
            if(!$MRAGraficos){
                $MRAGraficos                  = new MRAGraficos();
                $MRAGraficos->id              = 1;
                $acao                         = 'add';
            }

            $MRAGraficos->status              = (isset($store['status'])?$store['status']:0);
            $MRAGraficos->posicao             = (isset($store['posicao'])?$store['posicao']:0);
            $MRAGraficos->css                 = (isset($store['css'])?$store['css']:null);
            $MRAGraficos->codigo              = (isset($store['codigo'])?$store['codigo']:null);
            $MRAGraficos->html                = (isset($store['html'])?$store['html']:null);
            $MRAGraficos->script              = (isset($store['script'])?$store['script']:null);
            $MRAGraficos->r_auth              = $r_auth;

            $MRAGraficos->save();

            DB::commit();

            Session::flash('flash_success', "Gráficos atualizado com sucesso!");

            if($user) {
                Logs::cadastrar($user->id, ($user->name . ' mra_graficos|'.$acao.': atualizou ID: ' . $MRAGraficos->id));
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_graficos|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_graficos');
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
        /* ... */
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
        /* ... */
    }

    public function update(Request $request)
    {
        return $this->store($request);
    }

    public function destroy($id)
    {
        /* ... */
    }
}
