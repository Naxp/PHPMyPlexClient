<?php

namespace Cheezykins\PHPMyPlex;

use Cheezykins\PHPMyPlex\Exceptions\NoProxyException;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\JsonFileStore;

class Configuration
{
    protected $proxyScheme = 'http';
    protected $proxyHost = null;
    protected $proxyPort = null;
    protected $proxyUsername = null;
    protected $proxyPassword = null;
    protected $myPlexURL = 'https://plex.tv/users/sign_in.xml';

    protected $storage = null;

    protected $userName = null;
    protected $passWord = null;

    /**
     * Configuration constructor.
     *
     * @param KeyValueStore|null $storage
     */
    public function __construct(KeyValueStore $storage = null)
    {
        if ($storage === null) {
            $storage = new JsonFileStore(__DIR__.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'storage.json');
        }
        $this->setStorage($storage);
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $methodName = 'get'.ucfirst($name);
        if (method_exists($this, $methodName)) {
            return call_user_func([
                $this,
                $methodName,
            ]);
        }
        throw new \OutOfBoundsException($name.' is not a valid property.');
    }

    /**
     * @param $name
     * @param $value
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        $methodName = 'set'.ucfirst($name);
        if (method_exists($this, $methodName)) {
            call_user_func([
                $this,
                $methodName,
            ], $value);

            return $value;
        }
        throw new \OutOfBoundsException($name.' is not a valid property.');
    }

    /**
     * @throws NoProxyException
     *
     * @return string
     */
    public function getProxyAddress()
    {
        if (!$this->proxyHost) {
            throw new NoProxyException();
        }
        $proxyUrl = $this->proxyScheme.'://';
        $prefix = '';
        if ($this->proxyUsername !== null) {
            $proxyUrl .= $this->proxyUsername;
            if ($this->proxyPassword !== null) {
                $proxyUrl .= ':'.$this->proxyPassword;
            }
            $prefix = '@';
        }
        $proxyUrl .= $prefix.$this->proxyHost;
        if ($this->proxyPort !== null) {
            $proxyUrl .= ':'.$this->proxyPort;
        }

        return $proxyUrl;
    }

    /**
     * @return null|KeyValueStore
     */
    protected function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param KeyValueStore $storage
     */
    protected function setStorage(KeyValueStore $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return string
     */
    protected function getProxyScheme()
    {
        return $this->proxyScheme;
    }

    /**
     * @param $proxyScheme
     */
    protected function setProxyScheme($proxyScheme)
    {
        $this->proxyScheme = $proxyScheme;
    }

    /**
     * @return string
     */
    protected function getProxyHost()
    {
        return $this->proxyHost;
    }

    /**
     * @param $proxyHost
     */
    protected function setProxyHost($proxyHost)
    {
        $this->proxyHost = $proxyHost;
    }

    /**
     * @return int
     */
    protected function getProxyPort()
    {
        return $this->proxyPort;
    }

    /**
     * @param $proxyPort
     */
    protected function setProxyPort($proxyPort)
    {
        $this->proxyPort = $proxyPort;
    }

    /**
     * @return string
     */
    protected function getProxyUsername()
    {
        return $this->proxyUsername;
    }

    /**
     * @param $proxyUsername
     */
    protected function setProxyUsername($proxyUsername)
    {
        $this->proxyUsername = $proxyUsername;
    }

    /**
     * @return string
     */
    protected function getProxyPassword()
    {
        return $this->proxyPassword;
    }

    /**
     * @param $proxyPassword
     */
    protected function setProxyPassword($proxyPassword)
    {
        $this->proxyPassword = $proxyPassword;
    }

    /**
     * @return string
     */
    protected function getMyPlexURL()
    {
        return $this->myPlexURL;
    }

    /**
     * @param $myPlexURL
     */
    protected function setMyPlexURL($myPlexURL)
    {
        $this->myPlexURL = $myPlexURL;
    }

    /**
     * @return string
     */
    protected function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param $userName
     */
    protected function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    protected function getPassWord()
    {
        return $this->passWord;
    }

    /**
     * @param $passWord
     */
    protected function setPassWord($passWord)
    {
        $this->passWord = $passWord;
    }

    public function isValid()
    {
        return $this->userName !== null && $this->passWord !== null;
    }
}
