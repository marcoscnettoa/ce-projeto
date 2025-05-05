<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class REstados extends Model
{
    protected $table            = 'r_estados';
    protected $primaryKey       = 'id';
    public    $timestamps       = true;
    protected $guarded          = [];
    protected $hidden           = [];
    protected $casts            = [];

}
