<?php

use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\ImporterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\BackupsController;
use App\Http\Controllers\GanttTaskController;
use App\Http\Controllers\GanttLinkController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\IndicatorsController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\LinkPagamentoController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\CadastrosController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\CompanhiasController;
use App\Http\Controllers\FornecedoresController;
use App\Http\Controllers\ProdutosController;
use App\Http\Controllers\ServicosController;
use App\Http\Controllers\TrechosController;
use App\Http\Controllers\PassageiroController;
use App\Http\Controllers\VendedoresController;
use App\Http\Controllers\CcfCartaoDeCreditoController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\ContasAReceberController;
use App\Http\Controllers\ContasAPagarController;
use App\Http\Controllers\FluxoDeCaixaController;
use App\Http\Controllers\VendasController;
use App\Http\Controllers\FaturamentoController;
use App\Http\Controllers\FormasDePagamentosController;
use App\Http\Controllers\CadastroDeEmpresasController;
use App\Http\Controllers\OrcamentosController;
use App\Http\Controllers\TemplatesController;
use App\Http\Controllers\GridPassageirosController;
use App\Http\Controllers\GridPagamentosController;
use App\Http\Controllers\RClientesDocumentosController;

Route::group(['middleware' => ['auth']], function () {

    Route::post('importar', [ImporterController::class, 'importar']);
    Route::post('import_export', [ImporterController::class, 'import_export']); // # -

    Route::get('/', [HomeController::class, 'index']);
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/importer/template', [ImporterController::class, 'template']);
    Route::get('/doc/swagger', [HomeController::class, 'swagger']);
    Route::get('/data', [GanttController::class, 'get']);

    Route::resource('backups', BackupsController::class);
    Route::resource('task', GanttTaskController::class);
    Route::resource('link', GanttLinkController::class);

    Route::group(['middleware' => ['roles']], function () {

        Route::any('/perfil', [ProfilesController::class, 'perfil']);
        Route::post('/profiles/default', [ProfilesController::class, 'defaultProfile']);
        Route::get('/reports/generate/{id}', [ReportsController::class, 'generate']);
        Route::get('/permissions/user/{id}', [PermissionsController::class, 'user']);
        Route::get('/permissions/profile/{id}', [PermissionsController::class, 'profile']);

        Route::resource('users', UsersController::class);
        Route::resource('reports', ReportsController::class);
        Route::resource('indicators', IndicatorsController::class);
        Route::resource('permissions', PermissionsController::class);
        Route::resource('profiles', ProfilesController::class);
        Route::resource('logs', LogsController::class);
        Route::resource('link_pagamento', LinkPagamentoController::class);
        Route::resource('events', EventsController::class);
        Route::resource('cadastros', CadastrosController::class);
        //Route::get('clientes/list', [ClientesController::class,'list']); // # -
        //Route::get('clientes/create/modal', [ClientesController::class,'create']); // # -
        Route::resource('clientes', ClientesController::class);
        Route::resource('companhias', CompanhiasController::class);
        Route::resource('fornecedores', FornecedoresController::class);
        Route::resource('produtos', ProdutosController::class);
        Route::resource('servicos', ServicosController::class);
        Route::resource('trechos', TrechosController::class);
        Route::resource('passageiro', PassageiroController::class);
        Route::resource('vendedores', VendedoresController::class);
        Route::resource('ccf_cartao_de_credito', CcfCartaoDeCreditoController::class);
        Route::resource('financeiro', FinanceiroController::class);
        Route::resource('contas_a_receber', ContasAReceberController::class);
        Route::resource('contas_a_pagar', ContasAPagarController::class);
        Route::resource('fluxo_de_caixa', FluxoDeCaixaController::class);
        Route::post('vendas/faturamento/inicial/final', [VendasController::class,'vendasFaturamentoClienteDTinicialDTfinal']); // # -
        Route::resource('vendas', VendasController::class);
        Route::resource('faturamento', FaturamentoController::class);
        Route::resource('formas_de_pagamentos', FormasDePagamentosController::class);
        Route::resource('cadastro_de_empresas', CadastroDeEmpresasController::class);
        Route::resource('orcamentos', OrcamentosController::class);
        Route::resource('templates', TemplatesController::class);

    });

});

Auth::routes();

