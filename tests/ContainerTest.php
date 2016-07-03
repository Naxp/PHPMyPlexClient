<?php


namespace Cheezykins\PHPMyPlex\Tests;


use Cheezykins\PHPMyPlex\Api\PlexApi;
use Cheezykins\PHPMyPlex\Configuration;
use Cheezykins\PHPMyPlex\Container;
use Cheezykins\PHPMyPlex\Exceptions\InvalidConfigurationException;

class ContainerTest extends \PHPUnit_Framework_TestCase
{

    /** @var Container $container */
    protected $container;
    protected $configuration;

    protected function setUp()
    {
        $this->configuration = new Configuration();
        $this->configuration->userName = 'user123';
        $this->configuration->passWord = 'pass123';
        $this->container = new Container($this->configuration);
    }

    public function testConfigMustBeValid()
    {
        $configuration = new Configuration();
        $this->expectException(InvalidConfigurationException::class);
        new Container($configuration);
    }
    
    public function testContainerHasConfigurationSingleton()
    {
        $config = $this->container->get('configuration');
        $this->assertEquals($this->configuration, $config);
    }
    
    public function testContainerImplementsProvider()
    {
        $testClass = $this->container->get('test');
        $this->assertInstanceOf(TestContainerClass::class, $testClass);
    }

    public function testContainerKnowsPlexApi()
    {
        $testClass = $this->container->get('api');
        $this->assertInstanceOf(PlexApi::class, $testClass);
    }
}
