<?php
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;

// Set default timezone.
date_default_timezone_set('Europe/Amsterdam');

// Try/catch construction.
try {

    define('BASE_PATH', dirname(__DIR__));
    define('APP_PATH', BASE_PATH . '/apps');
    define('PROJECT_PATH', __DIR__ . '/../');

    $env = getenv('ENVIRONMENT');

    $di = new FactoryDefault();

    switch($env){
        case "production" :
            $config = include APP_PATH . "/../apps/config/config.production.php";
            //error_reporting(0);
            break;
        default :
            $config = include APP_PATH . "/../apps/config/config.development.php";
            //error_reporting(E_ALL);
            break;
    }

    include __DIR__ . "/../apps/config/services.php";
    include __DIR__ . "/../apps/config/loader.php";
    require __DIR__ . '/../vendor/autoload.php';

    // Handle the request.
    $application = new \Phalcon\Mvc\Application($di);

    // Register the available modules
    $application->registerModules(
        array(
            'votingfrontend' => array(
                'className' => 'EunoVoting\VotingFrontend\Module',
                'path' => '../apps/votingfrontend/Module.php',
            ),
            'votingbackend' => array(
                'className' => 'EunoVoting\VotingBackend\Module',
                'path' => '../apps/votingbackend/Module.php',
            ),
            'signup' => array(
                'className' => 'EunoVoting\Signup\Module',
                'path' => '../apps/signup/Module.php',
            )
        )
    );

    // Echo result.
    echo $application->handle()->getContent();

    exit;

// When an error occurred.
} catch (\Phalcon\Exception $e) {

    if($env !== 'production') {
        // When visitor is administrator/debugger.
        // if (in_array($_SERVER['REMOTE_ADDR'], explode(',', $config->debug->ipaddresses))) {
        echo 'PhalconException: ' . $e->getMessage();
        exit();
        // }
    }

    // Forward to 404 error message page.
    //header('Location: /index/notFound');
    exit;
}

/*use Phalcon\Di\FactoryDefault;

error_reporting(E_ALL);
ini_set("memory_limit","512M");

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PROJECT_PATH', __DIR__ . '/../');

try {

    $di = new FactoryDefault();

    include APP_PATH . "/config/services.php";

    $config = $di->getConfig();

    include APP_PATH . '/config/loader.php';

    require BASE_PATH . "/vendor/autoload.php";

    $application = new \Phalcon\Mvc\Application($di);

    echo $application->handle()->getContent();

} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}*/
