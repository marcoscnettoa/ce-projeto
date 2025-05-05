<?php
// # -
namespace App\Http\Controllers\MRA;

use DB;

use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\UploadRepository;
use GuzzleHttp\Client;

use App\Models\MRANfLog;
use App\Models\MRANfConfiguracoes;
use function PHPUnit\Framework\isJson;

class MRANotazz
{
    private $client;
    private $url_api         = "https://app.notazz.com/api";
    private $token_api       = null;
    private $token_webhook   = null;

    public function __construct($construct = true) {
        if($construct){
            $this->client            = new Client();
            // Verifica se tem Configuração Notazz da Empresa Principal
            $MRANfConfiguracoes      = MRANfConfiguracoes::find(1);
            if($MRANfConfiguracoes and !empty($MRANfConfiguracoes->token_api)){
                $this->token_api     = $MRANfConfiguracoes->token_api;
                $this->token_webhook = $MRANfConfiguracoes->token_webhook;
            }
        }
    }

    public function send($JSON = null, $acao = null){

        if(is_null($JSON)){ return []; }

        try {

            DB::beginTransaction();
            $MRANfLog                        = new MRANfLog();
            $MRANfLog->integracao            = 'Rxxx-APPS-Notazz';
            $MRANfLog->mra_nf_cfg_emp_id     =  1;
            $MRANfLog->resq                  = json_encode($JSON);

            // # Lançamento - Oficial
            $Client_resp = $this->client->post($this->url_api, [
                'verify'        => false,
                'headers'       => [
                    'Content-Type'  => 'application/json',
                    'API_KEY'       => $this->token_api
                ],
                'json'          => $JSON
            ]);
            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();
            // - #

            // ### DEBUG ###
                //$getStatusCode                  = 200;
                // - Transmitir - Sucesso
                //$getBody_getContents        = '{ "statusProcessamento": "sucesso", "codigoProcessamento": "000", "id": "ff1bf45652e07468053e1da140fd704e" }';
                // - Cancelar - Sucesso
                //$getBody_getContents        = '{"statusProcessamento":"sucesso","codigoProcessamento":"000"}';
                // - Cancelar - Erro
                //$getBody_getContents        = '{"statusProcessamento":"erro","codigoProcessamento":"301","motivo":"DOCUMENT_ID 7acb260dda089398e08584e6c4affffd1231 invalido"}';
                // - Consultar - Erro - statusNota: Rejeitada - Motivo
                //$getBody_getContents        = '{"statusProcessamento":"sucesso","codigoProcessamento":"000","statusNota":"Rejeitada","motivoStatus":"XXXX - Codigo ... Erro personalizado... etc."}';
                // - Consultar - Erro - codigoProcessamento: 996
                //$getBody_getContents        = '{"statusProcessamento":"erro","codigoProcessamento":"996","motivo":"Requisicao bloqueada temporariamente pois a mesma ja foi enviada em menos de 1 minuto"}';
                // - Consultar - Sucesso - Retorno - ( NF. Produto )
                /*$getBody_getContents        = '{
                      "statusProcessamento": "sucesso",
                      "codigoProcessamento": "000",
                      "numero": "123",
                      "chave": "51080701212344000127550010000000981364117781",
                      "pdf": "http://link.pdf",
                      "xml": "http://link.xml",
                      "xmlCancelamento": "http://link.cancelamento.xml",
                      "valorTotal": "97.00",
                      "emissao": "2017-01-01 15:35:26",
                      "statusNota": "Cancelada",
                      "logistica": "a2557a7b2e94197ff767970b67041697",
                      "venda": "5987656",
                      "rastreio": "DW98767876533"
                }';*/
            // - #

            $MRANfLog->acao                 = (isset($JSON['METHOD'])?$JSON['METHOD']:$acao);
            $MRANfLog->resp_status          = $getStatusCode;
            $MRANfLog->resp                 = $getBody_getContents;

            $MRANfLog->save();
            DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'notazz_resp'   =>  json_decode($getBody_getContents,true),
                'MRANfLog'      =>  $MRANfLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            // ## DEBUG ##
            //echo $e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRANfLog->acao                 = (isset($JSON['METHOD'])?$JSON['METHOD']:$acao);
            $MRANfLog->resp_status          = $getStatusCode;
            $MRANfLog->resp                 = $getBody_getContents;
            $MRANfLog->exception            = 'RequestException: '.$e->getMessage();

            $MRANfLog->save();
            DB::commit();

            return [
                'status'        =>  'RequestException',
                'notazz_resp'   =>  json_decode($getBody_getContents,true),
                'MRANfLog'      =>  $MRANfLog
            ];
        }catch(\Exception $e) {
            // ## DEBUG ##
            //echo $e->getMessage(); exit;
            // - #

            $MRANfLog->acao                 = (isset($JSON['METHOD'])?$JSON['METHOD']:$acao);
            $MRANfLog->exception            = 'Exception: '.$e->getMessage();

            $MRANfLog->save();
            DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRANfLog'      =>  $MRANfLog
            ];
        }

    }

    // :: Notazz API - Erros
    public static function Get_options_notazz_api_erros($unsets = null)
    {
        $options = array (
            ""    => "---",
            //"000" => "SUCESSO", // ! Original
            "000" => "Processado com sucesso", // ! Modificado
            "100" => "BLOCO COM ERROS DE VALIDAÇÕES",
            "101" => "EXTERNAL_ID já está vinculado em outro documento fiscal",
            "102" => "Validações de variáveis",
            "103" => "Registro não pode ser atualizado por motivo de status",
            "104" => "Registro não pode ser removido por motivo de status",
            "105" => "Não é possível cancelar a nota fiscal pois já foi encerrado a competência",
            "106" => "Não é possível cancelar a nota fiscal por motivo de status",
            "107" => "Deve-se informar no mínimo 30 caracteres no campo REASON",
            "108" => "Você deve preencher o campo REASON com o motivo do cancelamento",
            "109" => "Código ou link de rastreio não pode ser vazio",
            "110" => "Foi alcançado o limite de requisições por minuto",
            "111" => "A nota não pode ser cancelada pois já se passaram mais de 24h da sua emissão",
            "112" => "A nota não pode ser apagada pois existe um número vinculado à mesma",
            "113" => "Só é possível cancelar logística com status diferente de concluído",
            "114" => "Você deve preencher o campo MESSAGEM com o conteúdo da mensagem",
            //"115" => "Variável DESTINATION_PHONE vazia ou inválida", // ?? Validar pois existe 2 IDs 115
            //"115" => "Variável MESSAGE_TYPE inválida", // ?? Validar pois existe 2 IDs 115
            "115" => "Variável DESTINATION_PHONE vazia ou inválida e Variável MESSAGE_TYPE inválida",
            "116" => "Formato do arquivo inválido",
            "117" => "Não foi possível baixar o arquivo na URL informada",
            "118" => "O envio da mensagem sera pausada temporariamente por motivos de recusa de entrega do WhatsApp",
            "119" => "Para envio de mensagens via whatsapp deve-se respeitar um intervalo de pelo menos 10 minutos para envio de mensagens para o mesmo número",
            "120" => "O método N enviado na requisição anterior ainda está sendo excutado Aguarde o fim da execução para enviar uma nova requisição.",
            //"121" => "O IP xxx.xxx.xxx.xxx não está autorizado a utilizar recursos da API", // ! Original
            "121" => "O IP* não está autorizado a utilizar recursos da API", // ! Modificado
            "122" => "Não foi possível inserir/atualizar a nota de substituição pois a nota a ser substituida, DOCUMENT_ID_REPLACE = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx não existe ou não está autorizada",
            "200" => "BLOCO COM ERROS DE BANCO DE DADOS",
            "201" => "Erro ao inserir registro",
            "202" => "Registro não encontrado",
            "300" => "BLOCO COM ERROS DE ESTRUTURA",
            "301" => "DOCUMENT_ID inválido",
            "302" => "Método inválido",
            "303" => "APIKEY inválida",
            "304" => "Não foi informado os filtros para a consulta",
            "305" => "APIKEY não liberada para integração",
            "306" => "Requisição inválida",
            "307" => "URL inválida",
            "308" => "WEBHOOK_ID inválido",
            "900" => "BLOCO COM ERROS RELACIONADOS A BLOQUEIOS / FIREWALL",
            "996" => "Requisicão bloqueada temporariamente pois a mesma já foi enviada em menos de 1 minuto",
            "997" => "Requisição bloqueada temporariamente por consumo indevido com mais de 100 requisicões no último minuto",
            "999" => "API em manutenção"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_notazz_api_erros($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_notazz_api_erros();
        if(isset($options[$value])) { return $options[$value]; }
    }

}

