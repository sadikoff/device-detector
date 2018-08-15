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
 * Class CarBrowser.
 *
 * Device parser for car browser detection
 */
class CarBrowser extends DeviceParserAbstract
{
    protected $fixtureFile = 'regexes/device/car_browsers.yml';
    protected $parserName = 'car browser';

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
