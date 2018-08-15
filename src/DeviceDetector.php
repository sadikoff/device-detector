<?php
/**
 * Device Detector - The Universal Device Detection library for parsing User Agents.
 *
 * @see    http://piwik.org
 *
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */

namespace Koff\DeviceDetector;

use Koff\DeviceDetector\Cache\DDCacheInterface;
use Koff\DeviceDetector\Cache\StaticCache;
use Koff\DeviceDetector\Parser\Bot;
use Koff\DeviceDetector\Parser\BotParserAbstract;
use Koff\DeviceDetector\Parser\Client\Browser;
use Koff\DeviceDetector\Parser\OperatingSystem;
use Koff\DeviceDetector\Parser\Client\ClientParserAbstract;
use Koff\DeviceDetector\Parser\Device\DeviceParserAbstract;
use Koff\DeviceDetector\Parser\VendorFragment;

/**
 * Class DeviceDetector.
 *
 * Magic Device Type Methods
 *
 * @method bool isSmartphone()
 * @method bool isFeaturePhone()
 * @method bool isTablet()
 * @method bool isPhablet()
 * @method bool isConsole()
 * @method bool isPortableMediaPlayer()
 * @method bool isCarBrowser()
 * @method bool isTV()
 * @method bool isSmartDisplay()
 * @method bool isCamera()
 *
 * Magic Client Type Methods
 * @method bool isBrowser()
 * @method bool isFeedReader()
 * @method bool isMobileApp()
 * @method bool isPIM()
 * @method bool isLibrary()
 * @method bool isMediaPlayer()
 */
class DeviceDetector
{
    /**
     * Current version number of DeviceDetector.
     */
    const VERSION = '3.11.0';

    /**
     * Holds all registered client types.
     *
     * @var array
     */
    public static $clientTypes = array();

    /**
     * Operating system families that are known as desktop only.
     *
     * @var array
     */
    protected static $desktopOsArray = array('AmigaOS', 'IBM', 'GNU/Linux', 'Mac', 'Unix', 'Windows', 'BeOS', 'Chrome OS');

    /**
     * Constant used as value for unknown browser / os.
     */
    const UNKNOWN = 'UNK';

    /**
     * Holds the useragent that should be parsed.
     *
     * @var string
     */
    protected $userAgent;

    /**
     * Holds the operating system data after parsing the UA.
     *
     * @var array
     */
    protected $os = null;

    /**
     * Holds the client data after parsing the UA.
     *
     * @var array
     */
    protected $client = null;

    /**
     * Holds the device type after parsing the UA.
     *
     * @var string
     */
    protected $device = null;

    /**
     * Holds the device brand data after parsing the UA.
     *
     * @var string
     */
    protected $brand = '';

    /**
     * Holds the device model data after parsing the UA.
     *
     * @var string
     */
    protected $model = '';

    /**
     * Holds bot information if parsing the UA results in a bot
     * (All other information attributes will stay empty in that case).
     *
     * If $discardBotInformation is set to true, this property will be set to
     * true if parsed UA is identified as bot, additional information will be not available
     *
     * If $skipBotDetection is set to true, bot detection will not be performed and isBot will
     * always be false
     *
     * @var array|boolean
     */
    protected $bot = null;

    /**
     * @var bool
     */
    protected $discardBotInformation = false;

    /**
     * @var bool
     */
    protected $skipBotDetection = false;

    /**
     * Holds the cache class used for caching the parsed yml-Files.
     *
     * @var DDCacheInterface|\Doctrine\Common\Cache\CacheProvider
     */
    protected $cache = null;

    /**
     * @var ClientParserAbstract[]
     */
    protected $clientParsers = array();

    /**
     * @var DeviceParserAbstract[]
     */
    protected $deviceParsers = array();

    /**
     * @var BotParserAbstract[]
     */
    public $botParsers = array();

    /**
     * @var bool
     */
    private $parsed = false;

    /**
     * Constructor.
     *
     * @param string $userAgent UA to parse
     *
     * @throws \Exception
     */
    public function __construct($userAgent = '')
    {
        if ('' != $userAgent) {
            $this->setUserAgent($userAgent);
        }

        $this->addClientParser('FeedReader');
        $this->addClientParser('MobileApp');
        $this->addClientParser('MediaPlayer');
        $this->addClientParser('PIM');
        $this->addClientParser('Browser');
        $this->addClientParser('Library');

        $this->addDeviceParser('HbbTv');
        $this->addDeviceParser('Console');
        $this->addDeviceParser('CarBrowser');
        $this->addDeviceParser('Camera');
        $this->addDeviceParser('PortableMediaPlayer');
        $this->addDeviceParser('Mobile');

        $this->addBotParser(new Bot());
    }

