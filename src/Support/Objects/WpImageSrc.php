<?php

namespace OffbeatWP\Support\Objects;

use ArrayIterator;
use IteratorAggregate;

final class WpImageSrc implements IteratorAggregate
{
    private $url;
    private $width;
    private $height;
    private $resized;

    public function __construct(array $imgData)
    {
        $this->url = $imgData[0];
        $this->width = $imgData[1];
        $this->height = $imgData[2];
        $this->resized = $imgData[3];
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function isResized(): bool
    {
        return $this->resized;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this);
    }
}