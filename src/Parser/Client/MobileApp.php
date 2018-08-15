<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents.
 *
 * @see http://piwik.org
 *
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */

namespace Koff\DeviceDetector\Parser\Client;

/**
 * Class MobileApp.
 *
 * Client parser for mobile app detection
 */
class MobileApp extends ClientParserAbstract
{
    protected $fixtureFile = 'regexes/client/mobile_apps.yml';
    protected $parserName = 'mobile app';
}
