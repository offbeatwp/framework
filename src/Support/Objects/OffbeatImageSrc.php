<?php

namespace OffbeatWP\Support\Objects;

class OffbeatImageSrc
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
}