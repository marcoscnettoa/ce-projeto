<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicators extends Model
{
   	protected $table = 'r_indicators';
   	protected $primaryKey = 'id';

    protected $guarded = ['_token'];
}
