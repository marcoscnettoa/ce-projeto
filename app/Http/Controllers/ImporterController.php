<?php

namespace App\Http\Controllers;

use App\Library\Load;
use App\Models\Logs;
use Illuminate\Http\Request;

use Auth;
use PDF;
use Log;
use Session;
use Response;
use OpenSpout\Common\Entity\Style\Style;
use Rap2hpoutre\FastExcel\FastExcel;
use Exception;

class ImporterController extends Controller
{
    public function importar(Request $request)
    {
        $collections = [];

        try {

            $model = $request->get('model');

            $class = '\App\Models\\' . $model;

            $path = $request->file->getRealPath();

            $filename = uniqid().".".strtolower($request->file->getClientOriginalExtension());

            $request->file->move(public_path("files"), $filename);

            $collections = (new FastExcel)->configureCsv(';')->import('files/' . $filename);

            if (count($collections) > 1000) {

                Session::flash('flash_error', 'Não é possível importar mais de 1000 linhas em uma única importação. Certifique-se de ter no máximo 1000 linhas por importação!');

                return back();
            }

            $errors = [];

            foreach ($collections as $key => $value) {

                try {

                    $value = array_filter($value);

                    if (isset($value['id']) && $value['id']) {

                        $obj = (new $class())->find($value['id']);

                        unset($value['id']);

                        if (isset($value['r_auth']) && !is_int($value['r_auth'])) {
                            $value['r_auth'] = NULL;
                        }

                        if (!$obj) {
                            $obj = new $class();
                        }
                    }
                    else
                    {
                        $obj = new $class();
                    }

                    $obj->fill($value);

                    $obj->save();

                } catch (Exception $e) {

                    $value['error'] = $e->getMessage();

                    $errors[] = $value;

                }
            }

            if (!empty($errors)) {

                Session::flash('flash_error', "Erro ao importar " . count($errors) . ' linhas! <a href="/errors.xlsx">Clique aqui para baixar o arquivo</a>');

                (new FastExcel($errors))->export('errors.xlsx');
            }

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao importar :" . $e->getMessage());

        } catch(\Illuminate\Database\QueryException $ex){

            Session::flash('flash_error', "Por favor, verifique se você está importando um arquivo do tipo CSV com separador <b>;<b> <br><br>Erro ao importar :" . $ex->getMessage());
        }

        return back();

    }

    public function template(Request $request)
    {
        $file = $request->get('file');

        $class = '\App\Models\\' . $file;

        $obj = (new $class())->orderBy('id', 'DESC')->limit(1)->get();

        (new FastExcel($obj))->configureCsv(';')->export('template-para-importacao.csv');

        return Response::download('template-para-importacao.csv');
    }

