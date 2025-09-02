<?php

namespace OffbeatWP\Assets;

use RuntimeException;

final class AssetsManager
{
    /** @var array<string, array{js?: list<string>, css?: list<string>}>|null */
    private ?array $entrypoints = null;

    /**
     * @return array<string, array{js?: list<string>, css?: list<string>}>
     * @throws \JsonException
     * @throws \RuntimeException
     */
    private function getAssetsEntryPoints(): array
    {
        if ($this->entrypoints === null) {
            $path = $this->getAssetsPath('entrypoints.json', true);

            if (file_exists($path)) {
                $data = json_decode(file_get_contents($path), true, 8, JSON_THROW_ON_ERROR);
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
     * @return list<string>
     * @throws \JsonException
     * @throws \RuntimeException
     */
    private function getAssetsByEntryPoint(string $entry, string $key): array
    {
        $entrypoints = $this->getAssetsEntryPoints();

        if (empty($entrypoints[$entry][$key])) {
            trigger_error('Entry ' . $entry . ' -> ' . $key . ' could not be found in entrypoints.', E_USER_WARNING);
            return [];
        }

        return $entrypoints[$entry][$key];
    }

    public function getAssetsPath(string $path = '', bool $forceAssetsPath = false): string
    {
        $path = ltrim($path, '/');
        $path = ($path) ? "/{$path}" : '';

        $assetsPath = config('app.assets.path');

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
        if ($url) {
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
        $assets = $this->getAssetsByEntryPoint($entry, 'css');

        foreach ($assets as $asset) {
            $asset = ltrim($asset, './');
            $baseName = basename($asset);
            $handle = substr($baseName, 0, strpos($baseName, '.'));
            $handle = 'owp-' . $handle;

            if (!wp_style_is($handle)) {
                $url = $this->getAssetsUrl($asset);

                if (did_action('wp_enqueue_scripts') || in_array(current_action(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'enqueue_block_editor_assets', 'enqueue_block_assets', 'login_enqueue_scripts'])) {
                    wp_enqueue_style($handle, $url, $dependencies);
                } else {
                    add_action('wp_enqueue_scripts', function () use ($handle, $url, $dependencies) {
                        wp_enqueue_style($handle, $url, $dependencies);
                    });
                }
            }
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

        foreach ($assets as $asset) {
            $asset = ltrim($asset, './');
            $baseName = basename($asset);
            $handle = substr($baseName, 0, strpos($baseName, '.'));
            $handle = 'owp-' . $handle;

            if (!wp_script_is($handle)) {
                $enqueueNow = did_action('wp_enqueue_scripts') || in_array(current_action(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'enqueue_block_editor_assets', 'enqueue_block_assets', 'login_enqueue_scripts']);
                $url = $this->getAssetsUrl($asset);

                if ($enqueueNow) {
                    wp_enqueue_script($handle, $url, $dependencies, false, $args);
                } else {
                    add_action('wp_enqueue_scripts', function () use ($handle, $url, $dependencies, $args) {
                        wp_enqueue_script($handle, $url, $dependencies, false, $args);
                    });
                }
            }
        }

        return new WpScriptAsset($handle);
    }
}
