<?php

namespace OffbeatWP\Assets;

final class AssetsManager
{
    /** @var mixed[]|null */
    public ?array $manifest = null;
    /** @var mixed[]|null */
    public ?array $entrypoints = null;

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

    public function getPath(string $filename): ?string
    {
        $path = $this->getEntryFromAssetsManifest($filename);

        if ($path !== null) {
            return $this->getAssetsPath($path);
        }

        trigger_error('Could not retrieve path from asset manifest: ' . $filename, E_USER_WARNING);
        return null;
    }

    public function getEntryFromAssetsManifest(string $filename): ?string
    {
        return $this->getAssetsManifest()[$filename] ?? null;
    }

    /** @return mixed[] */
    public function getAssetsManifest(): array
    {
        if ($this->manifest === null) {
            $path = $this->getAssetsPath('manifest.json', true);

            if (file_exists($path)) {
                $manifest = json_decode(file_get_contents($path), true);

                if (is_array($manifest)) {
                    $this->manifest = $manifest;
                } else {
                    trigger_error('MANIFEST JSON ERROR - ' . json_last_error_msg(), E_USER_WARNING);
                    $this->manifest = [];
                }
            }
        }

        return $this->manifest;
    }

    /** @return mixed[] */
    public function getAssetsEntryPoints(): array
    {
        if ($this->entrypoints === null) {
            $path = $this->getAssetsPath('entrypoints.json', true);

            if (file_exists($path)) {
                $entrypoints = json_decode(file_get_contents($path), true);

                if (is_array($entrypoints)) {
                    $this->entrypoints = $entrypoints;
                } else {
                    trigger_error('ENTRYPOINT JSON ERROR - ' . json_last_error_msg(), E_USER_WARNING);
                    $this->entrypoints = [];
                }
            }
        }

        return $this->entrypoints;
    }

    /** @return mixed[] */
    public function getAssetsByEntryPoint(string $entry, string $key): array
    {
        $entrypoints = $this->getAssetsEntryPoints();

        if (empty($entrypoints['entrypoints'][$entry][$key])) {
            trigger_error('Entry ' . $entry . ' -> ' . $key . ' could not be found in entrypoints.', E_USER_WARNING);
            return [];
        }

        return $entrypoints['entrypoints'][$entry][$key];
    }

    public function getAssetsPath(string $path = '', bool $forceAssetsPath = false): string
    {
        $path = ltrim($path, '/');
        $path = ($path) ? "/{$path}" : '';

        $assetsPath = config('app.assets.path');

        if ($forceAssetsPath) {
            return $assetsPath . $path;
        }

        if ($assetsPath) {
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

        $url = config('app.assets.url');
        if ($url) {
            return $url . $path;
        }

        return get_template_directory_uri() . '/assets' . $path;
    }

    /** @param array<int, string> $dependencies */
    public function enqueueStyles(string $entry, array $dependencies = []): void
    {
        $assets = $this->getAssetsByEntryPoint($entry, 'css');

        if ($assets) {
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
        } else {
            $handle = 'theme-style' . $entry;
            $enqueueNow = did_action('wp_enqueue_scripts') || in_array(current_action(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'enqueue_block_editor_assets', 'enqueue_block_assets', 'login_enqueue_scripts']);
            $url = $this->getUrl($entry . '.css');

            if ($enqueueNow) {
                wp_enqueue_style($handle, $url, $dependencies);
            } else {
                add_action('wp_enqueue_scripts', function () use ($url, $dependencies, $handle) {
                    wp_enqueue_style($handle, $url, $dependencies);
                });
            }
        }
    }

    /**
     * @param array<int, string> $dependencies
     * @param array{in_footer?: bool, strategy?: 'defer'|'async'} $args
     */
    public function enqueueScripts(string $entry, array $dependencies = [], array $args = ['in_footer' => true]): WpScriptAsset
    {
        $assets = $this->getAssetsByEntryPoint($entry, 'js');
        $enqueueNow = false;
        $handle = '';

        if ($assets) {
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
        } else {
            $handle = 'theme-script-' . $entry;
            $enqueueNow = did_action('wp_enqueue_scripts') || in_array(current_action(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'enqueue_block_editor_assets', 'enqueue_block_assets', 'login_enqueue_scripts']);
            $url = $this->getUrl($entry . '.js');

            if ($enqueueNow) {
                wp_enqueue_script($handle, $url, $dependencies, false, $args);
            } else {
                add_action('wp_enqueue_scripts', function () use ($handle, $url, $dependencies, $args) {
                    wp_enqueue_script($handle, $url, $dependencies, false, $args);
                });
            }
        }

        return new WpScriptAsset($handle, $enqueueNow);
    }
}
