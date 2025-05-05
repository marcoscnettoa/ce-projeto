<?php
namespace App\Http\Controllers;

use \App\Models\GanttTask;
use \App\Models\GanttLink;

class GanttController extends Controller
{
    public function get(){

        $tasks = new GanttTask();
        $links = new GanttLink();

        return response()->json([
            "data" => $tasks->orderBy('sortorder')->get(),
            "links" => $links->all()
        ]);
    }
}
