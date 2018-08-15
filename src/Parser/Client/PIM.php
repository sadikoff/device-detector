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
 * Class PIM.
 *
 * Client parser for pim (personal information manager) detection
 */
class PIM extends ClientParserAbstract
{
    protected $fixtureFile = 'regexes/client/pim.yml';
    protected $parserName = 'pim';
}
