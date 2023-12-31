<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Http\Request;

class ImageHelperController extends Controller
{

    /**
     * singleImageUploadFn =>
     *   NOTE  This is for templars file upload
     *   Single File Upload use for temporary
     *
     * @param  mixed $request
     *
     * @return void
     */
    public function singleImageUploadFn(Request $request)
    {
        $input = $request->all();

        $validation = $this->requiredValidation(['image'], $input);
        if (isset($validation) && $validation['flag'] == false) {
            return $this->sendBadRequest(null, $validation['message']);
        }

        /** move file to storage folder */
        $fileResponse = $this->moveFile($input['image'], 'uploaded/images/profiles');
        if (isset($fileResponse) && $fileResponse['flag'] == false) {
            return $this->sendBadRequest(null, $fileResponse['message']);
        }

        return $this->sendSuccessResponse($fileResponse['data'], $fileResponse['message']);
    }

    /**
     * moveFile => move file to given folder name
     *
     * @param  mixed $file => object
     * @param  mixed $moduleName => Folder name
     *
     * @return void
     */
    public function moveFile($file = null, $moduleName = 'images')
    {

        //     $image = str_replace('data:image/png;base64,', '', $file);
        // $image = str_replace(' ', '+', $image);
        // $fileName = str_random(10) . '.' . 'png';
         $fileName = $file->getClientOriginalName();

        /** FIXME make compress image file */
        if (isset($fileName)) {
            $fileName = uniqid() . '_' . $fileName;
        }
        $fileName = str_slug($this->removeExtension($fileName));

        $filePath = UPLOADED_FOLDER_NAME . 'images/' . $moduleName;
        if (!is_dir(storage_path() . $filePath)) {
            mkdir(storage_path() . $filePath, 755, true);
        }

        /** file move to storage path */
        if ($file->move(storage_path() . $filePath, $fileName . PNG_EXTENSION)) {
            $publicPath = "{$filePath}/{$fileName}";
            return $this->makeResponse(['image' => $publicPath], __("validation.common.file_success_upload"));
        } else {
            return $this->makeError([], __('validation.common.error_in_file_upload'));
        }
    }

    /**
     * removeExtension => remove file extension using array pop
     *
     * @param  mixed $fileName
     *
     * @return void
     */
    public function removeExtension($fileName)
    {
        $arrayFile = explode('.', $fileName);
        array_pop($arrayFile);
        return implode('.', $arrayFile);
    }

    /**
     * removeImageFromStorage => remove file from server
     *
     * @param  mixed $filename
     *
     * @return void
     */
    public function removeImageFromStorage($fileName = null)
    {
        // first remove base url from file name
        $fileName = str_replace(env('APP_URL', url('/')), '', $fileName);

        /** check file exists */
        if (file_exists(storage_path($fileName . PNG_EXTENSION))) {
            unlink(storage_path($fileName . PNG_EXTENSION));
        }
    }

    /**
     * fileMoveOnStorage => file upload in storage
     *
     * @param  mixed $fileUrl
     *
     * @return void
     */
    public function fileMoveOnStorage($fileUrl = null)
    {
        if (!isset($fileUrl)) {
            return $this->makeError([], __('validation.common.error_in_file_upload'));
        }

        /** check file exists */
        if (file_exists(public_path($fileUrl))) {
            $storageFle = $this->fileMoveToStorage($fileUrl);
            if (isset($storageFle) && $storageFle['flag'] == false) {
                return $this->makeError($storageFle['data'], $storageFle['message']);
            }
        }
    }

    /**
     * getFileNameFromPath => get file name
     *
     * @param  mixed $filePath
     *
     * @return void
     */
    public function getFileNameFromPath($filePath)
    {
        $arrayName = explode('/', $filePath);
        return end($arrayName);
    }

    /**
     *  Not working.
     */
    // public function fileMoveToStorage($fileUrl)
    // {
    //     $moduleName = 'profiles';
    //     $storageFilePath = '/uploaded/images/' . $moduleName;
    //     $newPath = $fileUrl;
    //     $oldPath =   $fileUrl;
    //     $fileName = $this->getFileNameFromPath($fileUrl);
    //     try {
    //         if (move_uploaded_file($oldPath, $newPath)) {
    //             return $this->makeResponse(['image' => $fileUrl], __("validation.common.file_success_upload"));
    //         } else {
    //             return $this->makeError([], __('validation.common.error_in_file_upload'));
    //         }
    //     } catch (\Exception $ex) {
    //         \Log::error($ex->getMessage());
    //     }
    // }
}
