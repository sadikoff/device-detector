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
 * Class Camera.
 *
 * Device parser for camera detection
 */
class Camera extends DeviceParserAbstract
{
    protected $fixtureFile = 'regexes/device/cameras.yml';
    protected $parserName = 'camera';

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
