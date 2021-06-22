<?php

namespace OffbeatWP\Contracts;

use OffbeatWP\Form\Form;

interface ISettingsPage
{
    public function title(): string;
    public function form(): Form;
}