    public function __call($methodName, $arguments)
    {
        foreach (DeviceParserAbstract::getAvailableDeviceTypes() as $deviceName => $deviceType) {
            if (strtolower($methodName) == 'is'.strtolower(str_replace(' ', '', $deviceName))) {
                return $this->getDevice() == $deviceType;
            }
        }

        foreach (self::$clientTypes as $client) {
            if (strtolower($methodName) == 'is'.strtolower(str_replace(' ', '', $client))) {
                return $this->getClient('type') == $client;
            }
        }

        throw new \BadMethodCallException("Method $methodName not found");
    }

    /**
     * Sets the useragent to be parsed.
     *
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        if ($this->userAgent != $userAgent) {
            $this->reset();
        }
        $this->userAgent = $userAgent;
    }

    protected function reset()
    {
        $this->bot = null;
        $this->client = null;
        $this->device = null;
        $this->os = null;
        $this->brand = '';
        $this->model = '';
        $this->parsed = false;
    }

    /**
     * @param ClientParserAbstract|string $parser
     *
     * @throws \Exception
     */
    public function addClientParser($parser)
    {
        if (is_string($parser) && class_exists('Koff\\DeviceDetector\\Parser\\Client\\'.$parser)) {
            $className = 'Koff\\DeviceDetector\\Parser\\Client\\'.$parser;
            $parser = new $className();
        }

        if ($parser instanceof ClientParserAbstract) {
            $this->clientParsers[] = $parser;
            self::$clientTypes[] = $parser->getName();

            return;
        }

        throw new \Exception('client parser not found');
    }

    public function getClientParsers()
    {
        return $this->clientParsers;
    }

    /**
     * @param DeviceParserAbstract|string $parser
     *
     * @throws \Exception
     */
    public function addDeviceParser($parser)
    {
        if (is_string($parser) && class_exists('Koff\\DeviceDetector\\Parser\\Device\\'.$parser)) {
            $className = 'Koff\\DeviceDetector\\Parser\\Device\\'.$parser;
            $parser = new $className();
        }

        if ($parser instanceof DeviceParserAbstract) {
            $this->deviceParsers[] = $parser;

            return;
        }

        throw new \Exception('device parser not found');
    }

    public function getDeviceParsers()
    {
        return $this->deviceParsers;
    }

    /**
     * @param BotParserAbstract $parser
     */
    public function addBotParser(BotParserAbstract $parser)
    {
        $this->botParsers[] = $parser;
    }

    public function getBotParsers()
    {
        return $this->botParsers;
    }

    /**
     * Sets whether to discard additional bot information
     * If information is discarded it's only possible check whether UA was detected as bot or not.
     * (Discarding information speeds up the detection a bit).
     *
     * @param bool $discard
     */
    public function discardBotInformation($discard = true)
    {
        $this->discardBotInformation = $discard;
    }

    /**
     * Sets whether to skip bot detection.
     * It is needed if we want bots to be processed as a simple clients. So we can detect if it is mobile client,
     * or desktop, or enything else. By default all this information is not retrieved for the bots.
     *
     * @param bool $skip
     */
    public function skipBotDetection($skip = true)
    {
        $this->skipBotDetection = $skip;
    }

    /**
     * Returns if the parsed UA was identified as a Bot.
     *
     * @see bots.yml for a list of detected bots
     *
     * @return bool
     */
    public function isBot()
    {
        return !empty($this->bot);
    }

    /**
     * Returns if the parsed UA was identified as a touch enabled device.
     *
     * Note: That only applies to windows 8 tablets
     *
     * @return bool
     */
    public function isTouchEnabled()
    {
        $regex = 'Touch';

        return (bool) $this->matchUserAgent($regex);
    }

    /**
     * Returns if the parsed UA contains the 'Android; Tablet;' fragment.
     *
     * @return bool
     */
    protected function hasAndroidTableFragment()
    {
        $regex = 'Android( [\.0-9]+)?; Tablet;';

        return (bool) $this->matchUserAgent($regex);
    }

    /**
     * Returns if the parsed UA contains the 'Android; Mobile;' fragment.
     *
     * @return bool
     */
    protected function hasAndroidMobileFragment()
    {
        $regex = 'Android( [\.0-9]+)?; Mobile;';

        return (bool) $this->matchUserAgent($regex);
    }

    protected function usesMobileBrowser()
    {
        return 'browser' == $this->getClient('type') && Browser::isMobileOnlyBrowser($this->getClient('short_name'));
    }

