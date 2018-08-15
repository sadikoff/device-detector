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
 * Class PortableMediaPlayer.
 *
 * Device parser for portable media player detection
 */
class PortableMediaPlayer extends DeviceParserAbstract
{
    protected $fixtureFile = 'regexes/device/portable_media_player.yml';
    protected $parserName = 'portablemediaplayer';

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
