<?php

namespace OffbeatWP\Assets;

use InvalidArgumentException;
use OffbeatWP\Content\Common\Singleton;
use RuntimeException;

final class AssetsManager extends Singleton
{
    /** @var mixed[]|null */
    private ?array $manifest = null;
    /** @var mixed[]|null */
    private ?array $entrypoints = null;

    public function getUrl(string $filename): ?string
    {
        $path = $this->getEntryFromAssetsManifest($filename);

        if ($path !== null) {
            if (str_starts_with($path, 'http')) {
                return $path;
            }

            return $this->getAssetsUrl($path);
        }

        trigger_error('Could not retrieve url from asset manifest: ' . $filename, E_USER_WARNING);
        return null;
    }

    private function getEntryFromAssetsManifest(string $filename): ?string
    {
        return $this->getAssetsManifest()[$filename] ?? null;
    }

    /** @return mixed[] */
    private function getAssetsManifest(): array
    {
        if ($this->manifest === null) {
            $path = $this->getAssetsPath('manifest.json', true);

            if (file_exists($path)) {
                $fileContents = file_get_contents($path);
                if (!is_string($fileContents)) {
                    throw new RuntimeException('Unable to read entry points file.');
                }

                $manifest = json_decode($fileContents, true);
                if (!is_array($manifest)) {
                    throw new RuntimeException('Asset manifest should be decoded into an array, but was decoded as: ' . gettype($manifest));
                }
            } else {
                trigger_error('The manifest.json file could not be found!', E_USER_WARNING);
                $manifest = [];
            }

            $this->manifest = $manifest;
        }

        return $this->manifest;
    }

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

                $entrypoints = json_decode($fileContents, true, 8, JSON_THROW_ON_ERROR);

                if (!is_array($entrypoints)) {
                    throw new RuntimeException('Asset entrypoints should be decoded into an array, but was decoded as: ' . gettype($entrypoints));
                }
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
        /** @var string[] $assets */
        $assets = $this->getAssetsByEntryPoint($entry, 'css');

        foreach ($assets as $asset) {
            $asset = is_string($asset) ? ltrim($asset, './') : '';
            $handle = $this->generateHandle($asset);
            $url = $this->getAssetsUrl($asset);

            wp_enqueue_style($handle, $url, $dependencies);
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
        $assets = $this->getAssetsByEntryPoint($entry, 'js');
        $handle = '';

        /** @var string[] $assets */
        foreach ($assets as $asset) {
            $assetDetails = $this->getJsAssetInfo($asset);

            if (!$assetDetails) {
                throw new InvalidArgumentException("No asset info found for '{$asset}'");
            }

            /** @var array<int, string> $assetDependencies */
            $assetDependencies = $assetDetails['dependencies'] ?? [];

            $asset = is_string($asset) ? ltrim($asset, './') : '';
            $handle = $this->generateHandle($asset);

            $url = $this->getAssetsUrl($asset);
            
            wp_enqueue_script($handle, $url, array_merge($assetDependencies , $dependencies), $assetDetails['version'] ?? false, $args);
        }

        return new WpScriptAsset($handle);
    }

    /** @return mixed[]|null */
    public function getJsAssetInfo(string $asset): ?array {
        $count = 0;
        $assetFile = preg_replace('/.js$/', '.asset.php', $asset, -1, $count);
        
        if (!$count || !$assetFile) {
            return null;
        }

        $assetFile = $this->getAssetsPath($assetFile);
        
        return file_exists($assetFile) ? include $assetFile : null;
    }

    /** @return non-falsy-string */
    private function generateHandle(string $asset): string
    {
        $baseName = basename($asset);
        $pos = strpos($baseName, '.');
        $handle = substr($baseName, 0, is_int($pos) ? $pos : null);

        return 'owp-' . $handle;

    }
}
