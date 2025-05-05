<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\UploadRepository;
use Auth;
use DB;
use Exception;
use Log;
use Redirect;
use Response;
use Session;
use App\Logs;
use App\Permissions;
use GuzzleHttp\Client;
use App\models\RR;
use App\Models\RNfNfeTs;
use App\Models\RNfNfseTs;
use App\Http\Controllers\RTecnoSpeedController as RTecnoSpeed;
use App\Models\RNfLogTs;
use App\Models\RNfConfiguracoesTs;

class RNfTsController
{
    public function __construct(
        Client $client,
        UploadRepository $uploadRepository,
        ControllerRepository $controllerRepository,
        TemplateRepository $templateRepository,
        RTecnoSpeed $tecnospeed
    ) {
        $this->client = $client;
        $this->upload = $controllerRepository->upload;
        $this->maxSize = $controllerRepository->maxSize;

        $this->uploadRepository = $uploadRepository;
        $this->controllerRepository = $controllerRepository;
        $this->templateRepository = $templateRepository;
        $this->tecnospeed = $tecnospeed;
    }
    
    public function webhook(Request $request)
    {
        try {
            // sleep(10);

            DB::beginTransaction();

            Log::info("Token Webhook - ".json_encode($request->header('token_webhook')));
            Log::info("Webhook TecnoSpeed - ".json_encode($request->all()));

            // Iniciando LOG e salvando dados da requisição
            $nf_log                     = new RNfLogTs();
            $nf_log->acao               = 'Webhook';
            $nf_log->response_webhook   = json_encode($request->all());

            // Verificando se o token foi passado no header da requisição
            if(!$request->header('token_webhook')){
                return response()->json(['Token não encotrado!'], 404);
            }

            // Verificando se o token é válido
            $config_empresa = RNfConfiguracoesTs::find(1);

            if ($request->header('token_webhook') != env('APP_HASH_ID')) {
                return response()->json(['Token inválido!'], 401);
            }

            // Log::info("Webhook TecnoSpeed - ".json_encode($request->all()));
           
            $tipo = (isset($request->documento) && $request->documento == 'nfse')  ? '/nfse' : '/nfe';

            // Recuperando Nfe ou Nfse
            if ($tipo == '/nfe') {
                $nota_fiscal = RNfNfeTs::where('nf_response_id', $request->id)->first();
                $field = 'nf_id';
            }else {
                $nota_fiscal = RNfNfseTs::where('nf_response_id', $request->id)->first();
                $field = 'nfse_id';
            }

            if(!$nota_fiscal) {
                return response()->json(['Nota fiscal não encontrada!'], 404);
            }

            // Formatando datas
            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['emissao', 'autorizacao', 'dataAutorizacao', 'cancelamento', 'dataCancelamento'])) {
                    $request[$key] = str_replace('/', '-', $value);
                    $request[$key] = date('Y-m-d', strtotime($request[$key]));
                }
            }

            // Recuperando autor da solciitação de cancelamento
            if (isset($request->status) && $request->status == 'CANCELADO' || isset($request->situacao) && $request->situacao == 'CANCELADO') {
                $nf_log_solicitante = RNfLogTs::select('id', 'acao', 'autor')->where($field, $nota_fiscal->id)->where('acao', 'cancelar')->first();
            }

            if ($tipo == '/nfe') {
                $nota_fiscal->nf_status                    = isset($request->status) ? $request->status : null;
                $nota_fiscal->nf_numero                    = isset($request->numero) ? $request->numero : null;
                $nota_fiscal->nf_chave                     = isset($request->chave) ? $request->chave : null;
                $nota_fiscal->nf_response_dataAutorizacao  = isset($request->dataAutorizacao) ? $request->dataAutorizacao : null;
                $nota_fiscal->nf_response_protocol         = isset($request->protocolo) ? $request->protocolo : null;
            }else {
                $nota_fiscal->nf_status                    = isset($request->situacao) ? $request->situacao : null;
                $nota_fiscal->nf_numero_nfse               = isset($request->numeroNfse) ? $request->numeroNfse : null;
                $nota_fiscal->nf_serie                     = isset($request->serie) ? $request->serie : null;
                $nota_fiscal->nf_lote                      = isset($request->lote) ? $request->lote : null;
                $nota_fiscal->nf_numero                    = isset($request->numero) ? $request->numero : null;
                $nota_fiscal->nf_codigoVerificacao         = isset($request->codigoVerificacao) ? $request->codigoVerificacao : null;
                $nota_fiscal->nf_data_autorizacao          = isset($request->autorizacao) ? $request->autorizacao : null;
                $nota_fiscal->nf_prestador                 = isset($request->prestador) ? $request->prestador : null;
                $nota_fiscal->nf_tomador                   = isset($request->tomador) ? $request->tomador : null;
            }

            $nota_fiscal->nf_response_id               = isset($request->id) ? $request->id : null;
            $nota_fiscal->nf_emissao                   = isset($request->emissao) ? $request->emissao : null;
	        $nota_fiscal->nf_cancelamento              = isset($request->dataCancelamento) ? $request->dataCancelamento : null;
            
            $nf_log->nf_id                  = $tipo == '/nfe' ? $nota_fiscal->id : null;
            $nf_log->nfse_id                = $tipo == '/nfse' ? $nota_fiscal->id : null;
            $nf_log->nf_cliente_id          = $nota_fiscal->mra_nf_cliente_id;
            $nf_log->nf_empresa_id          = $nota_fiscal->mra_nf_cfg_emp_id;
            $nf_log->nf_transportadora_id   = $nota_fiscal->mra_nf_transp_id;
            $nf_log->nf_numero              = $nota_fiscal->nf_numero;
            $nf_log->nf_chave               = $nota_fiscal->nf_chave;
            $nf_log->nf_emissao             = $nota_fiscal->nf_emissao;
            $nf_log->response_status        = $nota_fiscal->nf_status;
            $nf_log->response_mensagem      = isset($request->mensagem) ? $request->mensagem : null;
            $nf_log->response_status_code   = 200;
            $nf_log->autor                  = isset($nf_log_solicitante) ? $nf_log_solicitante->autor : null;
            $nf_log->save();
            
            // Baixando PDF
            if (isset($request->pdf)) {
                $this->tecnospeed->baixarPDF($nota_fiscal, $tipo);
            }

            // Baixando XML
            if (isset($request->xml) && $nota_fiscal->nf_status != 'CANCELADO') {
                $this->tecnospeed->baixarXML($nota_fiscal, $tipo);
            }

            // Baixando XML de cancelamento
            if (isset($request->dataCancelamento)) {
                $this->tecnospeed->baixarXMLcancelamento($nota_fiscal, '/nfe');
            }

            $nota_fiscal->save();
            DB::commit();

            return response()->json(['success'], 200);

        }catch(Exception $e) {

            $nf_log->response_exception = $e->getMessage();
            $nf_log->save();

            DB::commit();
            return response()->json(['Webhook Error: ' => $e->getMessage()], 500);
        }
    }
}
