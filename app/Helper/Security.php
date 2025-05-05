<?php
// # MXTera -
namespace App\Helper;

use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class Security {

    public static function NO_CODE($CODE, $Permit=[], $AppModels = false){
        $vendorAutoLoad         = require base_path('vendor/autoload.php');
        $vendorAutoLoadClassMap = $vendorAutoLoad->getClassMap();
        $vendorAutoLoadClassMap = array_keys($vendorAutoLoadClassMap);
        $get_declared_classes   = get_declared_classes();
        $array_MU               = array_merge($vendorAutoLoadClassMap, $get_declared_classes);
        $array_MU               = array_unique($array_MU);
        $array_MU               = array_diff($array_MU,$Permit);

        if($AppModels){
            $array_MU               = array_diff($array_MU,self::get_AppModels());
        }

        $CODE                   = str_replace($array_MU, '_****_', $CODE);

        $Protect                = ['eval', 'exec', 'system', 'shell_exec', 'passthru', 'file_get_contents', 'file_put_contents', 'fopen', 'fclose', 'fwrite', 'unlink', 'rmdir', 'dirname', 'basename', 'curl', 'phpinfo', 'assert', 'extract', 'include', 'include_once', 'require', 'require_once', 'unserialize', 'popen', 'proc_open', 'pcntl_exec', 'fsockopen', 'socket_create', 'create_function',];
        $patterns               = [];
        foreach($Protect as $C) {  $patterns[] = '/\b'.preg_quote($C).'\s*\(/i'; }
        $CODE                   = preg_replace($patterns, '_****_', $CODE);
        $CODE                   = html_entity_decode($CODE);
        return $CODE;
    }

    public static function get_AppModels(){
        $Models         = [];
        $ModelsPath     = app_path('Models');
        $ModelFiles     = File::allFiles($ModelsPath);
        if(count($ModelFiles)){
            foreach($ModelFiles as $MF) {
                $M[]   = 'App\\Models\\' . $MF->getFilenameWithoutExtension();
            }
        }
        return $M;
    }

}
