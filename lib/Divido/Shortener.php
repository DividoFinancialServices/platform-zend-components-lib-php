<?php

/**
 * @see Zend_Service_ShortUrl_AbstractShortener
 */
require_once 'Zend/Service/ShortUrl/AbstractShortener.php';

/**
 * @see Zend_Json
 */
require_once 'Zend/Json.php';

/**
 * Goo.gl API implementation
 *
 * @category   Maxell
 * @package    Maxell_Service_ShortUrl
 * @subpackage GooGl
 * @version    $Id$
 */
class Divido_Shortener extends Zend_Service_ShortUrl_AbstractShortener
{
    /**
     * Base URI of the service
     *
     * @var string
     */
    public static $_apiKey = '';

    /**
     * Api URI
     *
     * @var string
     */
    public static $_host = 'https://short-link-api-pub.api.divido.net';

    public function __construct($host = false, $key = false)
    {
        if ($host) {
            $this->setHost($host);
        }
        if ($key) {
            $this->setApiKey($key);
        }

        return true;
    }

    /**
     * This function shortens long url
     *
     * @param string $url URL to Shorten
     * @throws Zend_Service_ShortUrl_Exception When URL is not valid
     * @return string New URL
     */
    public function shorten($url)
    {
        $this->_validateUri($url);

        $data = ['longUrl' => $url];

        if (!$this->getApiKey()) {
            return $url;
        }

        $data = ['cmd' => 'new', 'url' => $url];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($ch, CURLOPT_URL, $this->getHost()."/create");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Divido-API-Key: '.$this->getApiKey()]);

        $response = curl_exec($ch);

        $result = json_decode($response);

        if ($result->status == 'ok') {
            return $result->record->redirect;
        } else {
            return null;
        }
    }

    /**
     * Reveals target for short URL
     *
     * @param string $shortenedUrl URL to reveal target of
     * @throws Zend_Service_ShortUrl_Exception When URL is not valid or is not shortened by this service
     * @return string
     */
    public function unshorten($shortenedUrl)
    {
        $this->_validateUri($shortenedUrl);
        $this->_verifyBaseUri($shortenedUrl);

        $httpClient = $this->getHttpClient();
        $httpClient->setUri($this->getHost())
            ->setParameterGet('shortUrl', $shortenedUrl);

        $response = $httpClient->request();
        if ($response->isError()) {
            require_once 'Zend/Service/ShortUrl/Exception.php';
            throw new Zend_Service_ShortUrl_Exception($response->getMessage());
        }

        $body = $response->getBody();
        $body = Zend_Json::decode($body, Zend_Json::TYPE_OBJECT);

        return $body->longUrl;
    }

    public function setApiKey($key)
    {
        self::$_apiKey = $key;
    }

    public function getApiKey()
    {
        return self::$_apiKey;
    }

    public function setHost($host)
    {
        self::$_host = $host;
    }

    public function getHost()
    {
        return self::$_host;
    }
}
