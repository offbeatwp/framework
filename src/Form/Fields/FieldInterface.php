<?php
namespace OffbeatWP\Form\Fields;

interface FieldInterface {
    public function getAttribute(string $key);
    public function getLabel(): string;
    public function getId(): string;
}