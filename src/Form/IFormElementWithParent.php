<?php

namespace OffbeatWP\Form;

interface IFormElementWithParent
{
    public function setParent($item);
    public function getParent();
}