@component('mail::message')

<p>Olá, tudo bem? </p>

<p>Segue cobrança referente ao Produto/Serviço {{ $obj->product }}.</p>

<p><a href="{{ $obj->payment_link }}">{{ $obj->payment_link }}</a></p>

<p>{{ env('APP_NAME') }}</p>

@endcomponent