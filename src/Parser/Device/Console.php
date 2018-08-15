<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents.
 *
 * @see http://piwik.org
 *
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */

namespace Koff\DeviceDetector\Parser\Device;

/**
 * Class Console.
 *
 * Device parser for console detection
 */
class Console extends DeviceParserAbstract
{
    protected $fixtureFile = 'regexes/device/consoles.yml';
    protected $parserName = 'console';

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function parse()
    {
        if (!$this->preMatchOverall()) {
            return false;
        }

        return parent::parse();
    }
}
