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
 * Class FeedReader.
 *
 * Client parser for feed reader detection
 */
class FeedReader extends ClientParserAbstract
{
    protected $fixtureFile = 'regexes/client/feed_readers.yml';
    protected $parserName = 'feed reader';
}
