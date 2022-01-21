<?php

namespace OffbeatWP\Support\Objects;

class OffbeatUploadBitsResult
{
    private $file;
    private $url;
    private $type;
    private $error;

    public function __construct(array $data)
    {
        $this->file = $data['file'];
        $this->url = $data['url'];
        $this->type = $data['type'];
        $this->error = $data['error'] ?: null;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}