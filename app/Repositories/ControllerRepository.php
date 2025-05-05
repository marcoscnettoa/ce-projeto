<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;

use Auth;
use Redirect;
use Session;
use Validator;
use Exception;
use Response;
use DB;
use PDF;
use Log;

use \App\Models\Logs;
use \App\Models\Permissions;

class ControllerRepository
{
    public $upload = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'pdf', 'mp4', 'doc', 'docx', 'csv', 'xls', 'xlsx', 'txt', 'zip', 'rar', '7zip');

    public $maxSize = 5242880;

    public static function insertRelationship($cls, $relationships, $model, $field)
    {
        $fieldId = "$field" . '_id';

        foreach ($relationships as $key => $value) {

            $keyClass = 'R' . "$model" . ucwords(mb_strtolower(str_replace(['_', '.', '(', ')', '-'], [' ', ' ', ' ', ' ', ' '], $key)));

            $keyClass = str_replace(' ', '', $keyClass); 

            $class = '\App\Models\\' . $keyClass;

            (new $class())->where($fieldId, $cls->id)->delete();

            $value = array_filter($value);

            foreach ($value as $st) {

                $instance = new $class();

                $instance->$fieldId = $cls->id;
                $instance->$key = $st;
                $instance->save();
            }
        }
    }

    public static function saveGrids($cls, $grids, $field)
    {
        $upload = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'pdf', 'mp4', 'doc', 'docx', 'csv', 'xls', 'xlsx', 'txt', 'zip', 'rar', '7zip');

        $maxSize = 5242880;

        $fieldId = "$field" . '_id';

        foreach ($grids as $gk => $gv) {

            $class = '\App\Models\\' . $gk;

            $values = [];

            foreach ($gv as $k => $v) {
                
                foreach ($v as $vKy => $vValue) {

                    if (!isset($values[$vKy])) {
                        $values[$vKy] = [];
                    }

                    if (is_object($vValue)) {

                        if (!in_array(strtolower($vValue->getClientOriginalExtension()), $upload)) {
                            return back()->withInput()->withErrors('Tipo de arquivo não permitido! Extensões permitidas: ' . implode(", ", $upload));
                        }

                        if ($vValue->getSize() > $maxSize) {
                            return back()->withInput()->withErrors('Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB');
                        }

                        $filename = base64_encode($vValue->getClientOriginalName()) . "-" . uniqid().".".$vValue->getClientOriginalExtension();

                        if(env("FILESYSTEM_DRIVER") == "s3") {

                            Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $filename, file_get_contents($vValue));

                        } else { 

                            $vValue->move(public_path("images"), $filename);

                        } 

                        $values[$vKy][$k] = $filename;
                    }
                    else
                    {
                        $values[$vKy][$k] = $vValue;
                    } 
                }
            }

            (new $class())->where($fieldId, $cls->id)->delete();

            foreach ($values as $st) {

                $st = array_filter($st);

                if (!empty($st)) {
                    
                    $instance = new $class();

                    $instance->$fieldId = $cls->id;

                    $instance->fill($st);

                    $instance->save();
                }

            }
        }
    }

    public static function destroy($model, $id, $url)
    {
        try {

            $user = Auth::user();

            $r_auth = NULL;

            if ($user) {
                $r_auth = $user->id;
            }

            $model = $model::find($id);

            if (!$model) {

                \Session::flash('flash_error', 'Registro não encontrado!');

                return back();
            }

            if (!Permissions::permissaoModerador($user) && $model->r_auth != $r_auth) 
            {
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return back();
            }

            if ($model) {

                $model->delete();

                Session::flash('flash_success', "Registro excluído com sucesso!");

                if ($user) {
                    Logs::cadastrar($user->id, ($user->name . ' excluiu ID #' . $id . ' do módulo ' . get_class($model)));
                }
            }
            else {
                Session::flash('flash_error', "Registro não encontrado!");
            }

        } catch (\Illuminate\Database\QueryException $qe) {

            Log::info($qe->getMessage());

            Session::flash('flash_error', 'Não é possível excluir este registro!');

        } catch (Exception $e) {

            Log::info($e->getMessage());

            Session::flash('flash_error', "Erro ao excluir registro!");
        }

        return Redirect::to($url);
    }
}
