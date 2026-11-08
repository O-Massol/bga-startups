<?php

// Rules, Repository and Service classes must not depend on the BGA framework.
spl_autoload_register(function (string $class): void {
    $map = [
        'Bga\\Games\\startups\\' => __DIR__ . '/../modules/php/',
        'Bga\\Games\\startups\\Tests\\Fakes\\' => __DIR__ . '/Fakes/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require_once $file;
                return;
            }
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
