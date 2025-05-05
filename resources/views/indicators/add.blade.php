@extends('layouts.app')

@section('content')

@php
    
    $controller = get_class(\Request::route()->getController());

@endphp

<h3 class="box-title" style="margin-left: 15px;">Cadastrar indicador</h3>

<section class="content">

<div class="box">

    {!! Form::open(['url' => 'indicators', 'method' => 'post', 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8']) !!}

        <div class="box-body">

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('name', 'Nome') !!}
                            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder'=>'Ex: Total de usuários', 'required'=> 'required']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('Icon') !!}

                            <select name="glyphicon" id="glyphicon" class="selectpicker form-control">
                                <option data-icon="glyphicon glyphicon-signal" value="glyphicon glyphicon-signal" selected="selected"></option>
                                <option data-icon="glyphicon glyphicon-asterisk" value="glyphicon glyphicon-asterisk"></option>
                                <option data-icon="glyphicon glyphicon-plus" value="glyphicon glyphicon-plus"></option>
                                <option data-icon="glyphicon glyphicon-minus" value="glyphicon glyphicon-minus"></option>
                                <option data-icon="glyphicon glyphicon-euro" value="glyphicon glyphicon-euro"></option>
                                <option data-icon="glyphicon glyphicon-cloud" value="glyphicon glyphicon-cloud"></option>
                                <option data-icon="glyphicon glyphicon-envelope" value="glyphicon glyphicon-envelope"></option>
                                <option data-icon="glyphicon glyphicon-pencil" value="glyphicon glyphicon-pencil"></option>
                                <option data-icon="glyphicon glyphicon-glass" value="glyphicon glyphicon-glass"></option>
                                <option data-icon="glyphicon glyphicon-music" value="glyphicon glyphicon-music"></option>
                                <option data-icon="glyphicon glyphicon-search" value="glyphicon glyphicon-search"></option>
                                <option data-icon="glyphicon glyphicon-heart" value="glyphicon glyphicon-heart"></option>
                                <option data-icon="glyphicon glyphicon-star" value="glyphicon glyphicon-star"></option>
                                <option data-icon="glyphicon glyphicon-star-empty" value="glyphicon glyphicon-star-empty"></option>
                                <option data-icon="glyphicon glyphicon-user" value="glyphicon glyphicon-user"></option>
                                <option data-icon="glyphicon glyphicon-film" value="glyphicon glyphicon-film"></option>
                                <option data-icon="glyphicon glyphicon-th-large" value="glyphicon glyphicon-th-large"></option>
                                <option data-icon="glyphicon glyphicon-th" value="glyphicon glyphicon-th"></option>
                                <option data-icon="glyphicon glyphicon-th-list" value="glyphicon glyphicon-th-list"></option>
                                <option data-icon="glyphicon glyphicon-ok" value="glyphicon glyphicon-ok"></option>
                                <option data-icon="glyphicon glyphicon-remove" value="glyphicon glyphicon-remove"></option>
                                <option data-icon="glyphicon glyphicon-zoom-in" value="glyphicon glyphicon-zoom-in"></option>
                                <option data-icon="glyphicon glyphicon-zoom-out" value="glyphicon glyphicon-zoom-out"></option>
                                <option data-icon="glyphicon glyphicon-off" value="glyphicon glyphicon-off"></option>
                                <option data-icon="glyphicon glyphicon-signal" value="glyphicon glyphicon-signal"></option>
                                <option data-icon="glyphicon glyphicon-cog" value="glyphicon glyphicon-cog"></option>
                                <option data-icon="glyphicon glyphicon-trash" value="glyphicon glyphicon-trash"></option>
                                <option data-icon="glyphicon glyphicon-home" value="glyphicon glyphicon-home"></option>
                                <option data-icon="glyphicon glyphicon-file" value="glyphicon glyphicon-file"></option>
                                <option data-icon="glyphicon glyphicon-time" value="glyphicon glyphicon-time"></option>
                                <option data-icon="glyphicon glyphicon-road" value="glyphicon glyphicon-road"></option>
                                <option data-icon="glyphicon glyphicon-download-alt" value="glyphicon glyphicon-download-alt"></option>
                                <option data-icon="glyphicon glyphicon-download" value="glyphicon glyphicon-download"></option>
                                <option data-icon="glyphicon glyphicon-upload" value="glyphicon glyphicon-upload"></option>
                                <option data-icon="glyphicon glyphicon-inbox" value="glyphicon glyphicon-inbox"></option>
                                <option data-icon="glyphicon glyphicon-play-circle" value="glyphicon glyphicon-play-circle"></option>
                                <option data-icon="glyphicon glyphicon-repeat" value="glyphicon glyphicon-repeat"></option>
                                <option data-icon="glyphicon glyphicon-refresh" value="glyphicon glyphicon-refresh"></option>
                                <option data-icon="glyphicon glyphicon-list-alt" value="glyphicon glyphicon-list-alt"></option>
                                <option data-icon="glyphicon glyphicon-lock" value="glyphicon glyphicon-lock"></option>
                                <option data-icon="glyphicon glyphicon-flag" value="glyphicon glyphicon-flag"></option>
                                <option data-icon="glyphicon glyphicon-headphones" value="glyphicon glyphicon-headphones"></option>
                                <option data-icon="glyphicon glyphicon-volume-off" value="glyphicon glyphicon-volume-off"></option>
                                <option data-icon="glyphicon glyphicon-volume-down" value="glyphicon glyphicon-volume-down"></option>
                                <option data-icon="glyphicon glyphicon-volume-up" value="glyphicon glyphicon-volume-up"></option>
                                <option data-icon="glyphicon glyphicon-qrcode" value="glyphicon glyphicon-qrcode"></option>
                                <option data-icon="glyphicon glyphicon-barcode" value="glyphicon glyphicon-barcode"></option>
                                <option data-icon="glyphicon glyphicon-tag" value="glyphicon glyphicon-tag"></option>
                                <option data-icon="glyphicon glyphicon-tags" value="glyphicon glyphicon-tags"></option>
                                <option data-icon="glyphicon glyphicon-book" value="glyphicon glyphicon-book"></option>
                                <option data-icon="glyphicon glyphicon-bookmark" value="glyphicon glyphicon-bookmark"></option>
                                <option data-icon="glyphicon glyphicon-print" value="glyphicon glyphicon-print"></option>
                                <option data-icon="glyphicon glyphicon-camera" value="glyphicon glyphicon-camera"></option>
                                <option data-icon="glyphicon glyphicon-font" value="glyphicon glyphicon-font"></option>
                                <option data-icon="glyphicon glyphicon-bold" value="glyphicon glyphicon-bold"></option>
                                <option data-icon="glyphicon glyphicon-italic" value="glyphicon glyphicon-italic"></option>
                                <option data-icon="glyphicon glyphicon-text-height" value="glyphicon glyphicon-text-height"></option>
                                <option data-icon="glyphicon glyphicon-text-width" value="glyphicon glyphicon-text-width"></option>
                                <option data-icon="glyphicon glyphicon-align-left" value="glyphicon glyphicon-align-left"></option>
                                <option data-icon="glyphicon glyphicon-align-center" value="glyphicon glyphicon-align-center"></option>
                                <option data-icon="glyphicon glyphicon-align-justify" value="glyphicon glyphicon-align-justify"></option>
                                <option data-icon="glyphicon glyphicon-list" value="glyphicon glyphicon-list"></option>
                                <option data-icon="glyphicon glyphicon-indent-left" value="glyphicon glyphicon-indent-left"></option>
                                <option data-icon="glyphicon glyphicon-indent-right" value="glyphicon glyphicon-indent-right"></option>
                                <option data-icon="glyphicon glyphicon-facetime-video" value="glyphicon glyphicon-facetime-video"></option>
                                <option data-icon="glyphicon glyphicon-picture" value="glyphicon glyphicon-picture"></option>
                                <option data-icon="glyphicon glyphicon-map-marker" value="glyphicon glyphicon-map-marker"></option>
                                <option data-icon="glyphicon glyphicon-adjust" value="glyphicon glyphicon-adjust"></option>
                                <option data-icon="glyphicon glyphicon-tint" value="glyphicon glyphicon-tint"></option>
                                <option data-icon="glyphicon glyphicon-edit" value="glyphicon glyphicon-edit"></option>
                                <option data-icon="glyphicon glyphicon-share" value="glyphicon glyphicon-share"></option>
                                <option data-icon="glyphicon glyphicon-check" value="glyphicon glyphicon-check"></option>
                                <option data-icon="glyphicon glyphicon-move" value="glyphicon glyphicon-move"></option>
                                <option data-icon="glyphicon glyphicon-step-backward" value="glyphicon glyphicon-step-backward"></option>
                                <option data-icon="glyphicon glyphicon-fast-backward" value="glyphicon glyphicon-fast-backward"></option>
                                <option data-icon="glyphicon glyphicon-backward" value="glyphicon glyphicon-backward"></option>
                                <option data-icon="glyphicon glyphicon-play" value="glyphicon glyphicon-play"></option>
                                <option data-icon="glyphicon glyphicon-pause" value="glyphicon glyphicon-pause"></option>
                                <option data-icon="glyphicon glyphicon-stop" value="glyphicon glyphicon-stop"></option>
                                <option data-icon="glyphicon glyphicon-forward" value="glyphicon glyphicon-forward"></option>
                                <option data-icon="glyphicon glyphicon-fast-forward" value="glyphicon glyphicon-fast-forward"></option>
                                <option data-icon="glyphicon glyphicon-step-forward" value="glyphicon glyphicon-step-forward"></option>
                                <option data-icon="glyphicon glyphicon-eject" value="glyphicon glyphicon-eject"></option>
                                <option data-icon="glyphicon glyphicon-chevron-left" value="glyphicon glyphicon-chevron-left"></option>
                                <option data-icon="glyphicon glyphicon-chevron-right" value="glyphicon glyphicon-chevron-right"></option>
                                <option data-icon="glyphicon glyphicon-plus-sign" value="glyphicon glyphicon-plus-sign"></option>
                                <option data-icon="glyphicon glyphicon-minus-sign" value="glyphicon glyphicon-minus-sign"></option>
                                <option data-icon="glyphicon glyphicon-remove-sign" value="glyphicon glyphicon-remove-sign"></option>
                                <option data-icon="glyphicon glyphicon-ok-sign" value="glyphicon glyphicon-ok-sign"></option>
                                <option data-icon="glyphicon glyphicon-question-sign" value="glyphicon glyphicon-question-sign"></option>
                                <option data-icon="glyphicon glyphicon-info-sign" value="glyphicon glyphicon-info-sign"></option>
                                <option data-icon="glyphicon glyphicon-screenshot" value="glyphicon glyphicon-screenshot"></option>
                                <option data-icon="glyphicon glyphicon-remove-circle" value="glyphicon glyphicon-remove-circle"></option>
                                <option data-icon="glyphicon glyphicon-ok-circle" value="glyphicon glyphicon-ok-circle"></option>
                                <option data-icon="glyphicon glyphicon-ban-circle" value="glyphicon glyphicon-ban-circle"></option>
                                <option data-icon="glyphicon glyphicon-arrow-left" value="glyphicon glyphicon-arrow-left"></option>
                                <option data-icon="glyphicon glyphicon-arrow-right" value="glyphicon glyphicon-arrow-right"></option>
                                <option data-icon="glyphicon glyphicon-arrow-up" value="glyphicon glyphicon-arrow-up"></option>
                                <option data-icon="glyphicon glyphicon-arrow-down" value="glyphicon glyphicon-arrow-down"></option>
                                <option data-icon="glyphicon glyphicon-share-alt" value="glyphicon glyphicon-share-alt"></option>
                                <option data-icon="glyphicon glyphicon-resize-full" value="glyphicon glyphicon-resize-full"></option>
                                <option data-icon="glyphicon glyphicon-resize-small" value="glyphicon glyphicon-resize-small"></option>
                                <option data-icon="glyphicon glyphicon-exclamation-sign" value="glyphicon glyphicon-exclamation-sign"></option>
                                <option data-icon="glyphicon glyphicon-gift" value="glyphicon glyphicon-gift"></option>
                                <option data-icon="glyphicon glyphicon-leaf" value="glyphicon glyphicon-leaf"></option>
                                <option data-icon="glyphicon glyphicon-fire" value="glyphicon glyphicon-fire"></option>
                                <option data-icon="glyphicon glyphicon-eye-open" value="glyphicon glyphicon-eye-open"></option>
                                <option data-icon="glyphicon glyphicon-eye-close" value="glyphicon glyphicon-eye-close"></option>
                                <option data-icon="glyphicon glyphicon-warning-sign" value="glyphicon glyphicon-warning-sign"></option>
                                <option data-icon="glyphicon glyphicon-plane" value="glyphicon glyphicon-plane"></option>
                                <option data-icon="glyphicon glyphicon-calendar" value="glyphicon glyphicon-calendar"></option>
                                <option data-icon="glyphicon glyphicon-random" value="glyphicon glyphicon-random"></option>
                                <option data-icon="glyphicon glyphicon-comment" value="glyphicon glyphicon-comment"></option>
                                <option data-icon="glyphicon glyphicon-magnet" value="glyphicon glyphicon-magnet"></option>
                                <option data-icon="glyphicon glyphicon-chevron-up" value="glyphicon glyphicon-chevron-up"></option>
                                <option data-icon="glyphicon glyphicon-chevron-down" value="glyphicon glyphicon-chevron-down"></option>
                                <option data-icon="glyphicon glyphicon-retweet" value="glyphicon glyphicon-retweet"></option>
                                <option data-icon="glyphicon glyphicon-shopping-cart" value="glyphicon glyphicon-shopping-cart"></option>
                                <option data-icon="glyphicon glyphicon-folder-close" value="glyphicon glyphicon-folder-close"></option>
                                <option data-icon="glyphicon glyphicon-folder-open" value="glyphicon glyphicon-folder-open"></option>
                                <option data-icon="glyphicon glyphicon-resize-vertical" value="glyphicon glyphicon-resize-vertical"></option>
                                <option data-icon="glyphicon glyphicon-resize-horizontal" value="glyphicon glyphicon-resize-horizontal"></option>
                                <option data-icon="glyphicon glyphicon-hdd" value="glyphicon glyphicon-hdd"></option>
                                <option data-icon="glyphicon glyphicon-bullhorn" value="glyphicon glyphicon-bullhorn"></option>
                                <option data-icon="glyphicon glyphicon-bell" value="glyphicon glyphicon-bell"></option>
                                <option data-icon="glyphicon glyphicon-certificate" value="glyphicon glyphicon-certificate"></option>
                                <option data-icon="glyphicon glyphicon-thumbs-up" value="glyphicon glyphicon-thumbs-up"></option>
                                <option data-icon="glyphicon glyphicon-thumbs-down" value="glyphicon glyphicon-thumbs-down"></option>
                                <option data-icon="glyphicon glyphicon-hand-right" value="glyphicon glyphicon-hand-right"></option>
                                <option data-icon="glyphicon glyphicon-hand-left" value="glyphicon glyphicon-hand-left"></option>
                                <option data-icon="glyphicon glyphicon-hand-up" value="glyphicon glyphicon-hand-up"></option>
                                <option data-icon="glyphicon glyphicon-hand-down" value="glyphicon glyphicon-hand-down"></option>
                                <option data-icon="glyphicon glyphicon-circle-arrow-right" value="glyphicon glyphicon-circle-arrow-right"></option>
                                <option data-icon="glyphicon glyphicon-circle-arrow-left" value="glyphicon glyphicon-circle-arrow-left"></option>
                                <option data-icon="glyphicon glyphicon-circle-arrow-up" value="glyphicon glyphicon-circle-arrow-up"></option>
                                <option data-icon="glyphicon glyphicon-circle-arrow-down" value="glyphicon glyphicon-circle-arrow-down"></option>
                                <option data-icon="glyphicon glyphicon-globe" value="glyphicon glyphicon-globe"></option>
                                <option data-icon="glyphicon glyphicon-wrench" value="glyphicon glyphicon-wrench"></option>
                                <option data-icon="glyphicon glyphicon-tasks" value="glyphicon glyphicon-tasks"></option>
                                <option data-icon="glyphicon glyphicon-filter" value="glyphicon glyphicon-filter"></option>
                                <option data-icon="glyphicon glyphicon-briefcase" value="glyphicon glyphicon-briefcase"></option>
                                <option data-icon="glyphicon glyphicon-fullscreen" value="glyphicon glyphicon-fullscreen"></option>
                                <option data-icon="glyphicon glyphicon-dashboard" value="glyphicon glyphicon-dashboard"></option>
                                <option data-icon="glyphicon glyphicon-paperclip" value="glyphicon glyphicon-paperclip"></option>
                                <option data-icon="glyphicon glyphicon-heart-empty" value="glyphicon glyphicon-heart-empty"></option>
                                <option data-icon="glyphicon glyphicon-link" value="glyphicon glyphicon-link"></option>
                                <option data-icon="glyphicon glyphicon-phone" value="glyphicon glyphicon-phone"></option>
                                <option data-icon="glyphicon glyphicon-pushpin" value="glyphicon glyphicon-pushpin"></option>
                                <option data-icon="glyphicon glyphicon-usd" value="glyphicon glyphicon-usd"></option>
                                <option data-icon="glyphicon glyphicon-gbp" value="glyphicon glyphicon-gbp"></option>
                                <option data-icon="glyphicon glyphicon-sort" value="glyphicon glyphicon-sort"></option>
                                <option data-icon="glyphicon glyphicon-sort-by-alphabet" value="glyphicon glyphicon-sort-by-alphabet"></option>
                                <option data-icon="glyphicon glyphicon-sort-by-alphabet-alt" value="glyphicon glyphicon-sort-by-alphabet-alt"></option>
                                <option data-icon="glyphicon glyphicon-sort-by-order" value="glyphicon glyphicon-sort-by-order"></option>
                                <option data-icon="glyphicon glyphicon-sort-by-order-alt" value="glyphicon glyphicon-sort-by-order-alt"></option>
                                <option data-icon="glyphicon glyphicon-sort-by-attributes" value="glyphicon glyphicon-sort-by-attributes"></option>
                                <option data-icon="glyphicon glyphicon-sort-by-attributes-alt" value="glyphicon glyphicon-sort-by-attributes-alt"></option>
                                <option data-icon="glyphicon glyphicon-unchecked" value="glyphicon glyphicon-unchecked"></option>
                                <option data-icon="glyphicon glyphicon-expand" value="glyphicon glyphicon-expand"></option>
                                <option data-icon="glyphicon glyphicon-collapse-down" value="glyphicon glyphicon-collapse-down"></option>
                                <option data-icon="glyphicon glyphicon-collapse-up" value="glyphicon glyphicon-collapse-up"></option>
                                <option data-icon="glyphicon glyphicon-log-in" value="glyphicon glyphicon-log-in"></option>
                                <option data-icon="glyphicon glyphicon-flash" value="glyphicon glyphicon-flash"></option>
                                <option data-icon="glyphicon glyphicon-log-out" value="glyphicon glyphicon-log-out"></option>
                                <option data-icon="glyphicon glyphicon-new-window" value="glyphicon glyphicon-new-window"></option>
                                <option data-icon="glyphicon glyphicon-record" value="glyphicon glyphicon-record"></option>
                                <option data-icon="glyphicon glyphicon-save" value="glyphicon glyphicon-save"></option>
                                <option data-icon="glyphicon glyphicon-open" value="glyphicon glyphicon-open"></option>
                                <option data-icon="glyphicon glyphicon-saved" value="glyphicon glyphicon-saved"></option>
                                <option data-icon="glyphicon glyphicon-import" value="glyphicon glyphicon-import"></option>
                                <option data-icon="glyphicon glyphicon-export" value="glyphicon glyphicon-export"></option>
                                <option data-icon="glyphicon glyphicon-send" value="glyphicon glyphicon-send"></option>
                                <option data-icon="glyphicon glyphicon-floppy-disk" value="glyphicon glyphicon-floppy-disk"></option>
                                <option data-icon="glyphicon glyphicon-floppy-saved" value="glyphicon glyphicon-floppy-saved"></option>
                                <option data-icon="glyphicon glyphicon-floppy-remove" value="glyphicon glyphicon-floppy-remove"></option>
                                <option data-icon="glyphicon glyphicon-floppy-save" value="glyphicon glyphicon-floppy-save"></option>
                                <option data-icon="glyphicon glyphicon-floppy-open" value="glyphicon glyphicon-floppy-open"></option>
                                <option data-icon="glyphicon glyphicon-credit-card" value="glyphicon glyphicon-credit-card"></option>
                                <option data-icon="glyphicon glyphicon-transfer" value="glyphicon glyphicon-transfer"></option>
                                <option data-icon="glyphicon glyphicon-cutlery" value="glyphicon glyphicon-cutlery"></option>
                                <option data-icon="glyphicon glyphicon-header" value="glyphicon glyphicon-header"></option>
                                <option data-icon="glyphicon glyphicon-compressed" value="glyphicon glyphicon-compressed"></option>
                                <option data-icon="glyphicon glyphicon-earphone" value="glyphicon glyphicon-earphone"></option>
                                <option data-icon="glyphicon glyphicon-phone-alt" value="glyphicon glyphicon-phone-alt"></option>
                                <option data-icon="glyphicon glyphicon-tower" value="glyphicon glyphicon-tower"></option>
                                <option data-icon="glyphicon glyphicon-stats" value="glyphicon glyphicon-stats"></option>
                                <option data-icon="glyphicon glyphicon-sd-video" value="glyphicon glyphicon-sd-video"></option>
                                <option data-icon="glyphicon glyphicon-hd-video" value="glyphicon glyphicon-hd-video"></option>
                                <option data-icon="glyphicon glyphicon-subtitles" value="glyphicon glyphicon-subtitles"></option>
                                <option data-icon="glyphicon glyphicon-sound-stereo" value="glyphicon glyphicon-sound-stereo"></option>
                                <option data-icon="glyphicon glyphicon-sound-dolby" value="glyphicon glyphicon-sound-dolby"></option>
                                <option data-icon="glyphicon glyphicon-sound-5-1" value="glyphicon glyphicon-sound-5-1"></option>
                                <option data-icon="glyphicon glyphicon-sound-6-1" value="glyphicon glyphicon-sound-6-1"></option>
                                <option data-icon="glyphicon glyphicon-sound-7-1" value="glyphicon glyphicon-sound-7-1"></option>
                                <option data-icon="glyphicon glyphicon-copyright-mark" value="glyphicon glyphicon-copyright-mark"></option>
                                <option data-icon="glyphicon glyphicon-registration-mark" value="glyphicon glyphicon-registration-mark"></option>
                                <option data-icon="glyphicon glyphicon-cloud-download" value="glyphicon glyphicon-cloud-download"></option>
                                <option data-icon="glyphicon glyphicon-cloud-upload" value="glyphicon glyphicon-cloud-upload"></option>
                                <option data-icon="glyphicon glyphicon-tree-conifer" value="glyphicon glyphicon-tree-conifer"></option>
                                <option data-icon="glyphicon glyphicon-tree-deciduous" value="glyphicon glyphicon-tree-deciduous"></option>
                                <option data-icon="glyphicon glyphicon-cd" value="glyphicon glyphicon-cd"></option>
                                <option data-icon="glyphicon glyphicon-save-file" value="glyphicon glyphicon-save-file"></option>
                                <option data-icon="glyphicon glyphicon-open-file" value="glyphicon glyphicon-open-file"></option>
                                <option data-icon="glyphicon glyphicon-level-up" value="glyphicon glyphicon-level-up"></option>
                                <option data-icon="glyphicon glyphicon-copy" value="glyphicon glyphicon-copy"></option>
                                <option data-icon="glyphicon glyphicon-paste" value="glyphicon glyphicon-paste"></option>
                                <option data-icon="glyphicon glyphicon-alert" value="glyphicon glyphicon-alert"></option>
                                <option data-icon="glyphicon glyphicon-equalizer" value="glyphicon glyphicon-equalizer"></option>
                                <option data-icon="glyphicon glyphicon-king" value="glyphicon glyphicon-king"></option>
                                <option data-icon="glyphicon glyphicon-queen" value="glyphicon glyphicon-queen"></option>
                                <option data-icon="glyphicon glyphicon-pawn" value="glyphicon glyphicon-pawn"></option>
                                <option data-icon="glyphicon glyphicon-bishop" value="glyphicon glyphicon-bishop"></option>
                                <option data-icon="glyphicon glyphicon-knight" value="glyphicon glyphicon-knight"></option>
                                <option data-icon="glyphicon glyphicon-baby-formula" value="glyphicon glyphicon-baby-formula"></option>
                                <option data-icon="glyphicon glyphicon-tent" value="glyphicon glyphicon-tent"></option>
                                <option data-icon="glyphicon glyphicon-blackboard" value="glyphicon glyphicon-blackboard"></option>
                                <option data-icon="glyphicon glyphicon-bed" value="glyphicon glyphicon-bed"></option>
                                <option data-icon="glyphicon glyphicon-apple" value="glyphicon glyphicon-apple"></option>
                                <option data-icon="glyphicon glyphicon-erase" value="glyphicon glyphicon-erase"></option>
                                <option data-icon="glyphicon glyphicon-hourglass" value="glyphicon glyphicon-hourglass"></option>
                                <option data-icon="glyphicon glyphicon-lamp" value="glyphicon glyphicon-lamp"></option>
                                <option data-icon="glyphicon glyphicon-duplicate" value="glyphicon glyphicon-duplicate"></option>
                                <option data-icon="glyphicon glyphicon-piggy-bank" value="glyphicon glyphicon-piggy-bank"></option>
                                <option data-icon="glyphicon glyphicon-scissors" value="glyphicon glyphicon-scissors"></option>
                                <option data-icon="glyphicon glyphicon-bitcoin" value="glyphicon glyphicon-bitcoin"></option>
                                <option data-icon="glyphicon glyphicon-yen" value="glyphicon glyphicon-yen"></option>
                                <option data-icon="glyphicon glyphicon-ruble" value="glyphicon glyphicon-ruble"></option>
                                <option data-icon="glyphicon glyphicon-scale" value="glyphicon glyphicon-scale"></option>
                                <option data-icon="glyphicon glyphicon-ice-lolly" value="glyphicon glyphicon-ice-lolly"></option>
                                <option data-icon="glyphicon glyphicon-ice-lolly-tasted" value="glyphicon glyphicon-ice-lolly-tasted"></option>
                                <option data-icon="glyphicon glyphicon-education" value="glyphicon glyphicon-education"></option>
                                <option data-icon="glyphicon glyphicon-option-horizontal" value="glyphicon glyphicon-option-horizontal"></option>
                                <option data-icon="glyphicon glyphicon-option-vertical" value="glyphicon glyphicon-option-vertical"></option>
                                <option data-icon="glyphicon glyphicon-menu-hamburger" value="glyphicon glyphicon-menu-hamburger"></option>
                                <option data-icon="glyphicon glyphicon-modal-window" value="glyphicon glyphicon-modal-window"></option>
                                <option data-icon="glyphicon glyphicon-oil" value="glyphicon glyphicon-oil"></option>
                                <option data-icon="glyphicon glyphicon-grain" value="glyphicon glyphicon-grain"></option>
                                <option data-icon="glyphicon glyphicon-sunglasses" value="glyphicon glyphicon-sunglasses"></option>
                                <option data-icon="glyphicon glyphicon-text-size" value="glyphicon glyphicon-text-size"></option>
                                <option data-icon="glyphicon glyphicon-text-color" value="glyphicon glyphicon-text-color"></option>
                                <option data-icon="glyphicon glyphicon-text-background" value="glyphicon glyphicon-text-background"></option>
                                <option data-icon="glyphicon glyphicon-object-align-top" value="glyphicon glyphicon-object-align-top"></option>
                                <option data-icon="glyphicon glyphicon-object-align-bottom" value="glyphicon glyphicon-object-align-bottom"></option>
                                <option data-icon="glyphicon glyphicon-object-align-horizontal" value="glyphicon glyphicon-object-align-horizontal"></option>
                                <option data-icon="glyphicon glyphicon-object-align-left" value="glyphicon glyphicon-object-align-left"></option>
                                <option data-icon="glyphicon glyphicon-object-align-vertical" value="glyphicon glyphicon-object-align-vertical"></option>
                                <option data-icon="glyphicon glyphicon-object-align-right" value="glyphicon glyphicon-object-align-right"></option>
                                <option data-icon="glyphicon glyphicon-triangle-right" value="glyphicon glyphicon-triangle-right"></option>
                                <option data-icon="glyphicon glyphicon-triangle-left" value="glyphicon glyphicon-triangle-left"></option>
                                <option data-icon="glyphicon glyphicon-triangle-bottom" value="glyphicon glyphicon-triangle-bottom"></option>
                                <option data-icon="glyphicon glyphicon-triangle-top" value="glyphicon glyphicon-triangle-top"></option>
                                <option data-icon="glyphicon glyphicon-superscript" value="glyphicon glyphicon-superscript"></option>
                                <option data-icon="glyphicon glyphicon-subscript" value="glyphicon glyphicon-subscript"></option>
                                <option data-icon="glyphicon glyphicon-menu-left" value="glyphicon glyphicon-menu-left"></option>
                                <option data-icon="glyphicon glyphicon-menu-right" value="glyphicon glyphicon-menu-right"></option>
                                <option data-icon="glyphicon glyphicon-menu-down" value="glyphicon glyphicon-menu-down"></option>
                                <option data-icon="glyphicon glyphicon-menu-up" value="glyphicon glyphicon-menu-up"></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('Link') !!}
                            {!! Form::text('link', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('Cor') !!}
                            <input class="form-control" name="color" type="color">
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('Tamanho') !!}
                            {!! Form::select('size', [ 
                                1 => 1,
                                2 => 2,
                                3 => 3,
                                4 => 4,
                                5 => 5,
                                6 => 6,
                                7 => 7,
                                8 => 8,
                                9 => 9,
                                10 => 10,
                                11 => 11,
                                12 => 12
                            ], 3, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('Script SQL') !!}
                            {!! Form::textarea('query', null, ['class' => 'form-control', 'rows' => 2, 'placeholder'=>'Ex: SELECT count(*) FROM users;', 'required'=> 'required']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('Descrição') !!}
                            {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2]) !!}
                        </div>
                    </div>
                </div>
            </div>

            @if(\App\Models\Permissions::profileAdmin(\Auth::user()))
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">Para quem essa informação ficará disponível? Selecione um usuário. </label>
                            <select class="form-control" name="r_auth">
                                <option value="0">Disponível para todos</option>
                                <?php  foreach (\App\Models\User::get() as $key => $value) {  ?>
                                    <option value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            <br>
            <br>

            <div class="form-group">
                <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                    <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar">
                        <span class="glyphicon glyphicon-plus"></span> Cadastrar
                    </button>
                @endif
            </div>

        </div>

    {!! Form::close() !!}

</div>

</section>

@endsection