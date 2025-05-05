@component('mail::message')

@php

$array = $json->toArray();

@endphp

@if(isset($array['id']))
<b>Código</b>: {{ $array['id'] }}<br>
@endif

@php

foreach($array as $key => $value)
{
    if($key == 'r_auth' or $key == 'id'){
        continue;
    }

    if($key == 'e_mail'){
        $key = 'e-mail';
    }

    $key = ucfirst(mb_strtolower((str_replace('_', ' ', $key))));

@endphp

<b>{{ $key }}</b>: {{ $value }} <br>

@php
}
@endphp

@if(\Auth::user())
<b>Usuário que executou a ação</b>: {{\Auth::user()->name}}<br>
@endif

@endcomponent