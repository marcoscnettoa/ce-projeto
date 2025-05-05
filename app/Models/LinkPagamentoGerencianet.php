<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinkPagamentoGerencianet extends Model
{
	use SoftDeletes;

   	protected $table = 'r_payment_link';
   	protected $primaryKey = 'id';
    public $timestamps = true;

    protected $guarded = ['_token'];

    protected $dates = ['deleted_at'];

}
