<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace Koff\DeviceDetector\Tests\Parser\Client;

use Koff\DeviceDetector\Parser\Client\Library;
use PHPUnit\Framework\TestCase;
use Koff\DeviceDetector\Util\Yaml;

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