    public function isMobile()
    {
        // Mobile device types
        if (!empty($this->device) && in_array(
                $this->device,
                array(
                    DeviceParserAbstract::DEVICE_TYPE_FEATURE_PHONE,
                    DeviceParserAbstract::DEVICE_TYPE_SMARTPHONE,
                    DeviceParserAbstract::DEVICE_TYPE_TABLET,
                    DeviceParserAbstract::DEVICE_TYPE_PHABLET,
                    DeviceParserAbstract::DEVICE_TYPE_CAMERA,
                    DeviceParserAbstract::DEVICE_TYPE_PORTABLE_MEDIA_PAYER,
                )
            )
        ) {
            return true;
        }

        // non mobile device types
        if (!empty($this->device) && in_array(
                $this->device,
                array(
                    DeviceParserAbstract::DEVICE_TYPE_TV,
                    DeviceParserAbstract::DEVICE_TYPE_SMART_DISPLAY,
                    DeviceParserAbstract::DEVICE_TYPE_CONSOLE,
                )
            )
        ) {
            return false;
        }

        // Check for browsers available for mobile devices only
        if ($this->usesMobileBrowser()) {
            return true;
        }

        $osShort = $this->getOs('short_name');
        if (empty($osShort) || self::UNKNOWN == $osShort) {
            return false;
        }

        return !$this->isBot() && !$this->isDesktop();
    }

    /**
     * Returns if the parsed UA was identified as desktop device
     * Desktop devices are all devices with an unknown type that are running a desktop os.
     *
     * @see self::$desktopOsArray
     *
     * @return bool
     */
    public function isDesktop()
    {
        $osShort = $this->getOs('short_name');
        if (empty($osShort) || self::UNKNOWN == $osShort) {
            return false;
        }

        // Check for browsers available for mobile devices only
        if ($this->usesMobileBrowser()) {
            return false;
        }

        $decodedFamily = OperatingSystem::getOsFamily($osShort);

        return in_array($decodedFamily, self::$desktopOsArray);
    }

    /**
     * Returns the operating system data extracted from the parsed UA.
     *
     * If $attr is given only that property will be returned
     *
     * @param string $attr property to return(optional)
     *
     * @return array|string
     */
    public function getOs($attr = '')
    {
        if ('' == $attr) {
            return $this->os;
        }

        if (!isset($this->os[$attr])) {
            return self::UNKNOWN;
        }

        return $this->os[$attr];
    }

    /**
     * Returns the client data extracted from the parsed UA.
     *
     * If $attr is given only that property will be returned
     *
     * @param string $attr property to return(optional)
     *
     * @return array|string
     */
    public function getClient($attr = '')
    {
        if ('' == $attr) {
            return $this->client;
        }

        if (!isset($this->client[$attr])) {
            return self::UNKNOWN;
        }

        return $this->client[$attr];
    }

    /**
     * Returns the device type extracted from the parsed UA.
     *
     * @see DeviceParserAbstract::$deviceTypes for available device types
     *
     * @return int|null
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Returns the device type extracted from the parsed UA.
     *
     * @see DeviceParserAbstract::$deviceTypes for available device types
     *
     * @return string
     */
    public function getDeviceName()
    {
        if (null !== $this->getDevice()) {
            return DeviceParserAbstract::getDeviceName($this->getDevice());
        }

        return '';
    }

    /**
     * Returns the device brand extracted from the parsed UA.
     *
     * @see self::$deviceBrand for available device brands
     *
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Returns the full device brand name extracted from the parsed UA.
     *
     * @see self::$deviceBrand for available device brands
     *
     * @return string
     */
    public function getBrandName()
    {
        return DeviceParserAbstract::getFullName($this->getBrand());
    }

    /**
     * Returns the device model extracted from the parsed UA.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Returns the user agent that is set to be parsed.
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Returns the bot extracted from the parsed UA.
     *
     * @return array
     */
    public function getBot()
    {
        return $this->bot;
    }

    /**
     * Returns true, if userAgent was already parsed with parse().
     *
     * @return bool
     */
    public function isParsed()
    {
        return $this->parsed;
    }

    /**
     * Triggers the parsing of the current user agent.
     */
    public function parse()
    {
        if ($this->isParsed()) {
            return;
        }

        $this->parsed = true;

        // skip parsing for empty useragents or those not containing any letter
        if (empty($this->userAgent) || !preg_match('/([a-z])/i', $this->userAgent)) {
            return;
        }

        $this->parseBot();
        if ($this->isBot()) {
            return;
        }

        $this->parseOs();

        /*
         * Parse Clients
         * Clients might be browsers, Feed Readers, Mobile Apps, Media Players or
         * any other application accessing with an parseable UA
         */
        $this->parseClient();

        $this->parseDevice();
    }

