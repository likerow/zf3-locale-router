# Main config
```php
'localeRouter' => [
    // configure default language
    'defaultLocale' => 'de_DE',

    // configure supported languages
    // root key is special key. If you want example.com/myuri to be nl_NL (without "nl" segment in uri path, then you need to set the language for the "root" key)
    'languages' => ['root' => 'de_DE', 'en' => 'en_GB'],
]
```

* `defaultLocale`: Specifcy fall-back locale which is used if the locale could not be extracted from strategies
* `languages`: Array with uri-path => locale pairs.
  * If `root` is specified as key for a locale, no path segment will be used for this locale.
    * Example:
    <br>www.example.com/myuri would be `de_DE` in the example above.<br>
    www.example.com/en/myuri would be `en_GB`. 

# Extract strategies
The following strategies can be used to extract locale information.

## Asset strategy
This strategy is used to prevent the rewriting of Assets to an locale aware URI.

- config key: `extract-asset`
- options:
  - file_extensions (array): Specify file extensions which you don't want being rewritten to locale aware URIs. Default value: `'js', 'css', 'jpg', 'jpeg', 'gif', 'png'`.

F.e. if you don't want the URIs of your assets being redirected to locale aware URI apply the following config:

```php
'localeRouter' => [
    'extractStrategies' => [
        [
            'name' => 'extract-asset',
            'options' => [
                'file_extensions' => [
                    'js', 'css', 'jpg', 'jpeg', 'gif', 'png'
                ]
            ]
        ],
        ...
    ]
]
```

This prevents the URI `http://www.example.com/my/asset.css` from being rewritten to `http://www.example.com/LOCALE/my/asset.css`.


## User identity strategy
- config key: `extract-useridentity`

This strategy tries to extract locale information from a given logged in user. You have to specify a valid `authService` (which implements `Zend\Authentication\AuthenticationServiceInterface`) in your config:
```php
'localeRouter' => [
    'extractStrategies' => [
        'extract-useridentity'
    ],    
    'authService' => 'zfcuser_auth_service', // for example ZfcUser's authservice (see: https://github.com/ZF-Commons/ZfcUser)
];
```

You also need to implement `LocaleRouter\Entity\LocaleUserInterface` in your user entity/model. To do so, you can just use the provided `LocaleRouter\Entity\LocaleUserTrait.php`.

## Uripath strategy
- config key: `extract-uripath`

This strategy tries to parse the locale from the provided request URI:
```php
'localeRouter' => [
    // configure default language
    'defaultLocale' => 'de_DE',
    
    // configure supported languages
    'languages' => ['de' => 'de_DE', 'nl' => 'nl_NL'],
    
    'extractStrategies' => [
        'extract-uripath'
    ],    
];
```

Example:

```
Request URI: http://www.example.com/de/my/uri
=> resolves to "de_DE".

Request URI: http://www.example.com/nl/my/uri
=> resolves to "nl_NL".
```

## Query strategy
- config key: `extract-query`
- options
  - paramName (string): name of the parameter used for the detection. Default value: `locale`.

