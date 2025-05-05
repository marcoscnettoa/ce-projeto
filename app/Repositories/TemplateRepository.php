<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;

use PDF;
use Log;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;
use Illuminate\Support\Facades\File;
use Exception;

class TemplateRepository
{
    public static function parseTemplate($p)
    {
        // ! Verifica se existe o diretório -| template
        $dir_template = public_path('template');
        if (!File::exists($dir_template)) {
            File::makeDirectory($dir_template, 0755, true, true);
        }

        $model = current($p);

        extract($p);

    	$countFiles = count(glob(public_path("template/*")));

        for ($i=0; $i <= $countFiles; $i++) {

            if (file_exists(public_path("template/$i.pdf"))) {
                \File::delete(public_path("template/$i.pdf"));
                \File::delete(public_path("template/999.pdf"));
            }

            if (file_exists(public_path("template/c$i.pdf"))) {
                \File::delete(public_path("template/c$i.pdf"));
            }
        }

        $template = $model->Template->template;

        $lines = explode("\n", $template);

        $contador = 0;

        $parser = [];

        foreach ($lines as $k => $line) {

            if (($start = strpos($line, '[[IMG ', 0)) !== FALSE) {

                $widthtHeight = strpos($line, ']]', $start);

                $widthtHeight = substr($line, $start+6, $widthtHeight - ($start+6));

                $widthtHeight = explode(":", $widthtHeight);

                $widtht = $widthtHeight[0];
                $height = $widthtHeight[1];

                $end = strpos($line, '[[/IMG]]');

                $key = substr($line, $start+15, $end-$start-15);

                $key = html_entity_decode($key);

                $key = trim($key);

                $key = \Blade::compileString($key);

                $key = html_entity_decode($key);

                ob_start();

                eval("?> $key <?php ");

                $key = ob_get_contents();

                ob_end_clean();

                $key = trim($key);

                if($key && strpos($key, '.') !== false) {
                    $img = '<img src="https://s3.xxxxrxxx.com.br/files/' . env('FILEKEY') . '/images/' . $key . '" width="'.$widtht.'" height="'.$height.'">';
                }
                else
                {
                    $img = '';
                }

                $lines[$k] = substr_replace($line, $img, $start, $end-$start+8);

            }

        }

        foreach ($lines as $k => $line) {

            if ((($start = strpos($line, '[[PDF]]', 0)) !== FALSE)) {

                if (!empty($parser)) {

                    self::gerarPDF($parser, $p, ++$contador);

                    $parser = [];

                }

                $end = strpos($line, '[[/PDF]]', $start);

                $key = substr($line, $start+9, $end-$start-11);

                $lines[$k] = '';

                $key = html_entity_decode($key);

                ob_start();

                eval("echo $key;");

                $key = ob_get_contents();

                $key = trim($key);

                $line = str_replace($key, '', $line);

                if (strpos(strtolower($key), '.pdf') !== false) {

                    $pdf = 'https://s3.xxxxrxxx.com.br/files/' . env('FILEKEY') . '/images/' . $key;

                    $contents = file_get_contents($pdf);

                    file_put_contents(base_path('/public/template/' . ++$contador . '.pdf'), $contents);
                }

                ob_end_clean();
            }
            else
            {
                $parser[$k] = $line;
            }

            if (strpos(strtolower($line), 'rm ') !== false) {
                echo 'IN001';
                exit;
            }

            if (strpos(strtolower($line), 'cd ') !== false) {
                echo 'IN002';
                exit;
            }

            if (strpos(strtolower($line), 'grep ') !== false) {
                echo 'IN003';
                exit;
            }

            if (strpos(strtolower($line), 'artisan ') !== false) {
                echo 'IN004';
                exit;
            }

            if (strpos(strtolower($line), 'find ') !== false) {
                echo 'IN004';
                exit;
            }

        }

        self::gerarPDF($parser, $p, ++$contador);

        $countFiles = count(glob(public_path("template/*")));

        $pdf = new FPDI();

        for ($i=1; $i <= $countFiles; $i++) {

            Log::info($i);

            if (file_exists(public_path("template/$i.pdf"))) {

                try {

                    $pageCount = $pdf->setSourceFile(public_path("template/$i.pdf"));

                } catch (Exception $e) {

                    exec("gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . public_path("template/c$i.pdf") . ' ' . public_path("template/$i.pdf"));

                    $pageCount = $pdf->setSourceFile(public_path("template/c$i.pdf"));
                }

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $pageId = $pdf->ImportPage($pageNo);
                    $s = $pdf->getTemplatesize($pageId);
                    $pdf->AddPage($s['orientation'], $s);
                    $pdf->useImportedPage($pageId);
                }

            }
        }

        $base64 = base64_encode('Gerado por:' . \Auth::user()->name . ' em: ' . date('d/m/Y H:i'));

        $pdf->SetTitle($model->Template->titulo);

        $pdf->Output(public_path("template/".$base64.".pdf"), 'F');

        return '/template/' . $base64 . '.pdf';
    }

