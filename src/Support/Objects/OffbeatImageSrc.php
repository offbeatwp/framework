<?php

namespace OffbeatWP\Support\Objects;

use ArrayIterator;
use IteratorAggregate;

final class OffbeatImageSrc implements IteratorAggregate
{
    private $src;
    private $width;
    private $height;
    private $resized;

    public function __construct(array $imgData)
    {
        $this->src = $imgData[0];
        $this->width = $imgData[1];
        $this->height = $imgData[2];
        $this->resized = $imgData[3];
    }

    public function getSrc(): string
    {
        return $this->src;
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

    public function getIterator()
    {
        return new ArrayIterator($this);
    }
}