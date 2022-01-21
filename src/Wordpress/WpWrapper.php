<?php

namespace OffbeatWP\Wordpress;

use OffbeatWP\Support\Objects\OffbeatUploadBitsResult;

class WpWrapper
{
    /**
     * Create a file in the upload folder with given content.
     * If there is an error, then the key 'error' will exist with the error message. If success, then the key 'file' will have the unique file path, the 'url' key will have the link to the new file. and the 'error' key will be set to false.
     * This function will not move an uploaded file to the upload folder. It will create a new file with the content in $bits parameter. If you move the upload file, read the content of the uploaded file, and then you can give the filename and content to this function, which will add it to the upload folder.
     * The permissions will be set on the new file automatically by this function.
     * @param string $name Filename.
     * @param string $content File content.
     * @param string|null $time Optional. Time formatted in 'yyyy/mm'. Default <i>null</i>.
     * @return OffbeatUploadBitsResult Object containing information about the newly uploaded file.<br/>
     * If there is an error, then <b>getError()</b> will return the error message.<br/>
     * If success, then <b>getFile()</b> will return the unique file path, <b>getUrl()</b> will return the link to the new file and <b>getError()</b> will return null.
     */
    public static function uploadBits(string $name, string $content, ?string $time = null): OffbeatUploadBitsResult
    {
        return new OffbeatUploadBitsResult(wp_upload_bits($name, null, $content, $time));
    }
}