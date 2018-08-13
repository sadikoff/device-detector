<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace DeviceDetector\Tests\Parser\Client;

use DeviceDetector\Parser\Client\MobileApp;
use PHPUnit\Framework\TestCase;
use DeviceDetector\Util\Yaml;

class MobileAppTest extends TestCase
{
    /**
     * @dataProvider getFixtures
     */
    public function testParse($useragent, $client)
    {
        $mobileAppParser = new MobileApp();
        $mobileAppParser->setUserAgent($useragent);
        $this->assertEquals($client, $mobileAppParser->parse());
    }

    public function getFixtures()
    {
        $fixtureData = Yaml::parseFile(realpath(dirname(__FILE__)) . '/fixtures/mobile_app.yml');
        return $fixtureData;
    }
}
