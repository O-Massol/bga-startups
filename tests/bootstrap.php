<?php

// Rules classes live in modules/php/Rules and must not depend on the BGA framework.
spl_autoload_register(function (string $class): void {
    $prefix = 'Bga\\Games\\startups\\Rules\\';
    if (str_starts_with($class, $prefix)) {
        $file = __DIR__ . '/../modules/php/Rules/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

// Stand-in for the framework's translation marker, so material.inc.php can be loaded.
if (!function_exists('clienttranslate')) {
    function clienttranslate(string $text): string
    {
        return $text;
    }
}
