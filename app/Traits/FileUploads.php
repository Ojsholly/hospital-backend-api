<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;

trait FileUploads
{
    public function uploadFile(array|UploadedFile $file, $path): array|string
    {
        $path = config('app.name').'/'.config('app.env')."/$path";

        /**
         * if the file is an array, we will loop through it and upload each file and return an array of uploaded files.
         * if the file is not an array, we will upload the file and return the uploaded file.
         */
        if (is_array($file)) {
            $uploadedFiles = [];

            collect($file)->each(function ($file) use ($path, &$uploadedFiles) {
                $uploadedFiles[] = $file->storeOnCloudinaryAs($path, $file->getFilename())->getSecurePath();
            });

            return $uploadedFiles;
        }

        return $file->storeOnCloudinaryAs($path, $file->getFilename())->getSecurePath();
    }
}
