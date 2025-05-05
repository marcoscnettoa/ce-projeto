{{-- # Gráficos --}}
@php
    try {
        $Graficos     = null;
        if(class_exists('\App\Models\MRAGraficos')) {
            $Graficos = \App\Models\MRAGraficos::find(1);
        }
    }catch(\Exception $e){ /* ... */ }
@endphp
{{-- - # --}}

@extends('layouts.app')

@section('style')
    <style type="text/css">
        @if(!is_null($Graficos) && $Graficos && $Graficos->status)
            {!! $Graficos->css !!}
        @endif
    </style>
@endsection

@section('content')

    @if(isset($calendar) && $calendar)
        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.css"/>
    @endif

    <section class="content">

        @if(1)
            <div class="page-header">
                <div class="jumbotron">
                    <h2 style="margin-bottom: 20px;">Seja bem-vindo(a)!</h2>
                </div>
            </div>
        @endif

        {{-- # Gráficos --}}
        @php
            if(!is_null($Graficos) && $Graficos && $Graficos->status){
                try {
                    eval(\App\Helper\Security::NO_CODE($Graficos->codigo,['db'],true));
                }catch(\Throwable $e){ echo '[ Graficos -> CÓDIGO -| Error ]'; Log::info('Graficos -> CÓDIGO -| Error: '.$e->getMessage()); }
            }
        @endphp

        {{-- # Gráficos - Topo --}}
        @if(!is_null($Graficos) && $Graficos && $Graficos->status && !$Graficos->posicao)
            @php
                try {
                    $BladeCompile = \Blade::compileString(\App\Helper\Security::NO_CODE($Graficos->html,['db'],true));
                    eval("?>" . $BladeCompile . "<?php");
                }catch(\Throwable $e){ echo '[ Graficos -> HTML -| Error ]'; Log::info('Graficos -> CÓDIGO -| Error: '.$e->getMessage()); }
            @endphp
        @endif

        <div class="row">

            @if(1)

                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\IndicatorsController@show'))

                    @foreach($indicadores as $key => $value)

                        @php

                            try {

                                if (strpos(strtolower($value->query), 'select') !== false) {

                                    $query = strtolower($value->query);

                                    $query = str_replace('$user_id', \Auth::user()->id, $query);

                                    $result = DB::select($query);

                                    if(!empty($result)){
                                        $count = current($result[0]);
                                    } else {
                                        $count = 0;
                                    }

                                } else {

                                    $value->name = 'SCRIPT INVÁLIDO';
                                    $value->color = 'red';
                                    $value->description = 'Não foi possível obter resultado';

                                    $count = 0;
                                }

                            } catch (Exception $e) {
                                continue;
                            }

                        @endphp

                        <div class="col-md-{{$value->size}} col-sm-{{($value->size * 2)}} col-xs-{{($value->size * 4)}}">
                            <div class="info-box" style="height: 90px;">
                                <span class="info-box-icon bg-blue" style="background-color: {{$value->color}} !important;"><i class="{{$value->glyphicon}}"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-number">{{$count}}</span>

                                    @if($value->link != '')
                                        <a href="{{$value->link}}" target="_blank">
                                    @endif

                                    <p>{{$value->name}}</p>

                                    @if($value->link != '')
                                        </a>
                                    @endif

                                    <p>{{$value->description}}</p>

                                </div>
                            </div>
                        </div>

                    @endforeach

                @endif

            @endif

        </div>

        @if(isset($calendar) && $calendar)
            <div class="row calendar">
                <div class="col-md-12">
                    <div class="box" style="padding: 20px;">
                    {!! $calendar->calendar() !!}
                    </div>
                </div>
            </div>
        @endif

        @if(env('GANTT') and \App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\GanttController@get'))
            <div class="row">
                <div class="col-md-12">
                    <div id="gantt_here" style="min-height: 500px;"></div>
                </div>
            </div>
        @endif

        {{-- # Gráficos - Fundo --}}
        @if(!is_null($Graficos) && $Graficos && $Graficos->status && $Graficos->posicao)
            @php
                try {
                    $BladeCompile = \Blade::compileString(\App\Helper\Security::NO_CODE($Graficos->html,['db'],true));
                    eval("?>" . $BladeCompile . "<?php");
                }catch(\Throwable $e){ echo '[ Graficos -> HTML -| Error ]'; Log::info('Graficos -> CÓDIGO -| Error: '.$e->getMessage()); }
            @endphp
        @endif
        {{-- - # --}}

    </section>

    @section('script')
        <script type="text/javascript">
            {{-- # Gráficos --}}
            try {
                @if(!is_null($Graficos) && $Graficos && $Graficos->status)
                    {{-- $Graficos->script --}}
                    @php
                        try {
                            $BladeCompile = \Blade::compileString(\App\Helper\Security::NO_CODE($Graficos->script,['db'],true));
                            eval("?>" . $BladeCompile . "<?php");
                        }catch(\Throwable $e){ echo '[ Graficos -> SCRIPT -| Error ]'; Log::info('Graficos -> SCRIPT -| Error: '.$e->getMessage()); }
                    @endphp
                @endif
            }catch(e){ console.log('Graficos -> Script -| Erro:'+e); }
            {{-- - # --}}
        </script>
    @endsection

@endsection
