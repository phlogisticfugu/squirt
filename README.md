squirt
======

Simple and lightweight PHP dependency injection.

It is inspired by the ServiceBuilder in [Guzzle 3](https://github.com/guzzle/guzzle3),
but simplifies and expands upon that.

see the [[wiki|Home]]

Why squirt?
-----------

* Provides all the benefits of [dependency injection](https://en.wikipedia.org/wiki/Dependency_injection)
  via "constructor" like injection
* Make unit testing easier/possible by permitting the injection of mock objects
* Separate configuration from code.  Squirt configuration files contain the configuration
  with details like database logins, connection timeouts, and which classes to instantiate,
  while your code itself is kept easily reusable and configurable.
* Keep your code DRY.  Say you inject a common set of objects in multiple situations
  (e.g. a Logger and a Doctrine DBALConnection), squirt service extensions and config file inclusion
  let you keep that configuration in one place, instead of scattering it around the codebase.
  Configurations also cascade, permitting the setting of defaults at multiple levels.
* Designed for performance.  Squirt config files are written in PHP, so opcode caches
  already optimize them.  Squirt also supports Doctrine caches on the entire configuration
  (making use of the fact that configuration is pure data, with no code).
* Designed for compatibility.  If you use external libraries (and you should), it is very easy to
  write a wrapper class to add squirt support.
  You can even use squirt-compatible classes without the squirt service builder;
  so a squirt-compatible class can be used in frameworks that don't use squirt.

Basic Example
-------------

*app_config.php* - squirt config file

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

\* Note that there is no code in the configuration, so it can be cached and stored as data.
  And configuration permits normal PHP comments to provide clarity when needed.

*MyApp/App.php* - squirt-compatible end-user class

    namespace MyApp;
    
    use Squirt\Common\SquirtableInterface;
    use Squirt\Common\SquirtableTrait;
    use Squirt\Common\SquirtUtil;
 
    class App implements SquirtableInterface
    {
        use SquirtableTrait;

        private $logger;

        private $client;

        private $url;

        protected function __construct(array $params)
        {
            /*
             * Validate values from the $params and set them in our instance.
             * Using squirt utility functions that assist with this common task
             * and throw an InvalidArgumentException when there's a problem.
             *
             * This is the equivalent of adding validation to:
             * $this->logger = $params['logger'];
             * $this->client = $params['client'];
             * $this->url = $params['url'];
             */
            $this->logger =
                SquirtUtil::validateParamClass('logger', 'Monolog\Logger', $params);

            $this->client =
                SquirtUtil::validateParamClass('client', 'GuzzleHttp\Client', $params);

            $this->url = SquirtUtil::validateParam('url', $params);
        }

        public function run()
        {
            $response = $this->client->get($this->url);           

            $this->logger->info('Got result: ' . $response->getBody());
        }
    }

\* Note that there is no configuration in the code, for proper separation

*MyApp/Logger.php* - squirt-compatible wrapper for a Monolog Logger

    namespace MyApp;

    use Squirt\Common\SquirtableInterface;
    use Squirt\Common\SquirtUtil;
    use Monolog\Logger as MonologLogger;
    use Monolog\Handler\StreamHandler;

    class Logger extends MonologLogger implements SquirtableInterface
    {
        public static function factory(array $params=array())
        {
            $logFile = SquirtUtil::validateParam('logFile', $params);

            $instance = new static();
            $instance->pushHandler(new StreamHandler($logFile));

            return $instance;
        }
    }

*MyApp/GuzzleClient.php* - squirt-compatible wrapper for a Guzzle 4 Client

    namespace MyApp;

    use Squirt\Common\SquirtableInterface;
    use Squirt\Common\SquirtableTrait;
    use GuzzleHttp\Client;

    class GuzzleClient extends Client implements SquirtableInterface
    {
        /*
         * Squirt provides traits to help with common cases for making
         * squirt-compatible wrapper classes
         */
        use SquirtableTrait;
    }

*run.php* - normal squirt service-consuming script

    use Squirt\ServiceBuilder\SquirtServiceBuilder;

    require 'vendor/autoload.php'; // Composer class autoloader

    $squirtServiceBuilder = SquirtServiceBuilder::factory(array(
        'fileName' => 'app_config.php'
    ));

    $app = $squirtServiceBuilder->get('APP');

    $app->run();

*run_nonsquirt.php* - squirt-compatible classes can be run even without squirt if necessary

    use MyApp\App;
    use MyApp\Logger;
    use MyApp\GuzzleClient;

    require 'vendor/autoload.php'; // Composer class autoloader

    // Classes can be used even without squirt
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

Squirt services can also extend one another, to permit configuration re-use
and a cascade of defaults in a sensible manner.

example:

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

\* Note that squirt supports deep overrides for configuration parameters

Finally, the consuming script can override anything set in the configurations
with any additional overrides needed, perhaps to aid in some debugging or other
configuration.

example:

    $amazonHttpClient = $squirtServiceBuilder->get('AMAZON_HTTP_CLIENT', array(
        'http_options' => array(
            'timeout' => 90
        )
    ));

\* Note that squirt-configured services are normally cached so they behave like singletons,
preventing unecessary instantiation.  However, providing override parameters to the `get()` method
disables that caching.  One can also disable caching via `get($serviceName,null,false)`.

### Config file prefix

As configuration files get larger and more complex, it may be useful to separate them into sets
each with a given naming convention, so that it is easy to know which squirt service is configured
in which configuration file, and to avoid name collisions.  Squirt configuration files support
a prefix parameter, which is added to all services defined in that file.

example:

    return array(
        'prefix' => 'ADMIN',
        'services' => array(
            'APP' => array( ... ),
            'LOADER' => array( ... )
        )
    );

behaves the same as if one had instead used:

    return array(
        'services' => array(
            'ADMIN.APP' => array( ... ),
            'ADMIN.LOADER' => array( ... )
        )
    );