    // # -
    public function import_export(Request $request){
        try {

            ini_set('max_execution_time',300);
            ini_set('memory_limit', -1);

            $user         = Auth::user();
            $store        = $request->all();

            $r_model      = $store['r_model'];
            $AppModel     = 'App\Models\\'.$r_model;
            $AppModel_get = $AppModel::select('*');

            // :: Setup -|
            $Setup        = base_path('r_setup.json');
            $Setup        = file_get_contents($Setup);
            $SetupJson    = json_decode($Setup);
            $SetupModule  = null;
            // ! Selecionando Módulo Responsável
            if(isset($SetupJson->modules) and !empty($SetupJson->modules)){
                foreach($SetupJson->modules as $m){
                    if(Load::retornaNomeDoModel($m->title) == $r_model){ $SetupModule = $m;  break; }
                }
            }
            // ! Refatorando
            $SetupModuleFields = [];
            if(isset($SetupModule->fields) && !empty($SetupModule->fields)){
                foreach($SetupModule->fields as $k => $f){
                    $SetupModuleFields[Load::limpar($f->name)] = $f;
                }
            }

            // :: Filtro / Pesquisa
            if(isset($store['r_import_filtro_check']) && (isset($store['r_export_pdf']) || isset($store['r_export_excel']) || isset($store['r_export_csv']))){

                parse_str($store['r_import_filtro_check'],$filtro);
                $filtro = array_filter($filtro);

                // # Verificação / Filtro GRID para Inclusão
                if(isset($filtro['grid_fil'])){
                    foreach($filtro['grid_fil'] as $GF_K => $GF){
                        if(in_array($GF_K,['operador','between'])){ continue; }
                        $exp_gf_k = explode('__', $GF_K, 2);
                        // ! Reforço*
                        if(count($exp_gf_k) == 2){
                            $AppModel_get->leftJoin($exp_gf_k[0].'_'.$exp_gf_k[1],$exp_gf_k[0].'.id',$exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$exp_gf_k[0].'_id');
                            if(count($GF)){
                                foreach($GF as $GF_C => $GF_V){
                                    if(empty($GF_V)){ continue; }
                                    if(isset($filtro['grid_fil']['operador'][$GF_K][$GF_C]) || isset($filtro['grid_fil']['between'][$GF_K][$GF_C])){
                                        if($filtro['grid_fil']['operador'][$GF_K][$GF_C] == 'contem'){
                                            $AppModel_get->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                        }elseif($filtro['grid_fil']['operador'][$GF_K][$GF_C] == 'entre'){
                                            $AppModel_get->whereBetween($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, [$GF_V,$filtro['grid_fil']['between'][$GF_K][$GF_C]]);
                                        }else {
                                            $AppModel_get->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $filtro['grid_fil']['operador'][$GF_K][$GF_C], $GF_V);
                                        }
                                    }else {
                                        if(is_numeric($GF_V)){
                                            $AppModel_get->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                        }else {
                                            if(gettype($GF_V) == 'array'){
                                                $GF_V = array_filter($GF_V);
                                                $AppModel_get->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, $GF_V);
                                            }else {
                                                $AppModel_get->where($exp_gf_k[0].'_'.$exp_gf_k[1].'.'.$GF_C, "LIKE", "%" . $GF_V . "%");
                                            }
                                        }
                                    }
                                }
                            }
                        }

                    }
                }
                // - #
                unset($filtro['grid_fil']);
                if(!empty($filtro)){

                    $operador = [];
                    $between  = [];

                    if(isset($filtro['operador']) && !empty($filtro['operador'])) {
                        $operador = $filtro['operador'];
                        unset($filtro['operador']);
                    }

                    if(isset($filtro['between']) && !empty($filtro['between'])) {
                        $between = $filtro['between'];
                        unset($filtro['between']);
                    }

                    if(isset($filtro['_token'])) {
                        unset($filtro['_token']);
                    }

                    foreach($filtro as $key => $value) {
                        if($filtro[$key] === 'on') {
                            $filtro[$key] = 1;
                        }

                        if (array_key_exists($key, $operador)) {
                            if($operador[$key] == 'contem') {
                                $AppModel_get->where((new $AppModel())->getTable().'.'.$key, "LIKE", "%" . $filtro[$key] . "%");
                            }elseif ($operador[$key] == 'entre') {
                                $AppModel_get->whereBetween((new $AppModel())->getTable().'.'.$key, [$filtro[$key], $between[$key]]);
                            }else{
                                $AppModel_get->where((new $AppModel())->getTable().'.'.$key, $operador[$key], $filtro[$key]);
                            }
                        }else{
                            if(is_numeric($filtro[$key])) {
                                $AppModel_get->where((new $AppModel())->getTable().'.'.$key, $filtro[$key]);
                            }else{
                                if(gettype($filtro[$key]) == 'array'){
                                    $filtro[$key] = array_filter($filtro[$key]);
                                    $AppModel_get->where((new $AppModel())->getTable().'.'.$key, $filtro[$key]);
                                }else {
                                    $AppModel_get->where((new $AppModel())->getTable().'.'.$key, "LIKE", "%" . $filtro[$key] . "%");
                                }
                            }
                        }
                    }
                }

            }

            $export_limit  = (ENV('EXPORT_LIMIT')?ENV('EXPORT_LIMIT'):500);
            $r_export_page = (isset($store['r_export_page'])?$store['r_export_page']:1);

            $AppModel_get  = $AppModel_get
                ->skip(($r_export_page - 1) * $export_limit)
                ->take($export_limit)
                ->orderBy((isset($store['r_export_options_order_field'])?$store['r_export_options_order_field']:'id'), (isset($store['r_export_options_order_field_value'])?$store['r_export_options_order_field_value']:'DESC'))->get();

