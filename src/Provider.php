<?php


namespace Cheezykins\PHPMyPlex;


use Cheezykins\PHPMyPlex\Api\PlexApi;
use GuzzleHttp\Client;
use Cheezykins\PHPMyPlex\Tests\TestContainerClass;
use League\Container\Argument\RawArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;

class Provider extends AbstractServiceProvider
{

    protected $provides = [
        'test',
        'myplex',
        'client',
        'api',
    ];

    public function register()
    {
        $container = $this->getContainer();
        /** @var Configuration $configuration */
        $configuration = $container->get('configuration');
        $container->add('test', TestContainerClass::class);
        $container->add('client', Client::class);
        $container->add('api', PlexApi::class)
            ->withArgument(new RawArgument($configuration->storage))
            ->withArgument('client');
        $container->add('myplex', MyPlex::class)
            ->withArgument('api')
            ->withArgument(new RawArgument($configuration->userName))
            ->withArgument(new RawArgument($configuration->passWord));
    }

}