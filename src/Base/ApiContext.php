<?php namespace SDK\Base;

/**
 * Class ApiContext
 * @package SDK\API\Connection
 */
abstract class ApiContext
{
    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $api_version;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var string
     */
    private $cache_driver;

    /**
     * @var string
     */
    private $base_url;

    /**
     * @var string
     */
    private $lang_resource;

    /**
     * @var string
     */
    private $lang_locale;

    /**
     * @var bool
     */
    private $verify_callbacks;

    /**
     * @var array
     */
    protected $credentials;

    /**
     * ApiContext constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->mode = $config['mode'];
        $this->api_version = $config['api_version'];
        $this->timeout = $config['timeout'];
        $this->cache_driver = $config['cache_driver'];
        $this->verify_callbacks = $config['verify_callbacks'];
        $this->base_url = $config[$this->mode]['base_url'];
        $this->lang_resource = $config['lang']['resource'];
        $this->lang_locale = $config['lang']['locale'];
        $this->credentials = $this->buildCredentials($config);
    }

    /**
     * @param array $config Configuration file as an associative array
     * @return Credentials
     */
    protected abstract function buildCredentials(array $config);

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->api_version;
    }

    /**
     * @return string
     */
    public function getCacheDriver()
    {
        return $this->cache_driver;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->base_url . '/' . (empty($this->api_version) ? '' : "{$this->api_version}/");
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    public function getLangResource()
    {
        return $this->lang_resource;
    }

    /**
     * @return string
     */
    public function getLangLocale()
    {
        return $this->lang_locale;
    }

    /**
     * @return bool
     */
    public function verifyCallbacks()
    {
        return $this->verify_callbacks;
    }

    /**
     * @return Credentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

}