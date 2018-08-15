<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace Koff\DeviceDetector\Tests\Parser\Client;

use Koff\DeviceDetector\Parser\Client\PIM;
use PHPUnit\Framework\TestCase;
use Koff\DeviceDetector\Util\Yaml;

class PIMTest extends TestCase
{
    /**
     * @dataProvider getFixtures
     */
    public function testParse($useragent, $client)
    {
        $PIMParser = new PIM();
        $PIMParser->setUserAgent($useragent);
        $this->assertEquals($client, $PIMParser->parse());
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getFixtures()
    {
        $fixtureData = Yaml::parseFile(realpath(dirname(__FILE__)) . '/fixtures/pim.yml');
        return $fixtureData;
    }
}
