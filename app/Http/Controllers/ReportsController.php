<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;
use Redirect;
use Session;
use PDF;
use Exception;

use OpenSpout\Common\Entity\Style\Style;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use \App\Models\Logs;
use \App\Models\Permissions;
use \App\Models\Reports;

class ReportsController extends Controller
{
    public function index(Request $request)
    {

        // - # Temporário
        if(!Schema::hasColumn('r_reports', 'size_width')) {
            Schema::table('r_reports', function (Blueprint $table) {
                $table->string('size_width',10)->nullable()->after('size');
            });
        }
        if(!Schema::hasColumn('r_reports', 'size_height')) {
            Schema::table('r_reports', function (Blueprint $table) {
                $table->string('size_height',10)->nullable()->after('size_width');
            });
        }
        // - #

        $user = Auth::user();

        if (Permissions::permissaoModerador($user)) {
            $reports = Reports::orderBy('id', 'DESC')->limit(1000)->get();
        } else {
            $reports = Reports::where('r_auth', $user->id)->orderBy('id', 'DESC')->limit(1000)->get();
        }

        Logs::cadastrar($user->id, ($user->name . ' visualizou a lista de relatórios'));

        return view('reports.index', ['reports' => $reports]);
    }

    public function create()
    {
        return view('reports.add');
    }

    public function store(Request $request)
    {
        try {

            $user = Auth::user();

            $data = $request->all();

            $reports = new Reports();

            $reports->name = $data['name'];
            $reports->query = $data['query'];
            //$reports->description = $data['description'];
            //$reports->size = $data['size'];

            $reports->r_auth = $user->id;

            if ($request->image) {

                $img = time().'.'.$request->image->getClientOriginalExtension();

                $request->image->move(public_path('images'), $img);

                $reports->image = $img;

            }

            // # -
            $reports->size              = $data['size'];
            $reports->size_height       = null;
            $reports->size_width        = null;
            if($data['size'] == 3){
                $reports->size_height   = (!empty($data['size_height'])?$data['size_height']:2480);
                $reports->size_width    = (!empty($data['size_width'])?$data['size_width']:3508);
            }
            // - #

            $reports->save();

            Session::flash('flash_success', "Relatório cadastrado com sucesso!");

            Logs::cadastrar($user->id, ($user->name . ' cadastrou um relatório: ' . $reports->name));

        } catch (Exception $e) {
            Session::flash('flash_error', "Erro ao cadastrar relatório!");
        }

        return Redirect::to('/reports');
    }

    public function show($id)
    {
        $report = Reports::find($id);

        $query = DB::select($report->query);

        if(!empty($query))
        {
            $columns = array_keys((array)$query[0]);
        }
        else
        {
            $columns = array();
        }

        return view('reports.show', [
            'report' => $report,
            'query' => $query,
            'columns' => $columns,
        ]);
    }

    public function edit($id)
    {
        $reports = Reports::find($id);

        return view('reports.edit', [
            'reports' => $reports,
        ]);
    }

    // - #
    public function generate($id, Request $request)
    {
        try {

            set_time_limit(-1);

            $report = Reports::find($id);

            // # EXCEL
            if($request->get('tipo') and $request->get('tipo') == 'excel'){
                $query  = DB::select($report->query);
                $data   = collect($query)->map(function ($item) {
                    return (array) $item;
                });
                $style  = (new Style())
                    ->setShouldWrapText(false);

                return (new FastExcel($data))->rowsStyle($style)->download($report->name .'.xlsx');

            // # PDF
            }else{
                $query = DB::select($report->query);
                $data = [
                    'report' => $report,
                    'query' => $query,
                ];
                if(!empty($query))
                {
                    $data['columns'] = array_keys((array)$query[0]);
                }
                else
                {
                    $data['columns'] = array();
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

            // ### ANTIGO ### -| ! Breve Remoção
            /*$report = Reports::find($id);

            $query = DB::select($report->query);

            $data = [
                'report' => $report,
                'query' => $query,
            ];

            if(!empty($query))
            {
                $data['columns'] = array_keys((array)$query[0]);
            }
            else
            {
                $data['columns'] = array();
            }

            PDF::setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);

            $pdf = PDF::loadView('pdf', $data)->setPaper('a4', 'landscape');

            return $pdf->download( $report->name . '.pdf' );*/

        } catch (\Illuminate\Database\QueryException $e) {

            Session::flash('flash_error', "Erro ao gerar o relatório!");

            return Redirect::to('/reports');

        }
    }

    public function update(Request $request)
    {
        try {

            $data = $request->all();

            $reports = Reports::find($request->get('id'));

            $reports->name = $data['name'];
            $reports->query = $data['query'];
            //$reports->description = $data['description'];
            //$reports->size = $data['size'];

            if ($request->image) {

                $img = time().'.'.$request->image->getClientOriginalExtension();

                $request->image->move(public_path('images'), $img);

                $reports->image = $img;

            }

            // # -
            $reports->size              = $data['size'];
            $reports->size_height       = null;
            $reports->size_width        = null;
            if($data['size'] == 3){
                $reports->size_height   = (!empty($data['size_height'])?$data['size_height']:2480);
                $reports->size_width    = (!empty($data['size_width'])?$data['size_width']:3508);
            }
            // - #

            $reports->save();

            Session::flash('flash_success', "Relatório atualizado com sucesso!");

            Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' atualizou um relatório: ' . $reports->name));

        } catch (Exception $e) {
            Session::flash('flash_error', "Erro ao atualizar relatório!");
        }

        return Redirect::to('/reports');
    }

    public function destroy($id)
    {
        try {

            $reports = Reports::find($id)->delete();

            Session::flash('flash_success', "Relatório excluído com sucesso!");

            Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' excluiu o relatório ID: ' . $id));

        } catch (\Illuminate\Database\QueryException $e) {

            Session::flash('flash_error', 'Não é possível excluir este relatório!');

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao excluir relatório!");
        }

        return Redirect::to('/reports');
    }
}
