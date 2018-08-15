<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents.
 *
 * @see http://piwik.org
 *
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */

namespace Koff\DeviceDetector\Parser;

/**
 * Class BotParserAbstract.
 *
 * Abstract class for all bot parsers
 */
abstract class BotParserAbstract extends ParserAbstract
{
    /**
     * Enables information discarding.
     */
    abstract public function discardDetails();
}
