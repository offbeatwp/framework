<?php
namespace OffbeatWP\Assets;

class AssetsManager
{
    public $actions = [];
    public $manifest = null;

    public function getUrl($filename)
    {
        if ($this->getEntryFromAssetsManifest($filename) !== false) {
            return $this->getAssetsUrl($this->getEntryFromAssetsManifest($filename));
        }

        return false;
    }

    public function getPath($filename)
    {
        if ($this->getEntryFromAssetsManifest($filename) !== false) {
            return $this->getAssetsPath($this->getEntryFromAssetsManifest($filename));
        }

        return false;
    }

    public function getEntryFromAssetsManifest($filename)
    {
        if(isset($this->getAssetsManifest()->$filename)) {
            return $this->getAssetsManifest()->$filename;
        }

        return false;
    }

    public function getAssetsManifest() {
        if (is_null($this->manifest) && file_exists($this->getAssetsPath('manifest.json'))) {
            $this->manifest = json_decode(file_get_contents($this->getAssetsPath('manifest.json')));
        }

        return $this->manifest;
    }

    public function getAssetsPath($path = '')
    {
        return get_template_directory() . '/assets' . ( !empty($path) ? "/{$path}" : '' ); 
    }

    public function getAssetsUrl($path = '')
    {
        return get_template_directory_uri() . '/assets' . ( !empty($path) ? "/{$path}" : '' ); 
    }
}
