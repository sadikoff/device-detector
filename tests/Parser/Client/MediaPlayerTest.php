<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace DeviceDetector\Tests\Parser\Client;

use DeviceDetector\Parser\Client\MediaPlayer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class MediaPlayerTest extends TestCase
{
    /**
     * @dataProvider getFixtures
     */
    public function testParse($useragent, $client)
    {
        $mediaPlayerParser = new MediaPlayer();
        $mediaPlayerParser->setUserAgent($useragent);
        $this->assertEquals($client, $mediaPlayerParser->parse());
    }

    public function getFixtures()
    {
        $fixtureData = Yaml::parseFile(realpath(dirname(__FILE__)) . '/fixtures/mediaplayer.yml');
        return $fixtureData;
    }
}
