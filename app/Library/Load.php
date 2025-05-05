<?php

namespace App\Library;

class Load
{
    public static $json = null;

    public static function setJson($data)
    {
        self::$json = json_decode($data->JSON);

        return self::$json;
    }

    public static function getJson()
    {
        return self::$json;
    }

    public static function limpar($string, $controller = false, $menu = false){

        $string = str_replace(
            array( 'ä','ã','à','á','â','ê','ë','è','é','ï','ì','í','ö','õ','ò','ó','ô','ü','ù','ú','û','À','Á','É','Í','Ò','Ó','Ú','ñ','Ñ','ç','Ç', 'Ã', 'Š', 'Ê', 'Ã', 'Õ'),
            array( 'a','a','a','a','a','e','e','e','e','i','i','i','o','o','o','o','o','u','u','u','u','A','A','E','I','O','O','U','n','n','c','C', 'A', 'S', 'E', 'A', 'O'),
            $string
        );

        $string = preg_replace('/[áàãâä]/ui', 'a', $string);
        $string = preg_replace('/[éèêë]/ui', 'e', $string);
        $string = preg_replace('/[íìîï]/ui', 'i', $string);
        $string = preg_replace('/[óòõôö]/ui', 'o', $string);
        $string = preg_replace('/[úùûü]/ui', 'u', $string);
        $string = preg_replace('/[ç]/ui', 'c', $string);
        $string = preg_replace('/[^a-z0-9]/i', '_', $string);

        $string = trim($string);

        if (!$menu) {
            $string = strtolower($string);
        }

        $string = str_replace("____", "_", $string);

        $string = str_replace("___", "_", $string);

        $string = str_replace("__", "_", $string);

        if ($menu) {
            $string = str_replace("_", " ", $string);
            $string = ucfirst($string);
            return $string;
        }

        if ($controller) {
            $string = str_replace("_", " ", $string);
            $string = ucwords($string);
            $string = str_replace(" ", "", $string);
            return $string;
        }

        return $string;
    }

    public static function getClassName($string)
    {
        return self::limpar($string, true);
    }

    public static function getCleanName($string)
    {
        return self::limpar($string);
    }

    public static function getHumanName($string)
    {
        //return self::limpar($string, false, true);

        $string = str_replace("_", " ", $string);

        return ucwords($string);

    }

    public static function getFirstColumnName($json, $name){

        foreach ($json as $key => $value) {

            if ($value->title == $name) {

                if (empty($value->fields)) {
                    continue;
                }

                return self::getCleanName($value->fields[0]->name);
            }
        }
    }

    public static function referenceExists($json, $relationship, $relationship_reference){

        foreach ($json as $key => $value) {
            if ($value->title == $relationship) {

                if (empty($value->fields)) {
                    continue;
                }

                foreach ($value->fields as $keyField => $valueField) {
                    if ($valueField->name == $relationship_reference) {
                        return $relationship_reference;
                    }
                }

                return $value->fields[0]->name;
            }
        }
    }

    public static function geraSenha($tamanho = 8, $maiusculas = true, $numeros = true, $simbolos = false)
    {
        $lmin = 'abcdefghijklmnopqrstuvwxyz';
        $lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = '1234567890';
        $simb = '!@#$%*-';
        $retorno = '';
        $caracteres = '';

        $caracteres .= $lmin;
        if ($maiusculas) $caracteres .= $lmai;
        if ($numeros) $caracteres .= $num;
        if ($simbolos) $caracteres .= $simb;

        $len = strlen($caracteres);
        for ($n = 1; $n <= $tamanho; $n++) {
            // @SuppressWarnings("php:S2245")
            $rand = mt_rand(1, $len);
            $retorno .= $caracteres[$rand-1];
        }

        return $retorno;
    }

    public static function replaceCharacters($string)
    {
        $string = str_replace(['_', '.', '(', ')', '-', '/', ':', '?', '!'], [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '], $string);

        return $string;
    }

    public static function retornaNomeDoModel($module, $table = false)
    {
        if (!is_string($module)) {

            if (isset($module->r_title) && isset($module->clean_title)) {

                $nomeDoModulo = $module->r_title . ' ' . $module->clean_title;

            }
            else
            {
                $nomeDoModulo = $module->title;
            }
        }
        else
        {
            $nomeDoModulo = $module;
        }

        $nomeDoModulo = self::replaceCharacters($nomeDoModulo);

        $nomeDoModulo = ucwords(mb_strtolower($nomeDoModulo));

        $nomeDoModulo = str_replace(
            array( 'ä','ã','à','á','â','ê','ë','è','é','ï','ì','í','ö','õ','ò','ó','ô','ü','ù','ú','û','À','Á','É','Í','Ò','Ó','Ú','ñ','Ñ','ç','Ç', 'Ã', 'Š', 'Ê', 'Ã', 'Õ'),
            array( 'a','a','a','a','a','e','e','e','e','i','i','i','o','o','o','o','o','u','u','u','u','A','A','E','I','O','O','U','n','n','c','C', 'A', 'S', 'E', 'A', 'O'),
            $nomeDoModulo
        );

        $nomeDoModulo = trim($nomeDoModulo);

        $nomeDoModulo = ucwords(str_replace(['_', '.', '(', ')', '-'], [' ', ' ', ' ', ' ', ' '], $nomeDoModulo));

        if ($table) {

            $nomeDoModulo = strtolower($nomeDoModulo);

            $nomeDoModulo = str_replace("______", "_", $nomeDoModulo);

            $nomeDoModulo = str_replace("_____", "_", $nomeDoModulo);

            $nomeDoModulo = str_replace("____", "_", $nomeDoModulo);

            $nomeDoModulo = str_replace("___", "_", $nomeDoModulo);

            $nomeDoModulo = str_replace("__", "_", $nomeDoModulo);
        }
        else
        {
            $nomeDoModulo = str_replace(' ', '', $nomeDoModulo);
        }

        $nomeDoModulo = preg_replace('/[áàãâä]/ui', 'a', $nomeDoModulo);
        $nomeDoModulo = preg_replace('/[éèêë]/ui', 'e', $nomeDoModulo);
        $nomeDoModulo = preg_replace('/[íìîï]/ui', 'i', $nomeDoModulo);
        $nomeDoModulo = preg_replace('/[óòõôö]/ui', 'o', $nomeDoModulo);
        $nomeDoModulo = preg_replace('/[úùûü]/ui', 'u', $nomeDoModulo);
        $nomeDoModulo = preg_replace('/[ç]/ui', 'c', $nomeDoModulo);
        $nomeDoModulo = preg_replace('/[^a-z0-9]/i', '_', $nomeDoModulo);

        if (!$table) {
            //if (strlen($nomeDoModulo) >= 32) {
                //$nomeDoModulo = mb_substr($nomeDoModulo, 0, 32);
            //}
        }

        return $nomeDoModulo;
    }
}
