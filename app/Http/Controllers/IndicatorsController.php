<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Redirect;
use Session;
use Exception;

use \App\Models\Logs;
use \App\Models\Permissions;
use \App\Models\Indicators;

class IndicatorsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (Permissions::permissaoModerador($user)) {
            $indicators = Indicators::orderBy('id', 'DESC')->limit(1000)->get();
        } else {
            $indicators = Indicators::where('r_auth', $user->id)->orderBy('id', 'DESC')->limit(1000)->get();
        }

        Logs::cadastrar($user->id, ($user->name . ' visualizou a lista de indicadores'));

        return view('indicators.index', ['indicators' => $indicators]);
    }

    public function create()
    {
        return view('indicators.add');
    }

    public function store(Request $request)
    {
        try {

            $user = Auth::user();

            $data = $request->all();

            $indicators = new Indicators();

            $indicators->name = $data['name'];
            $indicators->query = strtolower($data['query']);
            $indicators->color = $data['color'];
            $indicators->description = $data['description'];
            $indicators->link = $data['link'];
            $indicators->size = $data['size'];
            $indicators->glyphicon = $data['glyphicon'];

            if (!isset($data['r_auth'])) {
                $indicators->r_auth = $user->id;
            }
            else
            {
                $indicators->r_auth = $data['r_auth'];
            }

            $indicators->save();

            Session::flash('flash_success', "Indicador cadastrado com sucesso!");

            Logs::cadastrar($user->id, ($user->name . ' cadastrou um indicador: ' . $indicators->name));

        } catch (Exception $e) {
            Session::flash('flash_error', "Erro ao cadastrar indicador!");
        }

        return Redirect::to('/indicators');
    }

    public function show($id)
    {
        $indicators = Indicators::find($id);

        return view('indicators.show', [
            'indicators' => $indicators,
        ]);
    }

    public function edit($id)
    {
        $indicators = Indicators::find($id);

        return view('indicators.edit', [
            'indicators' => $indicators,
        ]);
    }

    public function update(Request $request, $id)
    {
        try {

            $user = Auth::user();

            $data = $request->all();

            $indicators = Indicators::find($request->get('id'));

            $indicators->name = $data['name'];
            $indicators->query = strtolower($data['query']);
            $indicators->color = $data['color'];
            $indicators->description = $data['description'];
            $indicators->link = $data['link'];
            $indicators->size = $data['size'];
            $indicators->glyphicon = $data['glyphicon'];

            if (!isset($data['r_auth'])) {
                $indicators->r_auth = $user->id;
            }
            else
            {
                $indicators->r_auth = $data['r_auth'];
            }

            $indicators->save();

            Session::flash('flash_success', "Indicador atualizado com sucesso!");

            Logs::cadastrar($user->id, ($user->name . ' atualizou um indicador: ' . $indicators->name));

        } catch (Exception $e) {
            Session::flash('flash_error', "Erro ao atualizar indicador!");
        }

        return Redirect::to('/indicators');
    }

    public function destroy($id)
    {
        try {

            $indicators = Indicators::find($id);

            if ($indicators) {

                $indicators->delete();

                Session::flash('flash_success', "Indicador excluído com sucesso!");

                Logs::cadastrar(Auth::user()->id, (Auth::user()->name . ' excluiu o indicador ID: ' . $id));
            }
            else {
                Session::flash('flash_error', "Indicador não encontrado!");
            }

        } catch (\Illuminate\Database\QueryException $e) {

            Session::flash('flash_error', 'Não é possível excluir este indicador!');

        } catch (Exception $e) {

            Session::flash('flash_error', "Erro ao excluir indicador!");
        }

        return Redirect::to('/indicators');
    }
}
