<?php

namespace App\Http\Controllers;



use Auth;
use League\CommonMark\Exception\LogicException;
use Redirect;
use Session;
use Validator;
use Exception;
use Response;
use DB;
use PDF;
use Log;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use \App\Models\MRANfLog;
use \App\Models\MRANfConfiguracoes;
use \App\Models\MRANfNfse;
use \App\Models\MRANfNfe;
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

class MRANfController
{
    public function webhook(Request $request){

        set_time_limit(-1);
        try {

            $store = $request->all();

            $MRANfLog                               = new MRANfLog();
            $MRANfLog->integracao                   = 'Rxxx-APPS-Notazz';
            $MRANfLog->acao                         = 'webhook';
            $MRANfLog->resp_webhook                 = (!empty($store)?json_encode($store):null);

            // :: Configurações
            $MRANfConfiguracoes      = MRANfConfiguracoes::find(1); // ! ID # 1 Padrão - Temporário
            if(!$MRANfConfiguracoes){
                throw new MRALogException('Configurações- Não Existe!',404);
            }

            $MRANfLog->mra_nf_cfg_emp_id            = $MRANfConfiguracoes->id;


            // ! Token Notazz Existente ! Reforço*
            if(!isset($store['token'])){
                throw new MRALogException('Token Webhook Notazz - não informado!',404);
            }

            // ! Token de Permissão Configurado
            if($MRANfConfiguracoes->token_webhook == $store['token']){

                // :: Tipo ( nfse ou nfe )
                if(isset($store['tipo'])){

                    // :: Nota Fiscal de Serviço
                    if($store['tipo'] == 'nfse'){

                        if(!isset($store['id'])){
                            throw new MRALogException('ID de Retorno Notazz - não informado!',404);
                        }

                        $MRANfNfse = MRANfNfse::where('notazz_id_documento',$store['id'])->first();
                        if(!$MRANfNfse){
                            throw new MRALogException('Nota Fiscal de Serviço não encontrada!',404);
                        }

                        if(isset($store['statusNota'])){
                            // ! Autorizada
                            if($store['statusNota'] == 'Autorizada'){
                                $MRANfNfse->nf_numero                   = (isset($store['numero'])?$store['numero']:null);
                                $MRANfNfse->nf_codigoVerificacao        = (isset($store['codigoVerificacao'])?$store['codigoVerificacao']:null);
                                $MRANfNfse->nf_pdf                      = (isset($store['pdf'])?$store['pdf']:null);
                                $MRANfNfse->nf_xml                      = (isset($store['xml'])?$store['xml']:null);
                                $MRANfNfse->nf_pdf_prefeitura           = (isset($store['linkPrefeitura'])?$store['linkPrefeitura']:null);
                                $MRANfNfse->nf_xml_cancelamento         = (isset($store['xmlCancelamento'])?$store['xmlCancelamento']:null);
                                $MRANfNfse->nf_emissao                  = (isset($store['emissao'])?$store['emissao']:null);
                            }
                        }

                        $MRANfLog->mra_nf_cliente_id                    = $MRANfNfse->mra_nf_cliente_id;
                        $MRANfLog->mra_nf_nfs_e_id                      = $MRANfNfse->id;
                        $MRANfLog->nf_numero                            = $MRANfNfse->nf_numero;
                        $MRANfLog->nf_codigoVerificacao                 = $MRANfNfse->nf_codigoVerificacao;
                        $MRANfLog->nf_emissao                           = $MRANfNfse->nf_emissao;
                        $MRANfLog->notazz_statusNota                    = (isset($store['statusNota'])?$store['statusNota']:null);
                        $MRANfLog->notazz_motivo                        = (isset($store['motivoStatus'])?$store['motivoStatus']:$MRANfLog->notazz_statusNota);
                        $MRANfLog->resp_webhook_tipo                    = (isset($store['tipo'])?$store['tipo']:null);

                        $MRANfNfse->notazz_status                       = $MRANfLog->notazz_statusNota;
                        $MRANfNfse->notazz_motivo                       = $MRANfLog->notazz_motivo;

                    // :: Nota Fiscal de Produto
                    }elseif($store['tipo'] == 'nfe'){

                    }else {
                        throw new MRALogException('Tipo de Retorno Notazz - não reconhecida!',403);
                    }

                }else {
                    throw new MRALogException('Tipo de Retorno Notazz - não informado!',404);
                }

            }else {
                throw new MRALogException('Token Webhook Notazz não corresponde a Configuração!',403);
            }

            $MRANfLog->resp_webhook_status                              = 200;
            $MRANfLog->save();
            $MRANfNfse->save();
            return response()->json(['ok'], 200);

        }catch(MRALogException $e){
            $MRANfLog->exception                                        = $e->getMessage();
            $MRANfLog->resp_webhook_status                              = $e->getCode();
            $MRANfLog->save();
            Log::info('MRALogException - Erro: '. $e->getMessage());
            return response()->json(['error' => $MRANfLog->exception], $e->getCode());
        }catch(Exception $e) {
            $MRANfLog->exception                                        = $e->getMessage();
            $MRANfLog->save();
            Log::info('mra_nota_fiscal/webhook - Erro: '. $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}

class MRALogException extends Exception
{
    // ...
}
