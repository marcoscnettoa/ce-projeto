<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RCidades extends Model
{
    protected $table            = 'r_cidades';
    protected $primaryKey       = 'id';
    public    $timestamps       = true;
    protected $guarded          = [];
    protected $hidden           = [];
    protected $casts            = [];
}
