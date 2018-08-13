<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace DeviceDetector\Tests\Parser\Client;

use DeviceDetector\Parser\Client\Library;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class ToolTest extends TestCase
{
    /**
     * @dataProvider getFixtures
     */
    public function testParse($useragent, $client)
    {
        $LibraryParser = new Library();
        $LibraryParser->setUserAgent($useragent);
        $this->assertEquals($client, $LibraryParser->parse());
    }

    public function getFixtures()
    {
        $fixtureData = Yaml::parseFile(realpath(dirname(__FILE__)) . '/fixtures/library.yml');
        return $fixtureData;
    }
}