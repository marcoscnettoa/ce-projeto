<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use GuzzleHttp\Client;
use function PHPUnit\Framework\isJson;
use App\Models\RNfConfiguracoesTs;
use App\Models\RNfLogTs;
use Storage;
use Auth;
use Log;
use DB;
use App\Models\RCidades;

class RTecnoSpeedController extends Controller
{
    private $client;

    public function __construct() {

        $this->client = new Client();
        $this->user = Auth::user();

        // Verifica ambiente e credenciais de acesso a serem utuilizadas
        if (env('TECNOSPEED_ENVIRONMENT') == 'sandbox') {
            $this->url_api      = env('TECNOSPEED_SANDBOX_URL_API');
            $this->token_api    = env('TECNOSPEED_SANDBOX_X_API_KEY');

        }elseif (env('TECNOSPEED_ENVIRONMENT') == 'production') {
            $this->url_api      = env('TECNOSPEED_URL_API');

            // Verifica se a empresa possui token de acesso próprio
            $config_empresa = RNfConfiguracoesTs::find(1);
            if ($config_empresa && $config_empresa->token_api) {
                $this->token_api    = $config_empresa->token_api;
            }else {
                $this->token_api    = env('TECNOSPEED_X_API_KEY');
            }
        }
    }

    public function emitir($data = null, $tipo = null) {

        try {
            $acao           = 'Emitir';
            $nf_log         = new RNfLogTs();
            $nf_log->autor  = isset($this->user) ? $this->user->id : null;
            $nf_log->acao   = $acao;

            // --------------------- TESTES NFSE -------------------
            // Simulando Sucesso ao Transmitir
            // $getStatusCode          = 200;
            // $getBody_getContents    = '{
            //     "documents": [
            //         {
            //             "idIntegracao": "000001-SysTeste-TecnoSpeed-SANDBOX-1702651660",
            //             "prestador": "08187168000160",
            //             "id": "657c670a0ab1a2f288c28e21"
            //         }
            //     ],
            //     "message": "Nota(as) em processamento",
            //     "protocol": "ccddfac5-7f5c-44c2-b706-0692ee985b1e"
            // }';

            // Simulando Erro 400 ao Transmitir
            // $getStatusCode        = 400;
            // $getBody_getContents = '{
            //     "error": {
            //         "message": "Falha na validação do JSON de NFSe",
            //         "data": {
            //              "fields": {
            //                  "tomador.endereco.cep": "Tamanho mínimo (sem máscara): 8"
            //             }
            //         }
            //     }
            // }';

            // Simulando Erro 409 ao Transmitir
            // $getStatusCode        = 409;
            // $getBody_getContents = '{
            //     "error": {
            //         "message": "Já existe um(a) NFSe com os parâmetros informados",
            //         "data": {
            //                 "new": {
            //                 "idIntegracao": "TESTE",
            //                 "cnpj": "8187168000160"
            //             },
            //             "current": {
            //                 "id": "5exxxe508efc9a95e999faa0",
            //                 "idIntegracao": "TESTE",
            //                 "emissao": "13/04/2020",
            //                 "tipoAutorizacao": "WEBSERVICE",
            //                 "situacao": "CONCLUIDO",
            //                 "prestador": "8187168000160",
            //                 "tomador": "8187168000160",
            //                 "valorServico": 20,
            //                 "numeroNfse": "999",
            //                 "serie": "1",
            //                 "lote": 0,
            //                 "numero": 1,
            //                 "codigoVerificacao": "XXXX",
            //                 "autorizacao": "30/08/2011",
            //                 "mensagem": "RPS Autorizada com sucesso",
            //                 "pdf": "https://api.plugnotas.com.br/nfse/pdf/5exxxe508efc9a95e999faa0",
            //                 "xml": "https://api.plugnotas.com.br/nfse/xml/5exxxe508efc9a95e999faa0"
            //             }
            //         }
            //     }
            // }';

            $client_resp = $this->client->post($this->url_api.$tipo, [
                'verify'        => false,
                'headers'       => [
                    'Content-Type'  => 'application/json',
                    'x-api-key'     => $this->token_api
                ],
                'json'          => $data
            ]);
            $getStatusCode                  = $client_resp->getStatusCode();
            $getBody_getContents            = $client_resp->getBody()->getContents();

            $nf_log->response_status_code   = $getStatusCode;
            $nf_log->response               = $getBody_getContents;
            $nf_log->request                = json_encode($data);
            $nf_log->save();

            // Log::info("TecnoSpeed - emitir $tipo: (".$getStatusCode.")".$getBody_getContents);

            return [
                'status'    =>  $getStatusCode,
                'response'  =>  json_decode($getBody_getContents,true),
                'nf_log'    =>  $nf_log,
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            return $this->requestException($nf_log, $data, $acao, $e);

        }catch(\Exception $e) {
            return $this->exception($nf_log, $data, $acao, $e);
        }
    }

    public function consultar($data = null, $tipo = null) {
    
        try {
            $acao           = 'Consultar';
            $nf_log         = new RNfLogTs();
            $nf_log->autor  = isset($this->user) ? $this->user->id : null;
            $nf_log->acao   = $acao;

            if ($tipo == '/nfe') {
                $urn = $data['response_id'].'/resumo';
            }else {
                $urn = 'consultar/'.$data['response_id'];
            }
            
            // --------------------- TESTES Nfe -------------------
            // Simulando Sucesso ao Consultar
            // $getStatusCode        = 200;
            // $getBody_getContents  = '[
            //     {
            //         "id": "5f9ad47eff3b4d0d7b4994ea",
            //         "idIntegracao": "XXX999",
            //         "emissao": "29/10/2020",
            //         "status": "CONCLUIDO",
            //         "destinada": false,
            //         "emitente": "08187168000160",
            //         "destinatario": "08114280956",
            //         "valor": 9.2,
            //         "numero": "1000013",
            //         "serie": "805",
            //         "chave": "41201008187168000160558050010000131609769080",
            //         "protocolo": "141200000956123",
            //         "dataAutorizacao": "29/10/2020",
            //         "mensagem": "Autorizado o uso da NF-e",
            //         "pdf": "https://api.plugnotas.com.br/nfe/5f9ad47eff3b4d0d7b4994ea/pdf",
            //         "xml": "https://api.plugnotas.com.br/nfe/5f9ad47eff3b4d0d7b4994ea/xml",
            //         "dataCancelamento" : "29/10/2020",
            //         "xmlCancelamento" : "https://api.plugnotas.com.br/nfe/5f9ad47eff3b4d0d7b4994ea/xmlCancelamento",
            //         "cStat": 100
            //     }
            // ]';

            // Simulando Erro ao consultar
            // $getStatusCode        = 404;
            // $getBody_getContents  = '{
            //     "error": {
            //         "message": "Não localizamos qualquer NFe com os parâmetros informados",
            //         "data": {
            //             "idOrProtocol": "5eea65b2cbbbdb0a3ec532c"
            //         }
            //     }
            // }';

            // --------------------- TESTES Nfse -------------------
            // Simulando Sucesso ao Consultar
            // $getStatusCode        = 200;
            // $getBody_getContents  = '[
            //     {
            //         "id": "5ecbbaabbdbd4670e36b9999",
            //         "idIntegracao": "XX999",
            //         "emissao": "21/12/2020",
            //         "tipoAutorizacao": "WEBSERVICE",
            //         "situacao": "CONCLUIDO",
            //         "prestador": "08187168000160",
            //         "tomador": "08114280956",
            //         "valorServico": 425,
            //         "numeroNfse": "202000000012467",
            //         "protocoloPrefeitura": "123456",
            //         "serie": "7",
            //         "lote": 14176,
            //         "numero": 12571,
            //         "codigoVerificacao": "5482010c6",
            //         "autorizacao": "30/12/2020",
            //         "mensagem": "RPS Autorizada com sucesso",
            //         "pdf": "https://api.plugnotas.com.br/nfse/pdf/5ecbbaabbdbd4670e36b9999",
            //         "xml": "https://api.plugnotas.com.br/nfse/xml/5ecbbaabbdbd4670e36b9999"
            //     }
            // ]';

            // Simulando Erro ao consultar
            // $getStatusCode        = 404;
            // $getBody_getContents  = '{
            //     "error": {
            //         "message": "Não localizamos qualquer NFSe com os parâmetros informados",
            //         "data": {
            //             "id": "0000000"
            //         }
            //     }
            // }';

            $client_resp = $this->client->get($this->url_api.$tipo."/$urn", [
                'verify'        => false,
                'headers'       => [
                    'Content-Type'  => 'application/json',
                    'x-api-key'     => $this->token_api
                ]
            ]);
            $getStatusCode                  = $client_resp->getStatusCode();
            $getBody_getContents            = $client_resp->getBody()->getContents();

            $nf_log->response_status_code   = $getStatusCode;
            $nf_log->response               = $getBody_getContents;
            $nf_log->request                = json_encode($data);
            $nf_log->save();

            // Log::info("TecnoSpeed - consulta $tipo: (".$getStatusCode.")".$getBody_getContents);

            return [
                'status'    =>  $getStatusCode,
                'response'  =>  json_decode($getBody_getContents,true),
                'nf_log'    =>  $nf_log,
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            return $this->requestException($nf_log, $data, $acao, $e);

        }catch(\Exception $e) {
            return $this->exception($nf_log, $data, $acao, $e);
        }
    }

    public function cancelar($response_id, $tipo) {

        try {
            $acao           = 'Cancelar';
            $nf_log         = new RNfLogTs();
            $nf_log->autor  = isset($this->user) ? $this->user->id : null;
            $nf_log->acao   = $acao;

            if ($tipo == '/nfe') {
                $urn    = "$response_id".'/cancelamento';
                $data   = [
                    'justificativa' => 'Erro na emissão da Nota Fiscal eletrônica.',
                ];
            }else {
                $urn    = 'cancelar/'.$response_id;
                $data   = [
                    'motivo' => 'Erro na emissão da Nota Fiscal de Serviço eletrônica.',
                ];
            }

            $client_resp = $this->client->post($this->url_api."$tipo"."/$urn", [
                'verify'        => false,
                'headers'       => [
                    'Content-Type'  => 'application/json',
                    'x-api-key'     => $this->token_api
                ],
                'json'          => $data
            ]);
            $getStatusCode                  = $client_resp->getStatusCode();
            $getBody_getContents            = $client_resp->getBody()->getContents();

            $nf_log->response_status_code   = $getStatusCode;
            $nf_log->response               = $getBody_getContents;
            $nf_log->request                = json_encode($data);
            $nf_log->save();

            // Log::info('TecnoSpeed - cancelamento: ('.$getStatusCode.')'.$getBody_getContents);

            return [
                'status'    =>  $getStatusCode,
                'response'  =>  json_decode($getBody_getContents,true),
                'nf_log'    =>  $nf_log,
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            return $this->requestException($nf_log, $data, $acao, $e);

        }catch(\Exception $e) {
            return $this->exception($nf_log, $data, $acao, $e);
        }
    }

    public function baixarPDF($data, $tipo) {

        try {
            $acao           = 'Baixar PDF';
            $nf_log         = new RNfLogTs();
            $nf_log->autor  = isset($this->user) ? $this->user->id : null;
            $nf_log->acao   = $acao;

            if ($tipo == '/nfe') {
                $urn = $data['nf_response_id'].'/pdf';
            }else{
                $urn = 'pdf/'.$data['nf_response_id'];
            }

            $client_resp = $this->client->get($this->url_api.$tipo."/$urn", [
                'verify'        => false,
                'headers'       => [
                    'Content-Type'  => 'application/json',
                    'x-api-key'     => $this->token_api
                ]
            ]);
            $getStatusCode          = $client_resp->getStatusCode();
            $getBody_getContents    = $client_resp->getBody()->getContents();

            $nf_log->nf_id                  = $tipo == '/nfe' ? $data['id'] : null;
            $nf_log->nfse_id                = $tipo != '/nfe' ? $data['id'] : null;
            $nf_log->response_id            = isset($data['nf_response_id']) ? $data['nf_response_id'] : null;
            $nf_log->nf_empresa_id          = isset($data['mra_nf_cfg_emp_id']) ? $data['mra_nf_cfg_emp_id'] : null;
            $nf_log->nf_cliente_id          = isset($data['mra_nf_cliente_id']) ? $data['mra_nf_cliente_id'] : null;
            $nf_log->nf_transportadora_id   = isset($data['mra_nf_transp_id']) ? $data['mra_nf_transp_id'] : null;
            $nf_log->nf_idIntegracao        = isset($data['nf_response_idIntegracao']) ? $data['nf_response_idIntegracao'] : null;
            $nf_log->response_status        = isset($data['nf_status']) ? $data['nf_status'] : null;
            $nf_log->response_status_code   = $getStatusCode;
            $nf_log->request                = json_encode($data);
            $nf_log->save();

            // Caso seja retornado o PDF
            if ($getStatusCode == 200) {

                if (env("FILESYSTEM_DRIVER") == "s3") {
                    $file_path = "/nf/".$data->nf_response_id.".pdf";
                    Storage::disk("s3")->put('/files/'.env('FILEKEY').$file_path, $getBody_getContents);
                    $data['nf_pdf'] = $file_path;

                } else {
                    $file_path = "/nf/".$data->nf_response_id.".pdf";
                    Storage::disk('local')->put('/public'.$file_path, $getBody_getContents);
                    $data['nf_pdf'] = $file_path;
                }

                $nf_log->response_mensagem  = 'PDF baixado com sucesso!';
                $nf_log->save();
                $data->save();

                return [
                    'status'    =>  200,
                    'response'  =>  $file_path,
                ];

            // Caso seja retornado uma mensagem
            }elseif ($getStatusCode == 202) {

                $nf_log->response           = $getBody_getContents;
                $getBody_getContents        = json_decode($getBody_getContents, true);
                $nf_log->response_mensagem  = isset($getBody_getContents['message']) ? $getBody_getContents['message'] : null;
                $nf_log->save();

                return [
                    'status'    =>  202,
                    'response'  =>  $getBody_getContents,
                    'nf_log'    =>  $nf_log,
                ];

            // Caso seja retornado um erro
            }else {
                
                $nf_log->response           = $getBody_getContents;
                $getBody_getContents        = json_decode($getBody_getContents, true);
                $nf_log->response_mensagem  = isset($getBody_getContents['error']['message']) ? $getBody_getContents['error']['message'] : null;
                $nf_log->save();
                
                return [
                    'status'    =>  $getStatusCode,
                    'response'  =>  $getBody_getContents,
                    'nf_log'    =>  $nf_log,
                ];
            }

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            return $this->requestException($nf_log, $data, $acao, $e);

        }catch(\Exception $e) {
            return $this->exception($nf_log, $data, $acao, $e);
        }
    }

    public function baixarXML($data, $tipo) {

        try {
            $acao           = 'Baixar XML';
            $nf_log         = new RNfLogTs();
            $nf_log->autor  = isset($this->user) ? $this->user->id : null;
            $nf_log->acao   = $acao;
            $urn            = $data['nf_response_id'].'/xml';

            if ($tipo == '/nfe') {
                $urn = $data['nf_response_id'].'/xml';
            }else{
                $urn = 'xml/'.$data['nf_response_id'];
            }

            $client_resp = $this->client->get($this->url_api.$tipo."/$urn", [
                'verify'        => false,
                'headers'       => [
                    'Content-Type'  => 'application/json',
                    'x-api-key'     => $this->token_api
                ]
            ]);
            $getStatusCode          = $client_resp->getStatusCode();
            $getBody_getContents    = $client_resp->getBody()->getContents();

            $nf_log->nf_id                  = $tipo == '/nfe' ? $data['id'] : null;
            $nf_log->nfse_id                = $tipo != '/nfe' ? $data['id'] : null;
            $nf_log->response_id            = isset($data['nf_response_id']);
            $nf_log->nf_empresa_id          = isset($data['mra_nf_cfg_emp_id']);
            $nf_log->nf_cliente_id          = isset($data['mra_nf_cliente_id']);
            $nf_log->nf_transportadora_id   = isset($data['mra_nf_transp_id']);
            $nf_log->nf_idIntegracao        = isset($data['nf_response_idIntegracao']);
            $nf_log->response_status        = isset($data['nf_status']);
            $nf_log->response_status_code   = $getStatusCode;
            $nf_log->request                = json_encode($data);
            $nf_log->save();

            // Caso seja retornado o XML
            if ($getStatusCode == 200) {
                
                if (env("FILESYSTEM_DRIVER") == "s3") {
                    $file_path = "/nf/".$data->nf_response_id.".xml";
                    Storage::disk("s3")->put('/files/'.env('FILEKEY').$file_path, $getBody_getContents);

                } else {
                    $file_path = "/nf/".$data->nf_response_id.".xml";
                    Storage::disk('local')->put('/public'.$file_path, $getBody_getContents);
                }

                $nf_log->response_mensagem  = 'XML baixado com sucesso!';
                $nf_log->save();

                $data['nf_xml'] = $file_path;
                $data->save();

                return [
                    'status'    =>  200,
                    'response'  =>  $file_path,
                    'nf_log'    =>  $nf_log,
                ];

            // Caso seja retornado uma mensagem
            }elseif ($getStatusCode == 202) {

                $nf_log->response           = $getBody_getContents;
                $getBody_getContents        = json_decode($getBody_getContents, true);
                $nf_log->response_mensagem  = isset($getBody_getContents['message']) ? $getBody_getContents['message'] : null;
                $nf_log->save();

                return [
                    'status'    =>  202,
                    'response'  =>  $getBody_getContents,
                    'nf_log'    =>  $nf_log,
                ];

            // Caso seja retornado um erro
            }else {
                
                $nf_log->response           = $getBody_getContents;
                $getBody_getContents        = json_decode($getBody_getContents, true);
                $nf_log->response_mensagem  = isset($getBody_getContents['error']['message']) ? $getBody_getContents['error']['message'] : null;
                $nf_log->save();
                
                return [
                    'status'    =>  $getStatusCode,
                    'response'  =>  $getBody_getContents,
                    'nf_log'    =>  $nf_log,
                ];
            }

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            return $this->requestException($nf_log, $data, $acao, $e);

        }catch(\Exception $e) {
            return $this->exception($nf_log, $data, $acao, $e);
        }
    }

    public function baixarXMLcancelamento($data, $tipo) {

        try {
            $acao           = 'XML de Cancelamento';
            $nf_log         = new RNfLogTs();
            $nf_log->autor  = isset($this->user) ? $this->user->id : null;
            $nf_log->acao   = $acao;
            $urn            = $data['nf_response_id'].'/cancelamento/xml';

            $client_resp = $this->client->get($this->url_api.$tipo."/$urn", [
                'verify'        => false,
                'headers'       => [
                    'Content-Type'  => 'application/json',
                    'x-api-key'     => $this->token_api
                ]
            ]);

            $getStatusCode          = $client_resp->getStatusCode();
            $getBody_getContents    = $client_resp->getBody()->getContents();

            $nf_log->nf_id                  = $tipo == '/nfe' ? $data['id'] : null;
            $nf_log->nfse_id                = $tipo != '/nfe' ? $data['id'] : null;
            $nf_log->response_id            = $data['nf_response_id'];
            $nf_log->nf_empresa_id          = $data['mra_nf_cfg_emp_id'];
            $nf_log->nf_cliente_id          = $data['mra_nf_cliente_id'];
            $nf_log->nf_transportadora_id   = $data['mra_nf_transp_id'];
            $nf_log->nf_idIntegracao        = $data['nf_response_idIntegracao'];
            $nf_log->response_status        = $data->nf_status;
            $nf_log->response_status_code   = $getStatusCode;
            $nf_log->request                = json_encode($data);
            $nf_log->save();

            // Caso seja retornado o XML
            if ($getStatusCode == 200) {
                if (env("FILESYSTEM_DRIVER") == "s3") {
                    $file_path = "/nf/".$data->nf_response_id.'canc.xml';
                    Storage::disk("s3")->put('/files/'.env('FILEKEY').$file_path, $getBody_getContents);

                } else {
                    $file_path = "/nf/".$data->nf_response_id.'canc.xml';
                    Storage::disk('local')->put('/public'.$file_path, $getBody_getContents);
                }

                $nf_log->response_mensagem  = 'XML cancelamento baixado com sucesso!';
                $nf_log->save();

                $data['nf_xml_cancelamento'] = $file_path;
                $data->save();

                return [
                    'status'    =>  200,
                    'response'  =>  $file_path,
                    'nf_log'    =>  $nf_log,
                ];

            // Caso seja retornado uma mensagem
            }elseif ($getStatusCode == 202) {

                $nf_log->response           = $getBody_getContents;
                $getBody_getContents        = json_decode($getBody_getContents, true);
                $nf_log->response_mensagem  = isset($getBody_getContents['message']) ? $getBody_getContents['message'] : null;
                $nf_log->save();

                return [
                    'status'    =>  202,
                    'response'  =>  $getBody_getContents,
                    'nf_log'    =>  $nf_log,
                ];

            // Caso seja retornado um erro
            }else {
                
                $nf_log->response           = $getBody_getContents;
                $getBody_getContents        = json_decode($getBody_getContents, true);
                $nf_log->response_mensagem  = isset($getBody_getContents['error']['message']) ? $getBody_getContents['error']['message'] : null;
                $nf_log->save();
                
                return [
                    'status'    =>  $getStatusCode,
                    'response'  =>  $getBody_getContents,
                    'nf_log'    =>  $nf_log,
                ];
            }

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            return $this->requestException($nf_log, $data, $acao, $e);

        }catch(\Exception $e) {
            return $this->exception($nf_log, $data, $acao, $e);
        }
    }

    public function cadastrarCertificadoDigital($certificado_digital, $senha_certificado, $certificado_id = null) {
    
        try {

            if (!$certificado_id) {
                $method = 'POST';
                $url    = $this->url_api."/certificado";
                $acao   = 'Cadastrar Certificado Digital';
            }else {
                $method = 'PUT';
                $url    = $this->url_api."/certificado/$certificado_id";
                $acao   = 'Alterar Certificado Digital';
            }

            $nf_log         = new RNfLogTs();
            $nf_log->autor  = isset($this->user) ? $this->user->id : null;
            $nf_log->acao   = $acao;
            $data           = [];
            
            // --------------------- TESTES -------------------
            // Simulando Sucesso ao Consultar
            // $getStatusCode        = 201;
            // $getBody_getContents  = '{
            //     "message": "Cadastro efetuado com sucesso",
            //     "data": {
            //         "id": "6581f2850ab1a28324c297a6"
            //     }
            // }';

            // Simulando Erro ao consultar
            // $getStatusCode        = 401;
            // $getBody_getContents  = '{
            //     "error": {
            //         "message": "O token informado é inválido. Para o ambiente de produção, Acesse https://app2.plugnotas.com.br/, e gere o seu TOKEN de acesso"
            //     }
            // }';

            $client_resp = $this->client->$method($url, [
                'verify'        => false,
                'headers'       => [
                    'x-api-key'     => $this->token_api
                ],
                'multipart'     => [
                    [
                        'name'      => 'arquivo',
                        'contents'  => file_get_contents($certificado_digital->path()),
                        'filename'  => $certificado_digital->getClientOriginalName()
                    ],
                    [
                        'name'      => 'senha',
                        'contents'  => $senha_certificado
                    ],
                ]
            ]);
            $getStatusCode                  = $client_resp->getStatusCode();
            $getBody_getContents            = $client_resp->getBody()->getContents();

            $data = [
                'arquivo'   => $certificado_digital->getClientOriginalName(),
                'senha'     => $senha_certificado
            ];

            $nf_log->response_status_code   = $getStatusCode;
            $nf_log->response               = $getBody_getContents;
            $nf_log->request                = json_encode($data);
            $nf_log->save();

            // Log::info("TecnoSpeed - $acao: (".$getStatusCode.")".$getBody_getContents);

            return [
                'status'    =>  $getStatusCode,
                'response'  =>  json_decode($getBody_getContents,true),
                'nf_log'    =>  $nf_log,
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            return $this->requestException($nf_log, $data, $acao, $e);

        }catch(\Exception $e) {
            return $this->exception($nf_log, $data, $acao, $e);
        }
    }

    public function cadastrarEmpresa($data, $certificado_id, $acao) {
    
        try {

            $data['cnpj'] = preg_replace("/\D/", "", $data['cnpj']);

            if ($acao == 'add') {
                $method = 'POST';
                $url    = $this->url_api."/empresa";
                $acao   = 'Cadastrar Empresa';
                $rps    = [
                    "numeracao" => [
                        [
                            "serie"  => 1,
                            "numero" => 1
                        ]
                    ]
                ];
            }else {
                $method = 'PATCH';
                $url    = $this->url_api."/empresa/".$data['cnpj'];
                $acao   = 'Alterar Empresa';
                $rps    = null;
            }

            $nf_log         = new RNfLogTs();
            $nf_log->autor  = isset($this->user) ? $this->user->id : null;
            $nf_log->acao   = $acao;

            $endereco = RCidades::find($data['end_cidade']);
            if (!$endereco) {
                Session::flash('flash_error', 'Desculpe, cidade não encontrada!');
                return back()->withInput();
            }

            $data  = [
                "cpfCnpj" => preg_replace("/\D/", "", $data['cnpj']),
                "inscricaoMunicipal" => $data['inscricao_municipal'],
                "inscricaoEstadual" => preg_replace("/\D/", "", $data['inscricao_estadual']),
                "razaoSocial" => $data['razao_social'],
                "nomeFantasia" => $data['nome_fantasia'],
                "certificado" => $certificado_id,
                "simplesNacional" => $data['optante_simples_nacional'] == 1 ? true : false,
                "regimeTributario" => intval($data['regime_tributario']),
                "regimeTributarioEspecial" => intval($data['regime_tributario_especial']),
                "endereco" => [
                    "bairro" => $data['end_bairro'],
                    "cep" => $data['end_cep'],
                    "codigoCidade" => $endereco->codigo,
                    "estado" => $endereco->uf,
                    "logradouro" => $data['end_rua'],
                    "numero" => $data['end_numero'],
                    "tipoLogradouro" => $data['end_tipo_logradouro'],
                    "complemento" => $data['end_complemento'],
                    "descricaoCidade" => $endereco->nome,
                ],
                "telefone" => [
                    "ddd" => substr($data['cont_telefone'], 1, 2),
                    "numero" => preg_replace("/\D/", "", substr($data['cont_telefone'], 5)),
                ],
                "email" => $data['cont_email'],
                "nfse" => [
                    "ativo" => env('MODULO_NF_SERVICO') ? true : false,
                    "tipoContrato" => 0,  // 0 - Bilhetagem, 1 - Ilimitado
                    "config" => [
                        "producao" => $data['producao'] ? true : false,
                        "rps" => $rps
                    ]
                ],
                "nfe" => [
                    "ativo" => env('MODULO_NF_PRODUTO') ? true : false,
                    "tipoContrato" => 0,  // 0 - Bilhetagem, 1 - Ilimitado
                    "config" => [
                        "producao" => $data['producao'] ? true : false,
                    ]
                ],
                "nfce" => [
                    "ativo" => false,
                    "tipoContrato" => 0,  // 0 - Bilhetagem, 1 - Ilimitado
                    "config" => [
                        "producao" => $data['producao'] ? true : false,
                        "sefaz" => [
                            "idCodigoSegurancaContribuinte" => isset($data['id_codigo_seguranca_contribuinte']) ? $data['id_codigo_seguranca_contribuinte'] : null,
                            "codigoSegurancaContribuinte"   => isset($data['codigo_seguranca_contribuinte']) ? $data['codigo_seguranca_contribuinte'] : null,
                        ],
                    ]
                ],
                "mdfe" => [
                    "ativo" => false,
                    "tipoContrato" => 0,  // 0 - Bilhetagem, 1 - Ilimitado
                    "config" => [
                        "producao" => $data['producao'] ? true : false,
                    ]
                ]
            ];

            $client_resp = $this->client->$method($url, [
                'verify'        => false,
                'headers'       => [
                    'Content-Type'  => 'application/json',
                    'x-api-key'     => $this->token_api
                ],
                'json'          => $data
            ]);
            $getStatusCode                  = $client_resp->getStatusCode();
            $getBody_getContents            = $client_resp->getBody()->getContents();

            $nf_log->response_status_code   = $getStatusCode;
            $nf_log->response               = $getBody_getContents;
            $nf_log->request                = json_encode($data);
            $nf_log->save();

            // Log::info("TecnoSpeed - $acao: (".$getStatusCode.")".$getBody_getContents);

            return [
                'status'    =>  $getStatusCode,
                'response'  =>  json_decode($getBody_getContents,true),
                'nf_log'    =>  $nf_log,
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            return $this->requestException($nf_log, $data, $acao, $e);

        }catch(\Exception $e) {
            return $this->exception($nf_log, $data, $acao, $e);
        }
    }

    public function cadastrarWebhook($token_webhook, $cnpj, $acao) {

        try {

            if ($acao == 'add') {
                $method = 'POST';
                $acao   = 'Cadastrar Webhook';
                $url    = $this->url_api."/empresa/".preg_replace("/\D/", "", $cnpj)."/webhook";
            }else {
                $method = 'PUT';
                $acao   = 'Alterar Webhook';
                $url    = $this->url_api."/empresa/".preg_replace("/\D/", "", $cnpj)."/webhook";
            }

            $nf_log         = new RNfLogTs();
            $nf_log->autor  = isset($this->user) ? $this->user->id : null;
            $nf_log->acao   = $acao;

            $data = [
                "url" => URL('api/nf/ts/webhook'),
                "method" => $method,
                "headers"  => [
                    "token_webhook"  => $token_webhook
                ]
            ];

            // --------------------- TESTES -------------------
            // Simulando Sucesso ao Cadastrar
            // $getStatusCode        = 200;
            // $getBody_getContents = '{
            //     "message": "Operação realizada com sucesso",
            //     "data": {}
            // }';

            // Simulando Erro ao Cadastrar
            // $getStatusCode        = 400;
            // $getBody_getContents = '{
            //     "error": {
            //         "message": "Falha na validação do JSON de Webhook",
            //         "data": {
            //             "fields": {
            //                 "url": "Preenchimento obrigatório"
            //             }
            //         }
            //     }
            // }';

            $client_resp = $this->client->$method($url, [
                'verify'        => false,
                'headers'       => [
                    'Content-Type'  => 'application/json',
                    'x-api-key'     => $this->token_api
                ],
                'json'          => $data
            ]);
            $getStatusCode                  = $client_resp->getStatusCode();
            $getBody_getContents            = $client_resp->getBody()->getContents();

            $nf_log->response_status_code   = $getStatusCode;
            $nf_log->response               = $getBody_getContents;
            $nf_log->request                = json_encode($data);
            $nf_log->save();

            // Log::info("TecnoSpeed - $acao: (".$getStatusCode.")".$getBody_getContents);

            return [
                'status'    =>  $getStatusCode,
                'response'  =>  json_decode($getBody_getContents,true),
                'nf_log'    =>  $nf_log,
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            return $this->requestException($nf_log, $data, $acao, $e);

        }catch(\Exception $e) {
            return $this->exception($nf_log, $data, $acao, $e);
        }
    }

    public function notas_contabilizadas($cnpj, $tipo) {

        try {
            $acao           = 'Qtd '.strtoupper($tipo).' Enviadas';
            $nf_log         = new RNfLogTs();
            $nf_log->autor  = isset($this->user) ? $this->user->id : null;
            $nf_log->acao   = $acao;
            $data_inicio    = date('Y-m').'-01';
            $data_fim       = date('Y-m-d');
            $url            = "/$tipo/".preg_replace("/\D/", "", $cnpj)."/relatorio?from=$data_inicio&to=$data_fim";

            $client_resp = $this->client->get($this->url_api.$url, [
                'verify'        => false,
                'headers'       => [
                    'Content-Type'  => 'application/json',
                    'x-api-key'     => $this->token_api
                ]
            ]);
            $getStatusCode            = $client_resp->getStatusCode();
            $getBody_getContents = $client_resp->getBody()->getContents();

            // Verificando quantas nfe ou nfse foram concluidas ou canceladas
            $quantidade = 0;
            $nfe_nfse = json_decode($getBody_getContents);
            if ($getStatusCode) {
                foreach ($nfe_nfse as $value) {
                    if ($value->situacao == 'CONCLUIDO' || $value->situacao == 'CANCELADO') {
                        $quantidade += $value->quantidade;
                    }
                }
            }

            // Simulando Erro
            // $getStatusCode = 400;
            // $getBody_getContents['total_debitado'] => $quantidade = '{
            //     "error": {
            //         "message": "O intervalo entre as datas excedeu o limite de 31 dias.",
            //         "data": {
            //             "from": "2023-12-08",
            //             "to": "2024-01-09"
            //         }
            //     }
            // };

            $nf_log->response_status_code   = $getStatusCode;
            $nf_log->response = $getBody_getContents;
            $getBody_getContents = json_decode($getBody_getContents, true);
            $getBody_getContents['total_debitado'] = $quantidade;

            return [
                'status'    =>  $getStatusCode,
                'response'  =>  $getBody_getContents,
                'nf_log'    =>  $nf_log,
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            return $this->requestException($nf_log, [], $acao, $e);

        }catch(\Exception $e) {
            return $this->exception($nf_log, [], $acao, $e);
        }
    }

    private function requestException($nf_log, $data, $acao, $e) {
        $nf_log->nf_idIntegracao        = (isset($data[0]['idIntegracao']) ? $data[0]['idIntegracao'] : null);
        $nf_log->acao                   = (isset($acao) ? $acao : null);
        $nf_log->response_status_code   = $e->getResponse()->getStatusCode();
        $nf_log->response               = $e->getResponse()->getBody()->getContents();
        $nf_log->response_exception     = 'RequestException: '.$e->getMessage();
        $nf_log->save();

        Log::info('TecnoSpeed - RequestException (: '.$acao.')'.$e->getMessage());
        DB::commit();

        return [
            'status'    =>  'RequestException',
            'response'  =>  json_decode($nf_log->response, true),
            'nf_log'    =>  $nf_log
        ];
    }

    private function exception($nf_log, $data, $acao, $e) {
        $nf_log->nf_idIntegracao    = (isset($data[0]['idIntegracao']) ? $data[0]['idIntegracao'] : null);
        $nf_log->acao               = (isset($acao) ? $acao : null);
        $nf_log->response_exception = 'Exception: '.$e->getMessage();
        $nf_log->save();

        Log::info('TecnoSpeed - Exception (: '.$acao.')'.$e->getMessage());
        DB::commit();

        return [
            'status'    =>  'ErroException',
            'response'  =>  [
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ],
            'nf_log'    =>  $nf_log
        ];
    }
}
