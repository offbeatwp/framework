<?php

namespace OffbeatWP\Content\Meta;

final class MetaChangeResult
{
    private bool $success;
    private string $key;
    private array $errors;

    /**
     * @param non-empty-string $key
     * @param string[] $errors
     */
    public function __construct(string $key, array $errors = [])
    {
        $this->key = $key;
        $this->errors = $errors;
        $this->success = !$errors;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    /** @return string[] */
    public function getErrors(): array
    {
        return $this->errors;
    }
}