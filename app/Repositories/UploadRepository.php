<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;
use Exception;

class UploadRepository
{
    public static function parseUpload($upload, $maxSize, $store, $request)
    {
    	$relacionamento = array();

    	try {

	    	if (!empty($store)) {

	            foreach ($store as $key => $value) {

	                if (is_array($store[$key]) && !empty($store[$key])) {

                        if ($key == 'grid') {
                            continue;
                        }

	                    $store[$key] = array_filter($store[$key]);

	                    sort($store[$key]);

	                    if (!$request->hasFile($key)) {
	                        $relacionamento[$key] = $store[$key];
	                    }
	                    else
	                    {
	                        $list = $store[$key];

	                        foreach ($list as $k => $file) {

	                            if (is_object($file)) {

	                                if (!in_array(strtolower($file->getClientOriginalExtension()), $upload)) {
	                                    return back()->withInput()->withErrors('Tipo de arquivo não permitido! Extensões permitidas: ' . implode(", ", $upload));
	                                }

	                                if ($file->getSize() > $maxSize) {
	                                    return back()->withInput()->withErrors('Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB');
	                                }

	                                $filename = base64_encode($file->getClientOriginalName()) . "-" . uniqid().".".$file->getClientOriginalExtension();

	                                if(env("FILESYSTEM_DRIVER") == "s3") {

	                                    Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $filename, file_get_contents($file));

	                                } else {

	                                    $file->move(public_path("images"), $filename);

	                                }

	                                $relacionamento[$key][] = $filename;
	                            }
	                            else
	                            {
	                                $relacionamento[$key][] = $file;
	                            }
	                        }
	                    }

	                    unset($store[$key]);
	                }
	            }

	        }

        } catch (Exception $e) {
    		\Logs::info($e->getMessage());
    	}

    	return [
    		'relacionamento' => $relacionamento,
    		'store' => $store
    	];
    }
}
