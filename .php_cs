<?php

if (!file_exists(__DIR__.'/src')) {
    exit(0);
}

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
;

return PhpCsFixer\Config::create()
    ->finder($finder)
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->setRules(array(
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'long'],
        'protected_to_private' => false,
        'semicolon_after_instruction' => false,
    ))
    ->setRiskyAllowed(true)
;
