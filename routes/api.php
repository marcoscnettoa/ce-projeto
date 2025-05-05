<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'rest'], function() {
	Route::get('/setup', ['App\Http\Controllers\Api\SetupController', 'setup'], array('as' => 'api.setup'));
	Route::post('/auth/login', ['App\Http\Controllers\Api\AuthController', 'login'], array('as' => 'api.auth.login'));
	Route::post('/auth/refresh/token', ['App\Http\Controllers\Api\AuthController', 'refresh_token'], array('as' => 'api.auth.refresh.token'));
    Route::post('/users/register', ['App\Http\Controllers\Api\UsersController','store_register'], array('as' => 'api.users.register'));
    Route::post('/users/password/recovery', ['App\Http\Controllers\Api\UsersController','password_recovery'], array('as' => 'api.users.password.recovery'));
    Route::post('/users/password/reset', ['App\Http\Controllers\Api\UsersController','password_reset'], array('as' => 'api.users.password.reset'));
});

Route::group(['middleware' => 'auth:api'], function() {

        Route::resource('cadastros', 'App\Http\Controllers\Api\CadastrosController', array('as' => 'api.cadastros'));
        Route::resource('clientes', 'App\Http\Controllers\Api\ClientesController', array('as' => 'api.clientes'));
        Route::resource('companhias', 'App\Http\Controllers\Api\CompanhiasController', array('as' => 'api.companhias'));
        Route::resource('fornecedores', 'App\Http\Controllers\Api\FornecedoresController', array('as' => 'api.fornecedores'));
        Route::resource('produtos', 'App\Http\Controllers\Api\ProdutosController', array('as' => 'api.produtos'));
        Route::resource('servicos', 'App\Http\Controllers\Api\ServicosController', array('as' => 'api.servicos'));
        Route::resource('trechos', 'App\Http\Controllers\Api\TrechosController', array('as' => 'api.trechos'));
        Route::resource('passageiro', 'App\Http\Controllers\Api\PassageiroController', array('as' => 'api.passageiro'));
        Route::resource('vendedores', 'App\Http\Controllers\Api\VendedoresController', array('as' => 'api.vendedores'));
        Route::resource('ccf_cartao_de_credito', 'App\Http\Controllers\Api\CcfCartaoDeCreditoController', array('as' => 'api.ccf_cartao_de_credito'));
        Route::resource('financeiro', 'App\Http\Controllers\Api\FinanceiroController', array('as' => 'api.financeiro'));
        Route::resource('contas_a_receber', 'App\Http\Controllers\Api\ContasAReceberController', array('as' => 'api.contas_a_receber'));
        Route::resource('contas_a_pagar', 'App\Http\Controllers\Api\ContasAPagarController', array('as' => 'api.contas_a_pagar'));
        Route::resource('fluxo_de_caixa', 'App\Http\Controllers\Api\FluxoDeCaixaController', array('as' => 'api.fluxo_de_caixa'));
        Route::resource('vendas', 'App\Http\Controllers\Api\VendasController', array('as' => 'api.vendas'));
        Route::resource('faturamento', 'App\Http\Controllers\Api\FaturamentoController', array('as' => 'api.faturamento'));
        Route::resource('formas_de_pagamentos', 'App\Http\Controllers\Api\FormasDePagamentosController', array('as' => 'api.formas_de_pagamentos'));
        Route::resource('cadastro_de_empresas', 'App\Http\Controllers\Api\CadastroDeEmpresasController', array('as' => 'api.cadastro_de_empresas'));
        Route::resource('orcamentos', 'App\Http\Controllers\Api\OrcamentosController', array('as' => 'api.orcamentos'));
        Route::resource('templates', 'App\Http\Controllers\Api\TemplatesController', array('as' => 'api.templates'));
        //Route::resource('grid_passageiros', 'App\Http\Controllers\Api\GridPassageirosController', array('as' => 'api.grid_passageiros'));
        //Route::resource('grid_pagamentos', 'App\Http\Controllers\Api\GridPagamentosController', array('as' => 'api.grid_pagamentos'));

    Route::get('/auth/setup', ['App\Http\Controllers\Api\SetupController', 'auth_setup'], array('as' => 'api.setup.auth'));
    Route::resource('/users', 'App\Http\Controllers\Api\UsersController', array('as' => 'api.users'));
    Route::put('/profiles/default', ['App\Http\Controllers\Api\ProfilesController','profile_default'], array('as' => 'api.profiles.default'));
    Route::resource('/profiles', 'App\Http\Controllers\Api\ProfilesController', array('as' => 'api.profiles'));
    Route::get('/permissions/user/{id}', ['App\Http\Controllers\Api\PermissionsController','permissions_user'], array('as' => 'api.permissions.user'));
    Route::put('/permissions/user/{id}', ['App\Http\Controllers\Api\PermissionsController','permissions_user_edit'], array('as' => 'api.permissions.user.edit'));
    Route::get('/permissions/profile/{profile_id}', ['App\Http\Controllers\Api\PermissionsController','permissions_profile'], array('as' => 'api.permissions.profile'));
    Route::put('/permissions/profile/{profile_id}', ['App\Http\Controllers\Api\PermissionsController','permissions_profile_edit'], array('as' => 'api.permissions.profile.edit'));
});
