squirt
======

Simple and lightweight PHP dependency injection.

It is inspired by the ServiceBuilder in [Guzzle](https://github.com/guzzle/guzzle3),
but simplifies and improves upon that.  The ServiceBuilder was also removed in Guzzle 4.

Why squirt?
-----------

* Provides all the benefits of [dependency injection](https://en.wikipedia.org/wiki/Dependency_injection)
  via "constructor" injection
* Make unit testing easier/possible by permitting the injection of mock objects
* Separate configuration from code.  Squirt configuration files contain the configuration
  with details like database login details, connection timeouts, and other such parameters
  while your classes are kept easily reusable and configurable.
* Keep your code DRY.  Say you inject a common set of objects in multiple situations
  (e.g. a Logger and a Doctrine DBALConnection), squirt service extensions and config file inclusion
  let you keep that configuration in one place, instead of scattering it around the codebase.
  Configurations also cascade, permitting the setting of defaults at multiple levels.
* Uses "simple" PHP.  No annotations, no YAML/XML, no reflection.  Squirt config files
  are PHP files, so your IDE can already handle them, and opcode caches already optimize them.
  One can even use squirt-compatible classes without using squirt configurations/service builders,
  so that your squirt-compatible library can be used in frameworks that don't use squirt.
* Highly compatible.  If you use external libraries (and you should), but those libraries
  don't support squirt, it is very easy to write a wrapper class to add squirt support.

Basic Example
-------------

app_config.php - squirt config file

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

\* Note that there is no code in the configuration.  And configuration permits comments

MyApp\Logger.php - squirt-compatible wrapper for a Monolog Logger

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

MyApp\GuzzleClient - squirt-compatible wrapper for a Guzzle 4 Client

    namespace MyApp;

    use Squirt\Common\SquirtableInterface;
    use Squirt\Common\SquirtableTrait;
    use GuzzleHttp\Client;

    class GuzzleClient extends Client implements SquirtableInterface
    {
        use Squirt\Common\SquirtableTrait;
    }

MyApp\App.php - squirt-compatible end-user class

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
             * This is the equivalent of adding validation to:
             *
             * $this->logger = $params['logger'];
             * $this->client = $params['client'];
             * $this->url = $params['url'];
             */
            $this->logger = SquirtUtil::validateParamClass('logger', 'Monolog\Logger', $params);
            $this->client = SquirtUtil::validateParamClass('client', 'GuzzleHttp\Client', $params);
            $this->url = SquirtUtil::validateParam('url', $params);
        }

        public function run()
        {
            $response = $this->client->get($this->url);           

            $this->logger->info('Got result: ' . $response->getBody());
        }
    }

\* Note that there is no configuration in the code

run.php - normal squirt service-consuming script

    use Squirt\ServiceBuilder\SquirtServiceBuilder;

    require 'vendor/autoload.php'; // Composer class autoloader

    $squirtServiceBuilder = SquirtServiceBuilder::factory(array(
        'fileName' => 'app_config.php'
    ));

    $app = $squirtServiceBuilder->get('APP');

    $app->run();

run_nonsquirt.php - squirt-compatible classes can be run even without squirt if necessary

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

