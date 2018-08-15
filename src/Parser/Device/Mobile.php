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
 * Class Mobile.
 *
 * Device parser for mobile detection
 */
class Mobile extends DeviceParserAbstract
{
    protected $fixtureFile = 'regexes/device/mobiles.yml';
    protected $parserName = 'mobile';
}
