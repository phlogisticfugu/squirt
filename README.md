squirt
======

Simple and lightweight PHP dependency injection with parameter overrides and more.

It is inspired by the ServiceBuilder in [Guzzle 3](https://github.com/guzzle/guzzle3),
but simplifies and expands upon that.

see the [wiki](https://github.com/phlogisticfugu/squirt/wiki)

Why squirt?
-----------

* Provides all the benefits of [dependency injection](https://en.wikipedia.org/wiki/Dependency_injection)
* Separate configuration from code.  Unlike most DI frameworks, which use a container object
  and methods on it when doing configuration; all Squirt configuration is pure data.  This means
  that it can be manipulated/merged like data, and there is no global object in the configuration.
  Also, the factory code that constructs and configures instances of services is decoupled from
  the configuration parameters, providing better testability and code reuse.
* Keep your code DRY.  Service configurations can extend each other, to reduce repetition.
  Dependencies are automatically and recursively injected by name.
* Supports three modes of injected parameter overrides:
  * Service configurations can extend and override each other, providing shared default parameters.
    One can also override the instantiated class with a subclass, if needed.
  * Configuration files can include and override one another
    * organize your configuration, separate out related services
    * make integration tests easy: include the production configuration and only override
      what you need to.
  * End user code can provide selective overrides at instantiation time, to aid in ad-hoc configuration
    for testing (great for quick debug flags) and troubleshooting.
* Make unit testing easier/possible.  Mock objects can be injected into instances when unit testing.
  And configuration file overrides simplify integration tests.
* Designed for simplicity.  Injected parameters include both injected objects and injected configuration
  values in a natural manner.  There's only one method to learn: `$squirtServiceBuilder->get()`, as opposed
  to all the methods in most dependency injection containers.  There are no annotations to learn, and no
  XML or YAML.
* Designed for performance.  Squirt config files are written in PHP, so opcode caches
  already optimize them.  Squirt also supports [Doctrine caches](http://docs.doctrine-project.org/en/2.0.x/reference/caching.html)
  on the entire configuration
* Designed for compatibility.  If you use external libraries (and you should), it is very easy to
  write a wrapper class to add squirt support.  All that is needed for Squirt compatibility is a
  static factory() function which takes in an array of parameters (including injected dependencies and configuration values) and
  returns an instance.
  * Amazon's Guzzle3-based [AWS-PHP-SDK](http://docs.aws.amazon.com/aws-sdk-php/guide/latest/index.html) is
    already compatible.

Basic Example
-------------

*app_config.php* - squirt config file

```php
return array(
    'services' => array(
        'LOGGER' => array(
            'class' => 'MyApp\Logger',
            'params' => array(
                'logFile' => '/var/log/app.log'
            )
        ),
        'GUZZLE_CLIENT' => array(
            'class' => 'MyApp\GuzzleClient'
        ),
        'APP' => array(
            'class' => 'MyApp\App',
            'params' => array(
                'logger' => '{LOGGER}',
                'client' => '{GUZZLE_CLIENT}',
                'url' => 'https://github.com'
            )
        )
    )
);
```

\* Note that this is all that is needed to define how an application is wired up.
  There's no DI Container and new methods to learn.  Note also that injected configuration
  values, like the log file location, are represented naturally alongside injected services.

*MyApp/App.php* - squirt-compatible end-user class

```php
namespace MyApp;

use Monlog\Logger;
use GuzzleHttp\Client;

class App
{
    private $logger;

    private $client;

    private $url;

    public static function factory(array $params=array())
    {
        return new static($params);
    }

    protected function __construct(array $params)
    {
        /*
         * Read in and validate all of our injected dependencies
         * Note that the Squirt\Common\SquirtUtil class contains helper functions
         * which can reduce the repetition below.
         */

        if (isset($params['logger']) && ($params['logger'] instanceof Logger)) {
            $this->logger = $params['logger'];
        } else {
            throw new \InvalidArgumentException('Invalid or missing logger');
        }

        if (isset($params['client']) && ($params['client'] instanceof Client)) {
            $this->client = $params['client'];
        } else {
            throw new \InvalidArgumentException('Invalid or missing client');
        }

        if (! empty($params['url'])) {
            $this->url = $params['url'];
        } else {
            throw new \InvalidArgumentException('Missing url');
        }
    }

    public function run()
    {
        $response = $this->client->get($this->url);

        $this->logger->info('Got result: ' . $response->getBody());
    }
}
```

\* Note that there is no configuration in the code, for proper separation

*MyApp/Logger.php* - squirt-compatible wrapper for a Monolog Logger

```php
namespace MyApp;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class Logger extends MonologLogger
{
    public static function factory(array $params=array())
    {
        $logFile = $params['logFile'];

        $instance = new static();
        $instance->pushHandler(new StreamHandler($logFile));

        return $instance;
    }
}
```

*MyApp/GuzzleClient.php* - squirt-compatible wrapper for a Guzzle 4 Client

```php
namespace MyApp;

use GuzzleHttp\Client;

class GuzzleClient extends Client
{
    public static function factory(array $params=array())
    {
        return new static($params);
    }
}
```

*run.php* - normal squirt service-consuming script

```php
use Squirt\ServiceBuilder\SquirtServiceBuilder;

require 'vendor/autoload.php'; // Composer class autoloader

$squirtServiceBuilder = SquirtServiceBuilder::factory(array(
    'fileName' => 'app_config.php'
));

// Note that only one service needs to be requested.  All required dependencies
// are lazily created and injected.
$app = $squirtServiceBuilder->get('APP');

$app->run();
```

*run_nonsquirt.php* - This illustrates what Squirt is doing under the hood.

```php
use MyApp\App;
use MyApp\Logger;
use MyApp\GuzzleClient;

require 'vendor/autoload.php'; // Composer class autoloader

$logger = Logger::factory(array(
    'logFile' => '/var/log/app.log'
));

$client = GuzzleClient::factory();

$app = App::factory(array(
    'logger' => $logger,
    'client' => $client,
    'url' => 'https://github.com'
));

$app->run();
```

Installation
------------

Install squirt using [composer](https://getcomposer.org/).  Create a file named composer.json

    {
        "require": {
            "phlogisticfugu/squirt": "~1.0"
        }
    }

then follow the installation instructions for composer.

Features
--------

### Config file inclusion, service extension, and overrides

As one uses squirt in a complex application, the configuration files will
naturally get larger as more services are configured.  To aid in organizing
those files, configuration files may include one another.

example:

```php
return array(
    'includes' => array(
        'aws_config.php',
        'database_config.php',
        'production_logger_config.php'
    ),
    'services' => array(
        // service definitions which depend on services defined elsewhere
    )
);
```

Squirt services can also extend one another, to permit configuration re-use
and a cascade of defaults in a sensible manner.

example:

```php
return array(
    'includes' => array(
        'production_logger_config.php'
    ),
    'services' => array(
        'ABSTRACT_HTTP_CLIENT' => array(
            'class' => 'MyApp\HttpClient',
            'params' => array(
                'logger' => '{LOGGER}',
                'http_options' => array(
                    'timeout' => 10
                )
            )
        ),
        'GITHUB_HTTP_CLIENT' => array(
            'extends' => 'ABSTRACT_HTTP_CLIENT',
            'params' => array(
                'url' => 'https://github.com'
            )
        ),
        'AMAZON_HTTP_CLIENT' => array(
            'extends' => 'ABSTRACT_HTTP_CLIENT',
            'params' => array(
                'url' => 'https://www.amazon.com',
                'http_options' => array(
                    // overrides value from ABSTRACT_HTTP_CLIENT
                    'timeout' => 60
                )
            )
        )
    )
);
```

\* Note that squirt supports deep overrides for configuration parameters

Finally, the consuming script can override anything set in the configurations
with any additional overrides needed, perhaps to aid in some debugging or other
configuration.

example:

```php
$amazonHttpClient = $squirtServiceBuilder->get('AMAZON_HTTP_CLIENT', array(
    'http_options' => array(
        'timeout' => 90
    )
));
```

\* Note that squirt-configured services are normally cached so they behave like singletons,
preventing unecessary instantiation.  However, providing override parameters to the `get()` method
disables that caching.  One can also disable caching via `get($serviceName,null,false)`.



