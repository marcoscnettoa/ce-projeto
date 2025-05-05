<?php
// # -
namespace App\Http\Controllers\MRA;

use DB;
use Session;
use Redirect;

use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\UploadRepository;
use GuzzleHttp\Client;

use App\Models\MRAGIuguLog;
use App\Models\MRAGIuguConfiguracoes;
use function PHPUnit\Framework\isJson;
use function PHPUnit\Framework\isNull;

class MRAGIugu
{
    private $client;
    private $url_api         = "https://api.iugu.com/v1";
    private $token_api       = null;
    private $token_webhook   = null;

    public function __construct($construct = true) {
        if($construct){
            $this->client            = new Client();
            // Verifica se tem Configuração Gateway Iugu
            $MRAGIuguConfiguracoes      = MRAGIuguConfiguracoes::find(1);
            if($MRAGIuguConfiguracoes and !empty($MRAGIuguConfiguracoes->token_api)){
                $this->token_api     = $MRAGIuguConfiguracoes->token_api;
                $this->token_webhook = $MRAGIuguConfiguracoes->token_webhook;
            }
        }
    }

    public function set_api_token($JSON){
        //if(is_null($this->token_api) || empty($this->token_api)){ }
        $JSON['api_token']           = $this->token_api;
        return $JSON;
    }

