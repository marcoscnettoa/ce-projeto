<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;

use \App\Models\GanttTask;
use \App\Models\GanttLink;
 
class GanttLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([]);
    }

    public function store(Request $request){
        
        $link = new GanttLink();
 
        $link->type = $request->type;
        $link->source = $request->source;
        $link->target = $request->target;
        
        $link->save();
 
        return response()->json([
            "action"=> "inserted",
            "tid" => $link->id
        ]);
    }
 
    public function update($id, Request $request){
       
        $link = GanttLink::find($id);
 
        $link->type = $request->type;
        $link->source = $request->source;
        $link->target = $request->target;
 
        $link->save();
 
        return response()->json([
            "action"=> "updated"
        ]);
    }
 
    public function destroy($id){
      
        $link = GanttLink::find($id);
        $link->delete();
 
        return response()->json([
            "action"=> "deleted"
        ]);
    }
}