<?php

namespace Koff\DeviceDetector\Util;

use Koff\DeviceDetector\Exception\ResourceNotFoundException;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * @author Sadicov Vladimir <sadikoff@gmail.com>
 */
final class Yaml
{
    /**
     * @param $content
     *
     * @return mixed
     */
    public static function parse($content)
    {
        return SymfonyYaml::parse($content);
    }

    /**
     * @param $filename
     *
     * @return mixed
     *
     * @throws ResourceNotFoundException
     */
    public static function parseFile($filename)
    {
        if (is_file($filename) && is_readable($filename)) {
            $content = file_get_contents($filename);

            return SymfonyYaml::parse($content);
        }

        throw new ResourceNotFoundException(sprintf('File "%s" not found', $filename));
    }
}
