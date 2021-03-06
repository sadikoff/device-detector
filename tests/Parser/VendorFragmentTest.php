<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */
namespace Koff\DeviceDetector\Tests\Parser;

use Koff\DeviceDetector\Parser\VendorFragment;
use PHPUnit\Framework\TestCase;
use Koff\DeviceDetector\Util\Yaml;

class VendorFragmentTest extends TestCase
{
    static $regexesTested = array();

    /**
     * @dataProvider getFixtures
     */
    public function testParse($useragent, $vendor)
    {
        $vfParser = new VendorFragment();
        $vfParser->setUserAgent($useragent);
        $this->assertEquals($vendor, $vfParser->parse());
        self::$regexesTested[] = $vfParser->getMatchedRegex();
    }

    public function getFixtures()
    {
        $fixtureData = Yaml::parseFile(realpath(dirname(__FILE__)) . '/fixtures/vendorfragments.yml');
        return $fixtureData;
    }

    public function testAllRegexesTested()
    {
        $regexesNotTested = array();

        $vendorRegexes = Yaml::parseFile(realpath(__DIR__ . '/../../resources/patterns') . DIRECTORY_SEPARATOR . 'vendorfragment.yml');

        foreach ($vendorRegexes as $vendor => $regexes) {
            foreach ($regexes as $regex) {
                if (!in_array($regex, self::$regexesTested)) {
                    $regexesNotTested[] = $vendor . ' / ' . $regex;
                }
            }

        }

        $this->assertEmpty($regexesNotTested, 'Following vendor fragments are not tested: ' . implode(', ', $regexesNotTested));
    }
}
