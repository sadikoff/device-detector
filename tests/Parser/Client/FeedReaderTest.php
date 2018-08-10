<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace DeviceDetector\Tests\Parser\Client;

use DeviceDetector\Parser\Client\FeedReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class FeedReaderTest extends TestCase
{
    /**
     * @dataProvider getFixtures
     */
    public function testParse($useragent, $client)
    {
        $feedReaderParser = new FeedReader();
        $feedReaderParser->setUserAgent($useragent);
        $this->assertEquals($client, $feedReaderParser->parse());
    }

    public function getFixtures()
    {
        $fixtureData = Yaml::parseFile(realpath(dirname(__FILE__)) . '/fixtures/feed_reader.yml');
        return $fixtureData;
    }
}
