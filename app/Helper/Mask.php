<?php
// # MXTera -
namespace App\Helper;

class Mask
{
    //echo mask($cnpj, '##.###.###/####-##').'<br>';
    //echo mask($cpf, '###.###.###-##').'<br>';
    //echo mask($cep, '#####-###').'<br>';
    //echo mask($data, '##/##/####').'<br>';
    //echo mask($data, '##/##/####').'<br>';
    //echo mask($data, '[##][##][####]').'<br>';
    //echo mask($data, '(##)(##)(####)').'<br>';
    //echo mask($hora, 'Agora são ## horas ## minutos e ## segundos').'<br>';
    //echo mask($hora, '##:##:##');

    public static function MSK_G($val, $MSK)
    {
        // !
        switch(strtolower($MSK)){
            case 'cnpj':         $MSK = '##.###.###/####-##';  break;
            case 'cpf':          $MSK = '###.###.###-##';  break;
            case 'cep':          $MSK = '#####-###';  break;
            case 'data':         $MSK = '##/##/####';  break;
            case 'hora':         $MSK = '##:##:##';  break;
            case 'hora_extenso': $MSK = 'Agora são ## horas ## minutos e ## segundos';  break;
        }
        // - #
        $MSK_txt = '';
        $k   = 0;
        for($i = 0; $i <= strlen($MSK) - 1; ++$i) {
            if ($MSK[$i] == '#') {
                if(isset($val[$k])) { $MSK_txt .= $val[$k++]; }
            }else {
                if(isset($MSK[$i])) { $MSK_txt .= $MSK[$i]; }
            }
        }
        return $MSK_txt;
    }
}