    // # Clientes -
    public function customers_criar($JSON = null, $acao = null){
        try {
            if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'customers_criar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->post($this->url_api.'/customers', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'customers_criar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'customers_criar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function customers_alterar($JSON = null, $customer_id = null, $acao = null){
        try {
            if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'customers_alterar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->put($this->url_api.'/customers/'.$customer_id, [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'customers_alterar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'customers_alterar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function customers_remover($JSON = null, $customer_id = null, $acao = null){
        try {
            if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'customers_remover':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->delete($this->url_api.'/customers/'.$customer_id, [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'customers_remover':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'customers_remover':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function customers_buscar($JSON = null, $customers_id, $acao = null){
        try {
            //if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'customers_buscar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->get($this->url_api.'/customers/'.$customers_id, [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'customers_buscar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'customers_buscar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function customers_listar($JSON = null, $acao = null){

        try {
            /*if(is_null($JSON)){ return []; }*/
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'customers_listar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->get($this->url_api.'/customers', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'customers_listar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'customers_listar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }
    // - #

    // # Assinaturas -
    public function subscriptions_criar($JSON = null, $acao = null){
        try {
            if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_criar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->post($this->url_api.'/subscriptions', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_criar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_criar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function subscriptions_alterar($JSON = null, $subscriptions_id = null, $acao = null){
        try {
            if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_alterar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->put($this->url_api.'/subscriptions/'.$subscriptions_id, [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_alterar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_alterar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function subscriptions_remover($JSON = null, $subscriptions_id = null, $acao = null){
        try {
            if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_remover':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->delete($this->url_api.'/subscriptions/'.$subscriptions_id, [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_remover':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_remover':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function subscriptions_buscar($JSON = null, $subscriptions_id, $acao = null){
        try {
            //if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_buscar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->get($this->url_api.'/subscriptions/'.$subscriptions_id, [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_buscar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_buscar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function subscriptions_listar($JSON = null, $acao = null){
        try {
            /*if(is_null($JSON)){ return []; }*/
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_listar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->get($this->url_api.'/subscriptions', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            //print_r(json_decode($getBody_getContents,true)); exit;

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_listar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_listar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function subscriptions_ativar($JSON = null, $subscriptions_id = null, $acao = null){
        try {
            //if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_ativar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->post($this->url_api.'/subscriptions/'.$subscriptions_id.'/activate', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_ativar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_ativar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function subscriptions_suspender($JSON = null, $subscriptions_id = null, $acao = null){
        try {
            //if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_suspender':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->post($this->url_api.'/subscriptions/'.$subscriptions_id.'/suspend', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_suspender':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'subscriptions_suspender':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }
    // - #

    // # Planos -
    public function plans_criar($JSON = null, $acao = null){

        try {
            if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'plans_criar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->post($this->url_api.'/plans', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'plans_criar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'plans_criar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }

    }

    public function plans_alterar($JSON = null, $plan_id = null, $acao = null){
        try {
            if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'plans_alterar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->put($this->url_api.'/plans/'.$plan_id, [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'plans_alterar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'plans_alterar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function plans_remover($JSON = null, $plan_id = null, $acao = null){
        try {
            if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'plans_remover':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->delete($this->url_api.'/plans/'.$plan_id, [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'plans_remover':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'plans_remover':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function plans_buscar($JSON = null, $plans_id, $acao = null){
        try {
            //if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'plans_buscar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->get($this->url_api.'/plans/'.$plans_id, [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'plans_buscar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'plans_buscar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function plans_listar($JSON = null, $acao = null){
        try {
            /*if(is_null($JSON)){ return []; }*/
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'plans_listar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->get($this->url_api.'/plans', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'plans_listar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'plans_listar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }
    // - #

    // # Faturas -
    public function invoices_criar($JSON = null, $acao = null){
        try {
            if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'invoices_criar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->post($this->url_api.'/invoices', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_criar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_criar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function invoices_enviar_email($JSON = null, $invoices_id, $acao = null){
        try {

            //if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'invoices_enviar_email':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->post($this->url_api.'/invoices/'.$invoices_id.'/send_email', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_enviar_email':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_enviar_email':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }


    public function invoices_fatura_paga_externamente($JSON = null, $invoices_id, $acao = null){
        try {
            //if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'invoices_fatura_paga_externamente':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->put($this->url_api.'/invoices/'.$invoices_id.'/externally_pay', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_fatura_paga_externamente':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_fatura_paga_externamente':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function invoices_alterar($JSON = null, $acao = null){
    }

    public function invoices_cancelar($JSON = null, $invoices_id = null, $acao = null){
        try {
            if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'invoices_cancelar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->put($this->url_api.'/invoices/'.$invoices_id.'/cancel', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_cancelar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_cancelar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function invoices_buscar($JSON = null, $invoices_id, $acao = null){
        try {
            //if(is_null($JSON)){ return []; }
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'invoices_buscar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->get($this->url_api.'/invoices/'.$invoices_id, [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_buscar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_buscar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }

    public function invoices_listar($JSON = null, $acao = null){
        try {
            /*if(is_null($JSON)){ return []; }*/
            $JSON                           = $this->set_api_token($JSON);

            //DB::beginTransaction();
            $MRAGIuguLog                    = new MRAGIuguLog();
            $MRAGIuguLog->acao              = is_null($acao)?'invoices_listar':$acao;
            $MRAGIuguLog->resq              = json_encode($JSON);

            $Client_resp = $this->client->get($this->url_api.'/invoices', [
                'headers'       => [
                    'Content-Type'  => 'application/json'
                ],
                'json'          => $JSON
            ]);

            $getStatusCode                  = $Client_resp->getStatusCode();
            $getBody_getContents            = $Client_resp->getBody()->getContents();

            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;

            $MRAGIuguLog->save();
            //DB::commit();

            //print_r(json_decode($getBody_getContents,true)); exit;

            return [
                'status'        =>  $getStatusCode,
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\GuzzleHttp\Exception\RequestException $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'RequestException: '.$e->getMessage(); exit;
            // - #

            $getStatusCode                  = $e->getResponse()->getStatusCode();
            $getBody_getContents            = $e->getResponse()->getBody()->getContents();

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_listar':$acao;
            $MRAGIuguLog->resp_status       = $getStatusCode;
            $MRAGIuguLog->resp              = $getBody_getContents;
            $MRAGIuguLog->exception         = 'RequestException: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'RequestException',
                'iugu_resp'     =>  json_decode($getBody_getContents,true),
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];

        }catch(\Exception $e) {
            //DB::rollback();
            // ## DEBUG ##
            //echo 'Exception: '.$e->getMessage(); exit;
            // - #

            $MRAGIuguLog->acao              = is_null($acao)?'invoices_listar':$acao;
            $MRAGIuguLog->exception         = 'Exception: '.$e->getMessage();

            $MRAGIuguLog->save();
            //DB::commit();

            return [
                'status'        =>  'ErroException',
                'MRAGIuguLog'   =>  $MRAGIuguLog
            ];
        }
    }
    // - #

    // :: Status Fatura -| invoice_iugu_status
    public static function Get_options_invoice_iugu_status($unsets = null)
    {
        $options = array (
            ""                  =>  "---",
            "pending"           =>  "Pendente",
            "paid"              =>	"Paga",
            "canceled"          =>	"Cancelada",
            "partially_paid"    =>	"Paga Parcialmente",
            "externally_paid"   =>	"Paga Externamente",
            "refunded"          =>	"Reembolsada",
            "expired"           =>	"Expirada",
            "authorized"        =>	"Autorizada",
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_invoice_iugu_status($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_invoice_iugu_status();
        if (isset($options[$value])) {
            return $options[$value];
        }
    }

    // :: Tipo de Intervalo -| interval_type
    public static function Get_options_tipo_intervalo($unsets = null)
    {
        $options = array (
            ""       => "---",
            "months" => "Meses",
            "weeks"  =>	"Semanas",
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_tipo_intervalo($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_tipo_intervalo();
        if (isset($options[$value])) {
            return $options[$value];
        }
    }

    // :: API - Erros
    public static function Get_options_giugu_api_erros_fields($unsets = null)
    {
        $options = array (
            "email"                 => "E-mail",
            "name"                  => "Nome",
            "phone_prefix"          => "DDD Telefone",
            "phone"                 => "Telefone",
            "cpf_cnpj"              => "CPF ou CNPJ",
            "zip_code"              => "CEP",
            "number"                => "Número do Endereço",
            "street"                => "Endereço/Rua",
            "city"                  => "Cidade",
            "state"                 => "Estado",
            "district"              => "Bairro",
            "complement"            => "Complemento",
            "notes"                 => "Observações",
            "identifier"            => "Identificador Iugu",
            "are incompatible with plan. Available plan methods:" => "São incompatíveis com o plano. Métodos disponíveis no plano:",
            "Credit Card"           => "Cartão de Crédito",
            "Bank Slip"             => "Boleto Bancário",
            "payer.cpf_cnpj"        => "CPF ou CNPJ",
            "payer.name"            => "Nome",
            "items.quantity"        => "Item Quantidade",
            "items.price_cents"     => "Item Valor",
            "items.description"     => "Item Descrição",
            "total"                 => "Total",

        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_giugu_api_erros_fields($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_giugu_api_erros_fields();
        if(isset($options[$value])) { return $options[$value]; }
    }

    public static function Get_erros_fix_msg($value){
        $msg    = [
            'Unauthorized',
            'is invalid',
            'Customer Not Found',
            'Plan Not Found',
            'Invoice Not Found',
            'Internal Server Error',
            'Subscription Not Found',
        ];
        $msg_r  = [
            'API Token não Autorizado',
            'não é válido',
            'Cliente não encontrado',
            'Plano não encontrado',
            'Fatura não encontrada',
            'Erro do Servidor Interno',
            'Assinatura não encontrada',
        ];
        return str_replace($msg,$msg_r,$value);
    }

}

