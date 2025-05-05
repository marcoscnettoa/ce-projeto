<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Models\Permissions;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRAGIuguConfiguracoes extends Model
{
    use SoftDeletes;

   	protected $table        = 'mra_g_iugu_cfg';
   	protected $primaryKey   = 'id';
    protected $guarded      = ['_token'];
    public $timestamps      = true;
    public $filter_with     = [];

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

    public static function config_iugu(){
        $MRAGIuguConfiguracoes = MRAGIuguConfiguracoes::where('id',1)->first();
        return $MRAGIuguConfiguracoes;
    }

}