    /**
     * Parses the UA for bot information using the Bot parser.
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function parseBot()
    {
        if ($this->skipBotDetection) {
            $this->bot = false;

            return false;
        }

        $parsers = $this->getBotParsers();

        foreach ($parsers as $parser) {
            $parser->setUserAgent($this->getUserAgent());
            $parser->setCache($this->getCache());
            if ($this->discardBotInformation) {
                $parser->discardDetails();
            }
            $bot = $parser->parse();
            if (!empty($bot)) {
                $this->bot = $bot;
                break;
            }
        }

        return true;
    }

    protected function parseClient()
    {
        $parsers = $this->getClientParsers();

        foreach ($parsers as $parser) {
            $parser->setCache($this->getCache());
            $parser->setUserAgent($this->getUserAgent());
            $client = $parser->parse();
            if (!empty($client)) {
                $this->client = $client;
                break;
            }
        }
    }

    protected function parseDevice()
    {
        $parsers = $this->getDeviceParsers();

        foreach ($parsers as $parser) {
            $parser->setCache($this->getCache());
            $parser->setUserAgent($this->getUserAgent());
            if ($parser->parse()) {
                $this->device = $parser->getDeviceType();
                $this->model = $parser->getModel();
                $this->brand = $parser->getBrand();
                break;
            }
        }

        /*
         * If no brand has been assigned try to match by known vendor fragments
         */
        if (empty($this->brand)) {
            $vendorParser = new VendorFragment($this->getUserAgent());
            $vendorParser->setCache($this->getCache());
            $this->brand = $vendorParser->parse();
        }

        $osShortName = $this->getOs('short_name');
        $osFamily = OperatingSystem::getOsFamily($osShortName);
        $osVersion = $this->getOs('version');
        $clientName = $this->getClient('name');

        /*
         * Assume all devices running iOS / Mac OS are from Apple
         */
        if (empty($this->brand) && in_array($osShortName, array('ATV', 'IOS', 'MAC'))) {
            $this->brand = 'AP';
        }

        /*
         * Chrome on Android passes the device type based on the keyword 'Mobile'
         * If it is present the device should be a smartphone, otherwise it's a tablet
         * See https://developer.chrome.com/multidevice/user-agent#chrome_for_android_user_agent
         */
        if (null === $this->device && 'Android' == $osFamily && in_array($this->getClient('name'), array('Chrome', 'Chrome Mobile'))) {
            if ($this->matchUserAgent('Chrome/[\.0-9]* Mobile')) {
                $this->device = DeviceParserAbstract::DEVICE_TYPE_SMARTPHONE;
            } elseif ($this->matchUserAgent('Chrome/[\.0-9]* (?!Mobile)')) {
                $this->device = DeviceParserAbstract::DEVICE_TYPE_TABLET;
            }
        }

        /*
         * Some user agents simply contain the fragment 'Android; Tablet;' or 'Opera Tablet', so we assume those devices as tablets
         */
        if (null === $this->device && ($this->hasAndroidTableFragment() || $this->matchUserAgent('Opera Tablet'))) {
            $this->device = DeviceParserAbstract::DEVICE_TYPE_TABLET;
        }

        /*
         * Some user agents simply contain the fragment 'Android; Mobile;', so we assume those devices as smartphones
         */
        if (null === $this->device && $this->hasAndroidMobileFragment()) {
            $this->device = DeviceParserAbstract::DEVICE_TYPE_SMARTPHONE;
        }

        /*
         * Android up to 3.0 was designed for smartphones only. But as 3.0, which was tablet only, was published
         * too late, there were a bunch of tablets running with 2.x
         * With 4.0 the two trees were merged and it is for smartphones and tablets
         *
         * So were are expecting that all devices running Android < 2 are smartphones
         * Devices running Android 3.X are tablets. Device type of Android 2.X and 4.X+ are unknown
         */
        if (null === $this->device && 'AND' == $osShortName && '' != $osVersion) {
            if (-1 == version_compare($osVersion, '2.0')) {
                $this->device = DeviceParserAbstract::DEVICE_TYPE_SMARTPHONE;
            } elseif (version_compare($osVersion, '3.0') >= 0 and -1 == version_compare($osVersion, '4.0')) {
                $this->device = DeviceParserAbstract::DEVICE_TYPE_TABLET;
            }
        }

