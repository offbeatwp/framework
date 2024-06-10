<?php
$finder = (new PhpCsFixer\Finder())->in(__DIR__)
    ->exclude(['node_modules', 'build', 'vendor']);

return (new PhpCsFixer\Config())->setFinder($finder);