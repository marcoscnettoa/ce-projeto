<?php

namespace App\Http\Controllers;

use App\Models\MRAFContasBancarias;
use App\Models\MRAFExtratoBancario;
use Carbon\Carbon;
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

use \App\Models\MRAFFluxoCaixa;
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

class MRAFFluxoCaixaController extends Controller
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

    public function index(Request $request)
    {
        try {

            $user                   = Auth::user();

            $MRAFFluxoCaixa    = MRAFFluxoCaixa::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_f_fluxo_caixa'));
            }

            return view('mra_f_fluxo_caixa.index', [
                'exibe_filtros'     => 0,
                'MRAFFluxoCaixa'        => $MRAFFluxoCaixa,
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    public function filter(Request $request)
    {
        return $this->index($request);
    }

    public function create($id = null)
    {
        /* ... */
    }

    protected function validator(array $data, $id = ''){
        /* ... */
    }

    public function store(Request $request)
    {
        /* ... */
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
        $value                       = str_replace('__H2F__', '/', $value);
        $subForm                     = $request->get('subForm');
        $user                        = Auth::user();
        if($subForm){
            if(Permissions::permissaoModerador($user)){
                $MRAFFluxoCaixa = MRAFFluxoCaixa::where($subForm, $value)->get();
            }else {
                $MRAFFluxoCaixa = MRAFFluxoCaixa::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAFFluxoCaixa = MRAFFluxoCaixa::find($value);
            }else {
                $MRAFFluxoCaixa = MRAFFluxoCaixa::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAFFluxoCaixa);
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
        //return $this->create($id);
    }

    public function update(Request $request)
    {
        /* ... */
        //return $this->store($request);
    }

    public function destroy($id)
    {
        $MRAFFluxoCaixa = MRAFFluxoCaixa::find($id);
        return $this->controllerRepository::destroy(new MRAFFluxoCaixa(), $id, 'mra_fluxo_financeiro/mra_f_fluxo_caixa');
    }
}
