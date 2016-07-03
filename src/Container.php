<?php

namespace Cheezykins\PHPMyPlex;

use Cheezykins\PHPMyPlex\Exceptions\InvalidConfigurationException;
use League\Container\Container as LeagueContainer;
use League\Container\Definition\DefinitionFactoryInterface;
use League\Container\Inflector\InflectorAggregateInterface;
use League\Container\ReflectionContainer;
use League\Container\ServiceProvider\ServiceProviderAggregateInterface;

class Container extends LeagueContainer
{
    protected $config;

    public function __construct(
        Configuration $config,
        ServiceProviderAggregateInterface $providers = null,
        InflectorAggregateInterface $inflectors = null,
        DefinitionFactoryInterface $definitionFactory = null)
    {
        if (!$config->isValid()) {
            throw new InvalidConfigurationException();
        }
        parent::__construct($providers, $inflectors, $definitionFactory);
        $this->delegate(new ReflectionContainer());
        $this->addServiceProvider(Provider::class);
        $this->share('configuration', $config);
    }
}