    // # -
    public static function gerarPDF($lines, $p, $n)
    {
        extract($p);
        $template = implode("\n", $lines);

        // # -
        if(strpos($template, '[[LIB::RxxxAPPS]]') !== false){
            $template = str_replace("[[LIB::RxxxAPPS]]", "", $template);
            $template = str_replace("[[break]]", "<div style='page-break-after: always;'></div>", $template);
            $template = str_replace(['<!-- [CSS]','[CSS] -->'],"",$template);
            $NO_CODE  = ['eval', 'exec', 'system', 'shell_exec', 'passthru', 'file_get_contents', 'file_put_contents', 'fopen', 'fclose', 'fwrite', 'unlink', 'rmdir', 'dirname', 'basename', 'curl', 'phpinfo', 'assert', 'extract', 'include', 'include_once', 'require', 'require_once', 'unserialize', 'popen', 'proc_open', 'pcntl_exec', 'fsockopen', 'socket_create', 'create_function',];
            $patterns = [];
            foreach($NO_CODE as $C) {  $patterns[] = '/\b'.preg_quote($C).'\s*\(/i'; }
            $template = preg_replace($patterns, '_****_', $template);
            $template = html_entity_decode($template);
            $template = \Blade::compileString($template);
            ob_start();
            eval("?> $template <?php ");
            $template = ob_get_contents();
            ob_end_clean();
            $template = "<style type='text/css'>
                                @font-face { font-family: 'calibri'; src: url('".storage_path('/fonts/calibri.ttf')."'); font-weight: normal; }
                                @font-face { font-family: 'calibri'; src: url('".storage_path('/fonts/calibrib.ttf')."'); font-weight: bold; }
                                @font-face { font-family: 'calibri'; src: url('".storage_path('/fonts/calibrii.ttf')."'); font-weight: normal; font-style:italic; }
                         </style>".$template;
            PDF::loadHTML($template)->save(public_path('template/'.$n.'.pdf'));
        }else {
            $template = \Blade::compileString($template);
            $template = str_replace("[[break]]", "<div style='page-break-after: always;'></div>", $template);
            $template = str_replace(['<!-- [CSS]','[CSS] -->'],"",$template);
            // # -
            $NO_CODE  = ['eval', 'exec', 'system', 'shell_exec', 'passthru', 'file_get_contents', 'file_put_contents', 'fopen', 'fclose', 'fwrite', 'unlink', 'rmdir', 'dirname', 'basename', 'curl', 'phpinfo', 'assert', 'extract', 'include', 'include_once', 'require', 'require_once', 'unserialize', 'popen', 'proc_open', 'pcntl_exec', 'fsockopen', 'socket_create', 'create_function',];
            $patterns = [];
            foreach($NO_CODE as $C) {  $patterns[] = '/\b'.preg_quote($C).'\s*\(/i'; }
            $template = preg_replace($patterns, '_****_', $template);
            // -
            $template = html_entity_decode($template);
            ob_start();
            eval("?> $template <?php ");
            $template = ob_get_contents();
            ob_end_clean();
            $template = "<style type='text/css'>
                                @font-face { font-family: 'calibri'; src: url('".storage_path('/fonts/calibri.ttf')."'); font-weight: normal; }
                                @font-face { font-family: 'calibri'; src: url('".storage_path('/fonts/calibrib.ttf')."'); font-weight: bold; }
                                @font-face { font-family: 'calibri'; src: url('".storage_path('/fonts/calibrii.ttf')."'); font-weight: normal; font-style:italic; }
                         </style>".$template;
            PDF::loadHTML($template)->save(public_path('template/'.$n.'.pdf'));
        }

    }
}
