<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Models\Permissions;
use Auth;

class VendasGridPagamentos extends Model
{
   	protected $table = 'vendas_grid_pagamentos';
   	protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = ['_token'];

        public function FormaDePagamento()
    {
        return $this->belongsTo("App\Models\FormasDePagamentos", 'forma_de_pagamento');
    }




}