            // :: Footer - Cálculo Total
            $AppModel_get_footer = [];
            // :: Removendo Coluna se Existir remoção -| Hide
            $AppModel_get = $AppModel_get->each(function($item,$key) use ($store, $SetupModuleFields, &$AppModel_get_footer) {

                // ! *
                if($key == 0){
                    $AppModel_get_footer                = $item->toArray();
                    foreach($AppModel_get_footer as $key2 => $value2){
                        $AppModel_get_footer[$key2]     = '';
                    }
                }

                foreach($SetupModuleFields as $k => $f){

                    // :: Calculo Total - Footer
                    if((isset($f->hidden_view) && !$f->hidden_view) && isset($f->sum) && $f->sum){
                        if(in_array($AppModel_get_footer[$k],['',null])){
                            $AppModel_get_footer[$k]    = 0;
                        }
                        if($f->type == 'calculo'){
                            $AppModel_get_footer[$k]   += \App\Helper\Helper::H_Decimal_ptBR_DB($item->$k);
                        }else {
                            $AppModel_get_footer[$k]   += (double) $item->$k;
                        }
                        $AppModel_get_footer[$k]        = number_format($AppModel_get_footer[$k],2,'.','');
                    }

                    // :: Verificando Ocultação Exportação ( pdf, excel, csv )
                    if(
                        // :: Hide export PDF
                        (isset($f->hidden_export_pdf) && $f->hidden_export_pdf && isset($store['r_export_pdf'])) ||
                        // :: Hide export Excel
                        (isset($f->hidden_export_excel) && $f->hidden_export_excel && isset($store['r_export_excel'])) ||
                        // :: Hide export CSV
                        (isset($f->hidden_export_csv) && $f->hidden_export_csv && isset($store['r_export_csv']))
                    ){
                        unset($AppModel_get_footer[$k]);
                        unset($item->$k); continue;
                    }

                    // :: Lançamento valores relacionados ( relationship, options, checkbox, select ...etc ) e referência de coluna
                    if((isset($store['r_export_pdf']) || isset($store['r_export_excel'])) && isset($SetupModuleFields[$k])){
                        if($SetupModuleFields[$k]->type == 'select' && isset($SetupModuleFields[$k]->relationship) && isset($SetupModuleFields[$k]->relationship_reference)) {
                            $RelationshipnName              = Load::retornaNomeDoModel($SetupModuleFields[$k]->name);
                            $RelationshipReference_Column   = Load::limpar($SetupModuleFields[$k]->relationship_reference);
                            $item_copy                      = clone $item;
                            if($item_copy->$RelationshipnName){
                                $item->$k                   = $item_copy->$RelationshipnName->$RelationshipReference_Column;
                            }else {
                                $item->$k                   = '';
                            }
                            unset($item_copy);
                        }elseif($SetupModuleFields[$k]->type == 'selectbox'){
                            $OptionName                     = 'Get_'.Load::getCleanName($SetupModuleFields[$k]->name);
                            $item->$k                       = $item->$OptionName();
                        }elseif($SetupModuleFields[$k]->type == 'date'){
                            if(!empty($item->$k) && (bool)strtotime($item->$k)){
                                $item->$k                   = date('d/m/Y',strtotime($item->$k));
                            }else {
                                $item->$k                   = '';
                            }
                        }elseif($SetupModuleFields[$k]->type == 'datetime'){
                            if(!empty($item->$k) && (bool)strtotime($item->$k)){
                                $item->$k                   = date('d/m/Y H:i',strtotime($item->$k));
                            }else {
                                $item->$k                   = '';
                            }
                        }elseif($SetupModuleFields[$k]->type == 'dataehoraauto'){
                            if(!empty($item->$k) && (bool)strtotime($item->$k)){
                                $item->$k                   = date('d/m/Y H:i',strtotime($item->$k));
                            }else {
                                $item->$k                   = '';
                            }
                        }elseif($SetupModuleFields[$k]->type == 'checkbox'){
                            $item->$k                       = (!empty($item->$k) && $item->$k?'Sim':'Não');
                        }

                        // ## Campo valor formato Sistema 0.00 -> PT-BR 0,00
                        if(in_array($k,['valor_tarifa','tx_embarque','outras_taxas','desconto','comissao','incentivo','valor_','acrescimo_','desconto_','vlr_pago_'])){
                            $item->$k                       = (!empty($item->$k)?number_format($item->$k,2,',','.'):'0,00');
                        }
                        // - ##
                    }

                }
            });

            $AppModel_get        = $AppModel_get->toArray();

            // :: Cálculo Footer - ( PDF ou Excel )
            if(isset($store['r_export_pdf']) || isset($store['r_export_excel'])){
                array_push($AppModel_get,$AppModel_get_footer);
            }

