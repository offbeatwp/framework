<?php

namespace OffbeatWP\Tools\Polylang\Helpers;

class ViewHelpers {
    public function currentLang()
    {
        return pll_current_language();
    }

    public function __($string)
    {
        return pll__($string);
    }
}