Similar to [Uripath strategy](#uripath-strategy), but tries to extract the locale from a query string:
```php
'localeRouter' => [
    // configure default language
    'defaultLocale' => 'de_DE',
    
    // configure supported languages
    'languages' => ['de' => 'de_DE', 'en' => 'en_US'],
    
    'extractStrategies' => [
        [
            'name' => 'extract-query',
            'options' => [
                'paramName' => 'myLangParam'
            ]
        ],
    ],    
];
```

Example:
```
Request URI: http://www.example.com/my/uri?myLangParam=de
=> resolves to "de_DE".

Request URI: http://www.example.com/my/uri?myLangParam=de_DE
=> resolves to "de_DE".

Request URI: http://www.example.com/my/uri?myLangParam=en
=> resolves to "en_US".

Request URI: http://www.example.com/my/uri?myLangParam=en_US
=> resolves to "en_US".
```


## AcceptLanguage strategy
- config key: `extract-acceptlanguage`

This strategy tries to extract the locale information from the given Accept-Language header.

```php
'localeRouter' => [
    // configure default language
    'defaultLocale' => 'de_DE',
    
    // configure supported languages
    'languages' => ['de' => 'de_DE', 'en' => 'en_US'],
    
    'extractStrategies' => [
        'extract-acceptlanguage'
    ],    
];
```

Example header:
```
Accept-Language: de-DE, en;q=0.8, fr;q=0.7, *;q=0.5
=> resolves to "de_DE"

Accept-Language: en;q=0.8, fr;q=0.7, *;q=0.5
=> resolves to "en_US"
```


## Host strategy
- config key: `extract-host`
- options:
  - domain (string): specify your domain schema in respect of the locale which should be used. F.e. if you have multiple top-level-domains (TLDs), one for each language, you can specify `www.example.:locale` to tell the module to use the TLD for locale recognition.
  It is also possible to use differen subdomains per locale (`:locale.example.com`).
  - aliases (array): map TLDs or subdomains to locales.

This strategy extracts locale information from TLDs or subdomain of the request URI.
TLDs have to be mapped to a locale. F.e. if you want the locale for your `.com` TLD to be `en_US`, you have to map `'com' => 'en_US'`:

```php
'localeRouter' => [
    // configure default language
    'defaultLocale' => 'de_DE',
    
    // configure supported languages
    'languages' => ['de' => 'de_DE', 'en' => 'en_US'],
    
    'extractStrategies' => [
        [
            'name'    => 'extract-host',
            'options' => [
                'domain'  => 'www.example.:locale'
                'aliases' => ['com' => 'en_US', 'co.uk' => 'en_GB', 'de' => 'de_DE'],
            ],
        ],
    ],
],
```

## Cookie strategy
- config key: `extract-cookie`
- options:
  - cookieName (string): The name of the cookie. Default value: `localeRouter`.

This strategy tries to extract the locale information from a cookie.

```php
'localeRouter' => [
    // configure default language
    'defaultLocale' => 'de_DE',
    
    // configure supported languages
    'languages' => ['de' => 'de_DE', 'en' => 'en_US'],
    
    'extractStrategies' => [
        [
            'name'    => 'extract-cookie',
            'options' => [
                'cookieName'  => 'myCookie'
            ],
        ],
    ],
],
```

If a cookie with a locale was set previously, this strategy will read the correpsonding cookie.

# Persist strategies
The following strategies can be used to persist locale information. 

## Doctrine strategy
- config key: `persist-doctrine` or `LocaleRouter\Strategy\Persist\DoctrineStrategy::class`

This strategy does persist the extracted locale to a user entity. F.e. you want to save locale information per user in your application, you can first use one of the [extract strategies](#extract-strategies) to extract the locale and then save that information to the current user entity.
It is the counterpart of the extract [Useridentity strategy](#user-identity-strategy).
You have to specify a valid `authService` (which implements `Zend\Authentication\AuthenticationServiceInterface`) in your config (because the strategy retrieves the user entity of the logged in user from the auth service).
You also need to implement `LocaleRouter\Entity\LocaleUserInterface` in your user entity/model. To do so, you can just use the provided `LocaleRouter\Entity\LocaleUserTrait.php`.

```php
'localeRouter' => [
    'persistStrategies' => [
        'persist-doctrine'
    ],    
    'authService' => 'zfcuser_auth_service', // for example ZfcUser's authservice (see: https://github.com/ZF-Commons/ZfcUser)
];
```

## Cookie strategy (Persist)
- config key: `persist-cookie` or `LocaleRouter\Strategy\Persist\CookieStrategy::class`

This strategy does persist the extracted locale within a cookie.
It is the counterpart of the extract [Cookie strategy](#cookie-strategy).
Make sure if you use the custom `cookieName`-parameter to configure both strategies with the same parameter name.

```php
'localeRouter' => [
    'persistStrategies' => [
        [
            'name'    => 'persist-cookie',
            'options' => [
                'cookieName'  => 'myCookie'
            ],
        ],
    ], 
    'authService' => 'zfcuser_auth_service', // for example ZfcUser's authservice (see: https://github.com/ZF-Commons/ZfcUser)
];
```


# Example Config
```php
$settings = [
    // configure default language
    'defaultLocale' => 'de_DE',

    // configure supported languages
    'languages' => ['de' => 'de_DE', 'en' => 'en_GB'],

    // Adding extract strategies
    'extractStrategies' => [
        'extract-asset',
        'extract-query',
        'extract-uripath',
        'extract-host',
        'extract-useridentity',
        'extract-cookie',
        'extract-acceptlanguage'
    ],

    // Adding persist strategies
    'persistStrategies' => [
        LocaleRouter\Strategy\Persist\DoctrineStrategy::class,
        LocaleRouter\Strategy\Persist\CookieStrategy::class,
    ],

    // configure auth service (for Extract/UserIdentityStrategy or Persist/DoctrineStrategy)
    'authService' => 'zfcuser_auth_service' // for example ZfcUser's authservice (see: https://github.com/ZF-Commons/ZfcUser)
];
```

# Example code for Application's `Module.php`
This example code showcases how you can set the extracted locale information to your different locale aware modules. This code is not required, depends on your usecase and is only for showcase:

```php
namespace Application;

use Gedmo\Translatable\TranslatableListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\View\Renderer\JsonRenderer;
use Zend\View\ViewEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        // <-- Language Settings -->
        $eventManager   = $e->getApplication()->getEventManager();
        $serviceManager = $e->getApplication()->getServiceManager();

        $config = $serviceManager->get('Config');

        // get locale (previously set by localeRouter module)
        $locale = \Locale::getDefault();

        /** @var Translator $translator */
        $translator = $serviceManager->get('MvcTranslator');

        // set fallback locale for translator
        if (isset($config['localeRouter']['defaultLocale'])) {
            $translator->setFallbackLocale($config['localeRouter']['defaultLocale']);
        }

        // Set translator in FlashMessenger
        /** @var FlashMessenger $flashMessenger */
        $flashMessenger = $serviceManager->get('ViewHelperManager')->get('FlashMessenger');

        $flashMessenger->setTranslator($translator);
        $flashMessenger->setTranslatorTextDomain('Messages');

        // set form translator
        \Zend\Validator\AbstractValidator::setDefaultTranslator($translator);

        $language = \Locale::getPrimaryLanguage($locale);

        $translationPath = sprintf('vendor/zendframework/zend-i18n-resources/languages/%s/Zend_Captcha.php', $language);
        if (is_readable($translationPath)) {
            $translator->addTranslationFile('phpArray', $translationPath, 'default', $locale);
        }

        $translationPath = sprintf('vendor/zendframework/zend-i18n-resources/languages/%s/Zend_Validate.php', $language);
        if (is_readable($translationPath)) {
            $translator->addTranslationFile('phpArray', $translationPath, 'default', $locale);
        }

        // Configuring language view helper
        $sharedEvents = $eventManager->getSharedManager();
        $sharedEvents->attach('Zend\View\View', ViewEvent::EVENT_RENDERER_POST, function ($event) use ($locale) {
            $renderer = $event->getRenderer();
            if (! $renderer instanceof JsonRenderer) {
                $renderer->plugin('dateFormat')->setLocale($locale);
                $renderer->plugin('numberFormat')->setLocale($locale);
                $renderer->plugin('currencyFormat')->setLocale($locale);
            }
        });

        /** Doctrine Translatable (@see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/translatable.md) */
        $translatableListener = new TranslatableListener();

        // set default locale to Doctrine's translatable
        if (isset($config['localeRouter']['defaultLocale'])) {
            $translatableListener->setDefaultLocale($config['localeRouter']['defaultLocale']);
        }

        $translatableListener->setTranslatableLocale($locale);
        $translatableListener->setTranslationFallback(true);

        /** @var EventManager $doctrineEventManager */
        $doctrineEventManager = $serviceManager->get('doctrine.eventmanager.orm_default');
        $doctrineEventManager->addEventSubscriber($translatableListener);
        // <-- Language Settings -->
        
        // ...
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}

``` 

# PHPUnit tests
This module is disabled for `phpunit` tests by default, because `\Zend\Test\PHPUnit\Controller\AbstractControllerTestCase` does not work well with this module:
The `LanguageTreeRouteStack` cannot extract locale in the following scenario and will therefore issue a redirect response (which would break your tests).

Therefore the locale detection is disabled by default when executed in a `phpunit` environment.

```php
...
use PHPUnit\Framework\TestCase;
use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;

class Search extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(include 'config/application.config.php');
    }

    public function testAction()
    {
        $this->dispatch('/');
        $this->assertResponseStatusCode(200); # this will be always 302 if you use the dispatch method to issue a "faked" request to your controllers.
        $this->assertModuleName('MyModule');
        $this->assertMatchedRouteName('my/route');
    }
}
``` 

If you want to explicitly test the processing of locale / need a properly set locale for your test case, you have two options:

* Enable it per default for `phpunit` tests via `phpunit` configured server variable.
* Enable it intentionally on a portion of your tests / one specific test.

## Activate for all PHPUnit tests
```xml
<phpunit>
    ...
    <php>
        <server name="LOCALEROUTER_PHPUNIT" value="true"/>
    </php>
    ...
</phpunit>
```

## Activate for a portion of tests / when you need it
```php
use PHPUnit\Framework\TestCase;
use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;

class Search extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(include 'config/application.config.php');
        $bootstrap      = \Zend\Mvc\Application::init(include 'config/application.config.php');
        $serviceManager = $bootstrap->getServiceManager();
    }

    public function testAction()
    {
        $_SERVER['LOCALEROUTER_PHPUNIT'] = true;
        
        // between this two lines you can do your locale aware tests. Keep in mind, that the $this->dispatch method does not work perfectly.
        // you can dispatch a controller manually though
        $myController = $this->serviceManager->get('ControllerManager')->get(MyController::class);
        
        // setup post parameters
        $params = new Parameters();
        $params->set('product', $productId);
        
        // setup post request
        $request = new Request();
        
        // you can - of course - also use a GET request or other request methods 
        $request->setMethod('POST');
        $request->setPost($params);
        
        $response = $myController->dispatch($request);
        
        $_SERVER['LOCALEROUTER_PHPUNIT'] = false;
    }
}
```