            // :: Importando os Dados
            if(isset($store['r_import_file'])){

                $filename    = uniqid().".".strtolower($request->r_import_file->getClientOriginalExtension());
                $request->r_import_file->move(public_path("files"), $filename);
                $collections = (new FastExcel)->configureCsv(';')->import('files/' . $filename);
                if(count($collections) > 1000) {
                    Session::flash('flash_error', 'Não é possível importar mais de 1000 linhas em uma única importação. Certifique-se de ter no máximo 1000 linhas por importação!');
                    return back();
                }

                $errors = [];
                foreach ($collections as $key => $value) {
                    try {
                        $value   = array_filter($value);
                        if (isset($value['id']) && $value['id']) {
                            $obj = $AppModel::find($value['id']);
                            unset($value['id']);
                            if(isset($value['r_auth']) && !is_int($value['r_auth'])) {
                                $value['r_auth'] = NULL;
                            }
                            if(!$obj) {
                                $obj = new $AppModel();
                            }
                        }else {
                            $obj = new $AppModel();
                        }
                        $obj->fill($value);
                        $obj->save();
                    }catch (Exception $e) {
                        $value['error'] = $e->getMessage();
                        $errors[] = $value;
                    }
                }

                if(!empty($errors)) {
                    Session::flash('flash_error', "Erro ao importar " . count($errors) . ' linhas! <a href="/errors.xlsx">Clique aqui para baixar o arquivo</a>');
                    (new FastExcel($errors))->export('errors.xlsx');
                }

                // :: Excel - ( xlsx | csv )
            }elseif(isset($store['r_export_excel']) || isset($store['r_export_csv'])){

                if(isset($store['r_export_excel'])){
                    // :: Verificando Label ou Apelido
                    $AppModel_get_transf = [];
                    foreach($AppModel_get as $k => $AM_Get){
                        //print_r($SetupModuleFields);
                        //print_r($AM_Get); exit;
                        foreach($AM_Get as $k2 => $item){
                            $column = $k2;
                            // :: Alterando Coluna para Label caso encontre um 'Alias' ou 'Name'
                            if(isset($SetupModuleFields[$column]) && isset($SetupModuleFields[$column]->alias) && !empty($SetupModuleFields[$column]->alias)){
                                $column = $SetupModuleFields[$column]->alias;
                            }elseif(isset($SetupModuleFields[$column])){
                                $column = $SetupModuleFields[$column]->name;
                            }
                            $AppModel_get_transf[$k][$column] = $item;
                        }
                    }
                    $AppModel_get = $AppModel_get_transf;
                }

                $data     = collect($AppModel_get)->map(function ($item) { return (array) $item; });
                $style    = (new Style())->setShouldWrapText(false);

                if(isset($store['r_export_excel'])){
                    return (new FastExcel($data))->rowsStyle($style)->download($r_model .'.xlsx');
                }elseif(isset($store['r_export_csv'])){
                    return (new FastExcel($data))->rowsStyle($style)->configureCsv(';','"','UTF-8',true)->download($r_model .'.csv');
                }

            }elseif(isset($store['r_export_pdf'])){

                $report                 = new \stdClass();
                $report->image          = null;
                $report->size           = 'a4-horizontal'; // * Padrão
                switch($store['r_export_options_size']){
                    case 'a4-vertical':   $report->size = 1; break;
                    case 'a4-horizontal': $report->size = 2; break;
                    case 'custom':        $report->size = 3; break;
                }
                $report->size_width     = ((!is_null($store['r_export_options_size_width']) && !empty($store['r_export_options_size_width']))?$store['r_export_options_size_width']:1000);
                $report->size_height    = ((!is_null($store['r_export_options_size_height']) && !empty($store['r_export_options_size_height']))?$store['r_export_options_size_height']:1000);
                $report->name           = $r_model;

                $data                   = [
                    'report'    => $report,
                    'query'     => $AppModel_get,
                ];

                // :: Verificando Label ou Apelido
                $AppModel_get_transf = [];
                foreach($AppModel_get as $k => $AM_Get){
                    foreach($AM_Get as $k2 => $item){
                        $column = $k2;
                        if(isset($SetupModuleFields[$column]) && isset($SetupModuleFields[$column]->alias) && !empty($SetupModuleFields[$column]->alias)){
                            $column = $SetupModuleFields[$column]->alias;
                        }elseif(isset($SetupModuleFields[$column])){
                            $column = $SetupModuleFields[$column]->name;
                        }
                        $AppModel_get_transf[$k][$column] = $item;
                    }
                }
                $AppModel_get = $AppModel_get_transf;

                if(!empty($AppModel_get)) {
                    $data['columns']    = array_keys((array)$AppModel_get[0]);
                }else {
                    $data['columns']    = array();
                }

                PDF::setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);

                // A4 - Vertical
                if($report->size == 1){
                    $pdf = PDF::loadView('pdf', $data)->setPaper('a4', 'portrait');
                    // A4 - Horizontal
                }elseif($report->size == 2){
                    $pdf = PDF::loadView('pdf', $data)->setPaper('a4', 'landscape');
                    // A4 - Personalizado
                }elseif($report->size == 3){
                    $pdf = PDF::loadView('pdf', $data)->setPaper([0, 0, $report->size_width, $report->size_height]);
                    // A4 - Vertical - Padrão*
                }else {
                    $pdf = PDF::loadView('pdf', $data)->setPaper('a4', 'landscape');
                }

                return $pdf->download( $report->name . '.pdf' );
            }

            return back();

        }catch(\Exception $e){
            Log::error('Controllers\ImporterController - import_export -| '. $e->getMessage());
            Session::flash('flash_error', "Ocorreu um Erro! Tente novamente.");
            return back();
        }
    }
}
