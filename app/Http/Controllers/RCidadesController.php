<?php
// # MXTera
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

use \App\Models\Logs;
use \App\Models\Permissions;
use App\Models\RCidades;
use App\Models\REstados;
use \GuzzleHttp\Client;
use setasign\Fpdi\Fpdi;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\Filesystem\Filesystem;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;

use App\Repositories\UploadRepository;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;

class RCidadesController extends Controller
{
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

    public function index()
    {
        /* ... */
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
        // # Estado
        $Uf = REstados::find($value);
        if(!$value){ return Response::json([]); }
        $value = $Uf->sigla;
        // - #

        $value = str_replace('__H2F__', '/', $value);
        $subForm = $request->get('subForm');
        $user = Auth::user();
        if($subForm)
        {
            if (Permissions::permissaoModerador($user))
            {
                $lista_ncm = RCidades::where($subForm, $value)->get();
            }
            else
            {
               $lista_ncm = RCidades::where($subForm, $value)->where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->get();
            }
        }
        else
        {
            if (Permissions::permissaoModerador($user))
            {
                $lista_ncm = RCidades::find($value);
            }
            else
            {
               $lista_ncm = RCidades::where(function($q) use ($user) {
                    $q->whereNull('r_auth')
                    ->orWhere('r_auth', $user->id)
                    ->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($lista_ncm);
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
        /* ... */
    }

    public function destroy($id)
    {
        /* ... */
    }
}