<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Models\Permissions;
use Auth;

class VendasGridPassageiros extends Model
{
   	protected $table = 'vendas_grid_passageiros';
   	protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['_token'];

    public function Passageiros()
    {
        return $this->belongsTo("App\Models\Passageiro", 'passageiros');
    }




}