        /*
         * All detected feature phones running android are more likely a smartphone
         */
        if (DeviceParserAbstract::DEVICE_TYPE_FEATURE_PHONE == $this->device && 'Android' == $osFamily) {
            $this->device = DeviceParserAbstract::DEVICE_TYPE_SMARTPHONE;
        }

        /*
         * According to http://msdn.microsoft.com/en-us/library/ie/hh920767(v=vs.85).aspx
         * Internet Explorer 10 introduces the "Touch" UA string token. If this token is present at the end of the
         * UA string, the computer has touch capability, and is running Windows 8 (or later).
         * This UA string will be transmitted on a touch-enabled system running Windows 8 (RT)
         *
         * As most touch enabled devices are tablets and only a smaller part are desktops/notebooks we assume that
         * all Windows 8 touch devices are tablets.
         */

        if (null === $this->device && ('WRT' == $osShortName || ('WIN' == $osShortName && version_compare($osVersion, '8.0'))) && $this->isTouchEnabled()) {
            $this->device = DeviceParserAbstract::DEVICE_TYPE_TABLET;
        }

        /*
         * All devices running Opera TV Store are assumed to be a tv
         */
        if ($this->matchUserAgent('Opera TV Store')) {
            $this->device = DeviceParserAbstract::DEVICE_TYPE_TV;
        }

        /*
         * Devices running Kylo or Espital TV Browsers are assumed to be a TV
         */
        if (null === $this->device && in_array($clientName, array('Kylo', 'Espial TV Browser'))) {
            $this->device = DeviceParserAbstract::DEVICE_TYPE_TV;
        }

        // set device type to desktop for all devices running a desktop os that were not detected as an other device type
        if (null === $this->device && $this->isDesktop()) {
            $this->device = DeviceParserAbstract::DEVICE_TYPE_DESKTOP;
        }
    }

    protected function parseOs()
    {
        $osParser = new OperatingSystem();
        $osParser->setUserAgent($this->getUserAgent());
        $osParser->setCache($this->getCache());
        $this->os = $osParser->parse();
    }

    protected function matchUserAgent($regex)
    {
        $regex = '/(?:^|[^A-Z_-])(?:'.str_replace('/', '\/', $regex).')/i';

        if (preg_match($regex, $this->userAgent, $matches)) {
            return $matches;
        }

        return false;
    }

    /**
     * Parses a useragent and returns the detected data.
     *
     * ATTENTION: Use that method only for testing or very small applications
     * To get fast results from DeviceDetector you need to make your own implementation,
     * that should use one of the caching mechanisms. See README.md for more information.
     *
     * @internal
     *
     * @deprecated
     *
     * @param string $ua UserAgent to parse
     *
     * @return array
     * @throws \Exception
     */
    public static function getInfoFromUserAgent($ua)
    {
        $deviceDetector = new self($ua);
        $deviceDetector->parse();

        if ($deviceDetector->isBot()) {
            return array(
                'user_agent' => $deviceDetector->getUserAgent(),
                'bot' => $deviceDetector->getBot(),
            );
        }

        $osFamily = OperatingSystem::getOsFamily($deviceDetector->getOs('short_name'));
        $browserFamily = Browser::getBrowserFamily($deviceDetector->getClient('short_name'));

        $processed = array(
            'user_agent' => $deviceDetector->getUserAgent(),
            'os' => $deviceDetector->getOs(),
            'client' => $deviceDetector->getClient(),
            'device' => array(
                'type' => $deviceDetector->getDeviceName(),
                'brand' => $deviceDetector->getBrand(),
                'model' => $deviceDetector->getModel(),
            ),
            'os_family' => false !== $osFamily ? $osFamily : 'Unknown',
            'browser_family' => false !== $browserFamily ? $browserFamily : 'Unknown',
        );

        return $processed;
    }

    /**
     * Sets the Cache class.
     *
     * @param DDCacheInterface|\Doctrine\Common\Cache\CacheProvider $cache
     *
     * @throws \Exception
     */
    public function setCache($cache)
    {
        if ($cache instanceof DDCacheInterface ||
            (class_exists('\Doctrine\Common\Cache\CacheProvider') && $cache instanceof \Doctrine\Common\Cache\CacheProvider)
        ) {
            $this->cache = $cache;

            return;
        }

        throw new \Exception('Cache not supported');
    }

    /**
     * Returns Cache object.
     *
     * @return DDCacheInterface|\Doctrine\Common\Cache\CacheProvider
     */
    public function getCache()
    {
        if (!empty($this->cache)) {
            return $this->cache;
        }

        return new StaticCache();
    }
}
