<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Storage;
use Filesystem;
use Response;
use Exception;

class BackupsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backups.index');
    }

    public function show($sys, Request $request)
    {
        $filename = $request->get('file');

        $filename = base64_decode($filename);

        $file = Storage::disk('s3')->get($filename);

        $f = explode("-", $filename);

        $f = end($f);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => "attachment; filename={$f}",
            'filename'=> $f
        ];

        return response($file, 200, $headers);
    }
}
