<?php
$finder = (new PhpCsFixer\Finder())->in(__DIR__ . '/src');

return (new PhpCsFixer\Config())->setFinder($finder);