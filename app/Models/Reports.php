<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reports extends Model
{
   	protected $table = 'r_reports';
   	protected $primaryKey = 'id';

    protected $guarded = ['_token'];

    // # -
    public static function Get_options_tipo_size()
    {
        $options = array (
            ''  => '---',
            1   => 'A4 - Vertical',
            2   => 'A4 - Horizontal',
            3   => 'Personalizado'
        );
        return $options;
    }

    public function Get_tipo_size()
    {
        $options = self::Get_options_tipo_size();
        if(isset($options[$this->size])) {
            return $options[$this->size];
        }
    }
    // - #
}
