<?php
class Config
{
    private static $config;

    // Indlæser config.json én gang
    public static function load($path = __DIR__ . '/../shared/config.json')
    {
        if (!self::$config) {
            if (!file_exists($path)) {
                die("Manglende config-fil: " . $path);
            }

            $json = file_get_contents($path);
            self::$config = json_decode($json, true);

            if (!self::$config) {
                die("Ugyldig JSON i config");
            }
        }
    }

    // Få en sti eller URL — valgfrit med ekstra fil
    public static function get($key, $append = '')
    {
        self::load(); // Loader hvis ikke allerede indlæst

        if (!isset(self::$config[$key])) {
            die("Manglende konfiguration for nøgle: $key");
        }

        return rtrim(self::$config[$key], '/') . '/' . ltrim($append, '/');
    }

    // Eksempel: få baseUrl eller fysisk filsti
    public static function baseUrl($append = '') {
        return self::get('baseUrl', $append);
    }

    public static function serverPath($append = '') {
        $root = $_SERVER['DOCUMENT_ROOT'];
        return rtrim($root, '/') . '/' . ltrim(self::get('rootFolder', $append), '/');
    }
}
