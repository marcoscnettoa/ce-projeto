@extends('layouts.app')

@section('content')

<?php 
    
    $backups = \Storage::disk('s3')->allFiles('/files/'.env('FILEKEY').'/databases/');

    function size($size)
    {
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

?>

<h3 class="box-title" style="margin-left: 15px;">Backups</h3>

<section class="content">

<div class="box">
    
    <div class="box-body table-responsive">

        <table id="datatable-master" class="display table table-striped table-bordered" cellspacing="0" width="100%">
            
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tamanho</th>
                    <th>Baixar</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                @foreach($backups as $key => $value)

                    @php

                        $disk = \Storage::disk('s3');

                        $size = $disk->size($value);

                        $modified = date(DATE_RFC2822, $disk->lastModified($value));

                        $base64 = base64_encode($value);

                    @endphp

                    <tr>
                        <td>{{ date('d/m/Y H:i', strtotime($modified)) }}</td>
                        <td>{{size($size)}}</td>
                        <td><a href='{{ URL("/backups") }}/{{env("FILEKEY")}}?file={{$base64}}'><i class="glyphicon glyphicon-download-alt"></i></a></td>

                        <td style="width: 50px;">
                            @if(strpos($value, 'up') !== false)
                                Editado
                            @else
                                Automático
                            @endif
                        </td>   
                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

</section>

@endsection