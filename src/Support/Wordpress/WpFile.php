<?php

namespace OffbeatWP\Support\Wordpress;

use Exception;
use OffbeatWP\Exceptions\WpErrorException;

final class WpFile
{
    /** @var string */
    private $file;
    /** @var string */
    private $url;
    /** @var string */
    private $type;
    /** @var string|null */
    private $error;

    private function __construct(array $result)
    {
        $this->file = $result['file'];
        $this->url = $result['url'];
        $this->type = $result['type'];
        $this->error = ($result['error'] !== false) ? $result['error'] : null;
    }

    public function getFileName(): string
    {
        return $this->file;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getFileType(): string
    {
        return $this->type;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    /** @return int|null Returns the ID of the inserted attachment or <i>null</i> if the insert action failed. */
    public function insertAsAttachment(string $description = '', int $parentId = 0): ?int
    {
        // The update_attachment_metadata requires this file to be loaded outside of the wp-admin
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attachmentId = wp_insert_attachment([
            'guid' => $this->url,
            'post_mime_type' => $this->type,
            'post_title' => sanitize_file_name(basename($this->file)),
            'post_content' => $description
        ], $this->file, $parentId);

        if (!$attachmentId) {
            return null;
        }

        wp_update_attachment_metadata($attachmentId, wp_generate_attachment_metadata($attachmentId, $this->file));

        return $attachmentId;
    }
    /**
     * Create a file in the upload folder with given content.<br>
     * If there is an error, then the key 'error' will exist with the error message.<br>
     * If success, then the key 'file' will have the unique file path, the 'url' key will have the link to the new file. and the 'error' key will be set to <b>null</b>.<br>
     * This function will not move an uploaded file to the upload folder.<br>
     * It will create a new file with the content in $bits parameter.<br>
     * If you move the upload file, read the content of the uploaded file, and then you can give the filename and content to this function, which will add it to the upload folder.<br>
     * The permissions will be set on the new file automatically by this function.
     * @param string $filename Name of the file to upload.
     * @param string $fileContent Content of the file.
     * @param string|null $time Optional. Time formatted in <i>yyyy/mm</i>.
     * @return WpFile
     */
    public static function uploadBits(string $filename, string $fileContent, ?string $time = null): WpFile
    {
        return new WpFile(wp_upload_bits($filename, null, $fileContent, $time));
    }

    /** @param array{name: string, tmp_name: string} $fileArray Array that represents a `$_FILES` upload array. */
    public static function uploadFromArray(array $fileArray, int $postId = 0): WpFile
    {
        return self::uploadBits($fileArray['name'], file_get_contents($fileArray['tmp_name']));
    }

    /**
     * @param string $url
     * @param string[] $allowedContentTypes Allowed content MIME-types.
     * @return WpFile|null Returns a <i>WpFile</i> on success or <i>null</i> otherwise.
     * @throws Exception
     */
    public static function uploadFromUrl(string $url, array $allowedContentTypes): ?WpFile
    {
        $fileName = pathinfo($url)['filename'];

        if (!$fileName) {
            throw new Exception('Could not get remote filename.');
        }

        $headers = get_headers($url, 1);

        if (!isset($headers['Content-Type'])) {
            throw new Exception('Could not determine content type of file.');
        }

        if (in_array($headers['Content-Type'], $allowedContentTypes, true)) {
            return null;
        }

        $downloadUrl = download_url($url);

        if (is_wp_error($downloadUrl)) {
            throw new WpErrorException($downloadUrl->get_error_message());
        }

        return self::uploadBits($fileName, file_get_contents($downloadUrl));
    }
}