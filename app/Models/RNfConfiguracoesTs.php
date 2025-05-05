<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Models\Permissions;
use Auth;
use App\Http\Controllers\RTecnoSpeedController as RTecnoSpeed;

use Illuminate\Database\Eloquent\SoftDeletes;

class RNfConfiguracoesTs extends Model
{
    use SoftDeletes;

   	protected $table          = 'r_nf_cfg_emp_ts';
   	protected $primaryKey     = 'id';
    protected $guarded        = ['_token'];
    public $timestamps        = true;
    public $filter_with       = [];

    public function makeHidden($attributes)
    {
        $attributes = (array) $attributes;
        foreach ($attributes as $key => $value) {
            if (isset($this->$value)) {
                unset($this->$value);
            }
        }
        return $this;
    }

    public static function config_empresa(){
        $RNfConfiguracoes = RNfConfiguracoesTs::where('id',1)->first();
        return $RNfConfiguracoes;
    }

    /*
    *   Retorna o limite de envios disponíveis para o CNPJ da empresa
    *   @return int
    *   @param string $tipo
    */
    public static function envios_disponiveis($tipo) {

        $config_empresa = RNfConfiguracoesTs::where('id',1)->first();
        $cnpj = $config_empresa ? $config_empresa->cnpj : '';

        if ($cnpj) {
            $tecnospeed = new RTecnoSpeed();
            $tecnospeed_response = $tecnospeed->notas_contabilizadas($cnpj, $tipo);

            if ($tecnospeed_response['status'] == 200) {
                if ($tipo == 'nfe') {
                    $result = env('TECNOSPEED_LIMITE_NFE') - $tecnospeed_response['response']['total_debitado'];
                }elseif ($tipo == 'nfse') {
                    $result = env('TECNOSPEED_LIMITE_NFSE') - $tecnospeed_response['response']['total_debitado'];
                }else {
                    $result = '--';
                }
            }else {
                $tecnospeed_response['nf_log']->response_mensagem = $tecnospeed_response['response']['error']['message'];
                $tecnospeed_response['nf_log']->save();
                
                $result = 'XX';
            }
        }else {
            $result = '0';
        }
        return $result;
    }
}

