<?php


namespace Cheezykins\PHPMyPlex\Tests;


use Cheezykins\PHPMyPlex\Configuration;
use Cheezykins\PHPMyPlex\Exceptions\NoProxyException;
use Webmozart\KeyValueStore\JsonFileStore;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /** @var Configuration $configuration */
    protected $configuration;

    public function testConfigurationTakesProxyData()
    {
        $this->configuration->proxyScheme = 'https';
        $this->assertEquals('https', $this->configuration->proxyScheme);
        $this->configuration->proxyHost = 'mytestproxy';
        $this->assertEquals('mytestproxy', $this->configuration->proxyHost);
        $this->configuration->proxyPort = 8080;
        $this->assertEquals(8080, $this->configuration->proxyPort);
        $this->configuration->proxyUsername = 'user123';
        $this->assertEquals('user123', $this->configuration->proxyUsername);
        $this->configuration->proxyPassword = 'pass123';
        $this->assertEquals('pass123', $this->configuration->proxyPassword);
    }

    public function testGeneratingProxyAddress()
    {
        $this->configuration->proxyScheme = 'https';
        $this->configuration->proxyHost = 'mytestproxy';
        $this->configuration->proxyPort = 8080;
        $this->configuration->proxyUsername = 'user123';
        $this->configuration->proxyPassword = 'pass123';
        $this->assertEquals('https://user123:pass123@mytestproxy:8080', $this->configuration->getProxyAddress());
    }

    public function testNoProxyThrows()
    {
        $this->expectException(NoProxyException::class);
        $this->configuration->getProxyAddress();
    }

    public function testGetters()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->configuration->testwibble;
    }

    public function testSetters()
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->configuration->testwibble = 'wibble';
    }

    public function testProxySchemeDefaults()
    {
        $this->assertEquals('http', $this->configuration->proxyScheme);
    }

    public function testMyPlexUrlDefaults()
    {
        $this->assertEquals('https://plex.tv/users/sign_in.xml', $this->configuration->myPlexUrl);
        $this->configuration->myPlexUrl = 'https://test/wibble';
        $this->assertEquals('https://test/wibble', $this->configuration->myPlexUrl);
    }

    public function testUserNameDefaults()
    {
        $this->assertNull($this->configuration->userName);
        $this->configuration->userName = 'user123';
        $this->assertEquals('user123', $this->configuration->userName);
    }

    public function testPassWordDefaults()
    {
        $this->assertNull($this->configuration->passWord);
        $this->configuration->passWord = 'pass123';
        $this->assertEquals('pass123', $this->configuration->passWord);
    }

    public function testInvalidConfigFails()
    {
        $this->assertFalse($this->configuration->isValid());
        $this->configuration->userName = 'user123';
        $this->configuration->passWord = 'pass123';
        $this->assertTrue($this->configuration->isValid());
    }

    public function testConfigurationInstantiatesDefaultStore()
    {
        $this->assertInstanceOf(JsonFileStore::class, $this->configuration->storage);
    }

    protected function setUp()
    {
        $this->configuration = new Configuration();
    }
}
