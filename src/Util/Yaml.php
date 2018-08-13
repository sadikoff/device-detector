<?php

namespace DeviceDetector\Util;

use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * @author Sadicov Vladimir <sadikoff@gmail.com>
 */
final class Yaml
{
    /**
     * @param $filename
     *
     * @return mixed
     * @throws \Exception
     */
    public static function parseFile($filename)
    {
        if (is_file($filename) && is_readable($filename)) {
            $content = file_get_contents($filename);

            return SymfonyYaml::parse($content);
        }

        throw new \Exception(sprintf('File "%s" not found', $filename));
    }
}