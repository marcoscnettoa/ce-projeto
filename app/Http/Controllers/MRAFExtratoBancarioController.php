<?php

namespace App\Http\Controllers;

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

use \App\Models\MRAFExtratoBancario;
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

class MRAFExtratoBancarioController extends Controller
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

            $MRAFExtratoBancario    = MRAFExtratoBancario::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_f_extrato_bancario'));
            }

            return view('mra_f_extrato_bancario.index', [
                'exibe_filtros'         => 0,
                'MRAFExtratoBancario'   => $MRAFExtratoBancario,
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
                $MRAFExtratoBancario = MRAFExtratoBancario::where($subForm, $value)->get();
            }else {
                $MRAFExtratoBancario = MRAFExtratoBancario::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAFExtratoBancario = MRAFExtratoBancario::find($value);
            }else {
                $MRAFExtratoBancario = MRAFExtratoBancario::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAFExtratoBancario);
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
    }

    public function destroy($id)
    {
        $MRAFExtratoBancario = MRAFExtratoBancario::find($id);
        return $this->controllerRepository::destroy(new MRAFExtratoBancario(), $id, 'mra_fluxo_financeiro/mra_f_extrato_bancario');
    }
}
