<?php

namespace LocaleRouter\Factory\Strategy\Persist;

use Interop\Container\ContainerInterface;
use LocaleRouter\Options\LanguageOptions;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Stdlib\RequestInterface;

class CookieStrategyFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        /** @var LanguageOptions $languageOptions */
        $languageOptions = $container->get(LanguageOptions::class);

        /** @var RequestInterface $request */
        $request = $container->get('request');

        return new $requestedName($languageOptions, $request);
    }
}
