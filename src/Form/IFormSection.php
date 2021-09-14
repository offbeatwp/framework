<?php

namespace OffbeatWP\Form;

interface IFormSection
{
    public function getLabel(): string;
    public function getId(): string;
}