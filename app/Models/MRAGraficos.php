<?php

namespace App\Models;

use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;
use Auth;

use Illuminate\Database\Eloquent\SoftDeletes;

class MRAGraficos extends Model
{
    use SoftDeletes;

    protected $table        = 'mra_graficos';
    protected $primaryKey   = 'id';
    protected $guarded      = ['_token'];
    public $timestamps      = true;
    public $filter_with     = [];

    // :: Posição
    public static function Get_options_posicao($unsets = null)
    {
        $options = array (
            ""  =>  "---",
            0   => 	"Topo",
            1   =>  "Fundo",
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_status_ai($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_posicao();
        if(isset($options[$value])) { return $options[$value]; }
    }

}

