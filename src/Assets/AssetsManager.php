<?php

namespace OffbeatWP\Assets;

use OffbeatWP\Content\Common\Singleton;
use RuntimeException;

final class AssetsManager extends Singleton
{
    /** @var mixed[] */
    private ?array $entrypoints = null;

    /**
     * @return mixed[]
     * @throws \JsonException
     * @throws \RuntimeException
     */
    private function getAssetsEntryPoints(): array
    {
        if ($this->entrypoints === null) {
            $path = $this->getAssetsPath('entrypoints.json', true);

            if (file_exists($path)) {
                $fileContents = file_get_contents($path);
                if (!is_string($fileContents)) {
                    throw new RuntimeException('Unable to read entry points file.');
                }

                $data = json_decode($fileContents, true, 8, JSON_THROW_ON_ERROR);
                if (!is_array($data) || !array_key_exists('entrypoints', $data) || !is_array($data['entrypoints'])) {
                    throw new RuntimeException('Asset entrypoints should be decoded into an array, but was decoded as: ' . gettype($data));
                }

                $entrypoints = $data['entrypoints'];
            } else {
                trigger_error('The entrypoints.json file could not be found!', E_USER_WARNING);
                $entrypoints = [];
            }

            $this->entrypoints = $entrypoints;
        }

        return $this->entrypoints;
    }

    /**
     * @return mixed[]
     * @throws \JsonException
     * @throws \RuntimeException
     */
    private function getAssetsByEntryPoint(string $entry, string $key): array
    {
        $entrypoints = $this->getAssetsEntryPoints();

        if (empty($entrypoints[$entry]) || !is_array($entrypoints[$entry])) {
            trigger_error('Entry ' . $entry . ' -> ' . $key . ' could not be found in entrypoints.', E_USER_WARNING);
            return [];
        }

        if (empty($entrypoints[$entry][$key]) || !is_array($entrypoints[$entry][$key])) {
            trigger_error('Entry key ' . $entry . ' -> ' . $key . ' could not be found in entrypoints.', E_USER_WARNING);
            return [];
        }

        return $entrypoints[$entry][$key];
    }

    public function getAssetsPath(string $path = '', bool $forceAssetsPath = false): string
    {
        $path = ltrim($path, '/');
        $path = ($path) ? "/{$path}" : '';

        $assetsPath = config('app.assets.path');
        if (!is_string($assetsPath)) {
            $assetsPath = '';
        }

        if ($assetsPath || $forceAssetsPath) {
            return $assetsPath . $path;
        }

        return get_template_directory() . '/assets' . $path;
    }

    public function getAssetsUrl(string $path = ''): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $path = ltrim($path, '/');
        $path = ($path) ? "/{$path}" : '';

        if (config('app.assets.from_site_root')) {
            $url = get_site_url();

            return $url . $path;
        }


        $url = config('app.assets.url');
        if ($url && is_string($url)) {
            return $url . $path;
        }

        return get_template_directory_uri() . '/assets' . $path;
    }

    /**
     * @param list<non-falsy-string> $dependencies
     * @throws \JsonException
     * @throws \RuntimeException
     */
    public function enqueueStyles(string $entry, array $dependencies = []): void
    {
        foreach ($this->getAssetsByEntryPoint($entry, 'css') as $asset) {
            $data = $this->generateHandleUrl($asset);
            wp_enqueue_style($data['handle'], $data['url'], $dependencies);
        }
    }

    /**
     * @param array<int, string> $dependencies
     * @param array{in_footer?: bool, strategy?: 'defer'|'async'} $args
     * @return \OffbeatWP\Assets\WpScriptAsset
     * @throws \JsonException
     * @throws \RuntimeException
     */
    public function enqueueScripts(string $entry, array $dependencies = [], array $args = ['in_footer' => true]): WpScriptAsset
    {
        $handle = '';

        foreach ($this->getAssetsByEntryPoint($entry, 'js') as $asset) {
            $data = $this->generateHandleUrl($asset);
            $handle = $data['handle'];
            wp_enqueue_script($data['handle'], $data['url'], $dependencies, false, $args);
        }

        return new WpScriptAsset($handle);
    }

    /** @return array{handle: non-falsy-string, url: string} */
    private function generateHandleUrl(string $asset): array
    {
        $url = $this->getAssetsUrl($asset);

        $baseName = basename(ltrim($asset, './'));
        $pos = strpos($baseName, '.');
        $handle = substr($baseName, 0, is_int($pos) ? $pos : null);

        return [
            'handle' => 'owp-' . $handle,
            'url' => $url
        ];
    }
}
