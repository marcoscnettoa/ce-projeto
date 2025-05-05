<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{$report->name}}</title>

    <style>
        th, td{
            border: solid 1px #ccc;
            font-size: 12px;
            text-align: left;
        }
    </style>
</head>
<body>

    <div style="text-align: center;">
        @if($report->image)
            <img src="{{ URL('/') }}/images/{{$report->image}}" width="50">
        @endif
        <h3>{{$report->name}}</h3>
    </div>

    @if(!empty($query))
        <table width="100%">
            <thead>
            <tr>
                @foreach($columns as $key)
                    <th style="padding: 3px; background-color:#f1f1f1; white-space: nowrap;">{{$key}}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($query as $column)
                <tr>
                    @php
                        $column = array_values((array)$column);
                        echo '<td style="padding: 3px;">' . implode('</td><td style="padding: 3px;">', $column) . '</td>';
                    @endphp
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <h5>Nenhum resultado encontrado</h5>
    @endif

</body>
</html>
