<?php
// # MXTera -
namespace App\Helper;

use Carbon\Carbon;
use NumberToWords\NumberToWords;

class Helper {

    public static function H_Decimal_ptBR_DB($v){
        if(empty($v)){ return null; }
        return str_replace(',','.',str_replace('.','',$v));
    }

    public static function H_Decimal_DB_ptBR($v, $d = 2){
        if(empty($v)){ return null; }
        return number_format($v,$d,',','.');
    }

    public static function H_Decimal_ptBR_ValueCents($v){
        if(empty($v)){ return null; }
        return str_replace(',','',str_replace('.','',$v));
    }

    public static function H_Decimal_DB_ValueCents($v){
        if(empty($v)){ return null; }
        $v = number_format($v,2,'.','');
        return str_replace('.','',$v);
    }

    public static function H_Decimal_ValueCents_ptBR($v,$d = 2){
        if(empty($v)){ return null; }
        return number_format(($v / pow(10,$d)),$d,',','.');
    }

    public static function H_Decimal_ValueCents_DB($v,$d = 2){
        if(empty($v)){ return null; }
        return number_format(($v / pow(10,$d)),$d,'.','');
    }

    public static function H_ValueCents_Ext($v){
        if(empty($v)){ return null; }
        return NumberToWords::transformCurrency('pt_BR',self::H_Decimal_DB_ValueCents($v),'BRL');
    }

    public static function H_Data_ptBR_DB($v){
        if(empty($v) and strlen($v) != 10){ return null; }
        return Carbon::createFromFormat('d/m/Y', $v)->format('Y-m-d');
    }

    public static function H_Data_DB_ptBR($v){
        if(empty($v)){ return null; }
        return Carbon::createFromFormat('Y-m-d', $v)->format('d/m/Y');
    }

    public static function H_DataHora_ptBR_DB($v){
        if(empty($v) and strlen($v) != 10){ return null; }
        $cff = 'd/m/Y H:i';
        if(strlen($v)>16){ $cff = 'd/m/Y H:i:s'; }
        return Carbon::createFromFormat($cff, $v)->format('Y-m-d H:i');
    }

    public static function H_DataHora_DB_ptBR($v,$ApenasData = false){
        if(empty($v)){ return null; }
        $cff = 'Y-m-d H:i';
        if(strlen($v)>16){ $cff = 'Y-m-d H:i:s'; }
        if($ApenasData){
            return Carbon::createFromFormat($cff, $v)->format('d/m/Y');
        }else {
            return Carbon::createFromFormat($cff, $v)->format('d/m/Y H:i');
        }
    }

    public static function H_Data_DB_ptBR_Ext($v){
        if(empty($v)){ return null; }
        //setlocale(LC_TIME, 'pt-BR');
        setlocale(LC_TIME, 'pt_BR');
        $Date  = Carbon::createFromFormat('Y-m-d', $v);
        $FDate_d = $Date->formatLocalized('%d');
        $FDate_b = $Date->formatLocalized('%B');
        $FDate_y = $Date->formatLocalized('%Y');
        $FDate = $FDate_d.' de '.ucfirst($FDate_b).' de '.$FDate_y;
        return $FDate;
    }
}
