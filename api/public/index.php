<?php

use Avolle\Banebok\App;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Whoops\Handler\CallbackHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__) . DS);
define('LOGS', ROOT . 'logs' . DS);
define('TMP', ROOT . 'tmp' . DS);

$config = require ROOT . 'config' . DS . 'config.php';
if ($config['debug']) {
    require ROOT . 'cors.php';
}

require ROOT . 'utils.php';
require ROOT . 'vendor/autoload.php';

if (!setlocale(LC_ALL, 'nb_NO')) {
    setlocale(LC_ALL, 'nb');
}
date_default_timezone_set('Europe/Oslo');

$log = new Logger('error');
$log->pushHandler(new StreamHandler(LOGS . 'error.log', Level::Error));
$whoops = new Run();

if ($config['debug']) {
    $handler = new PrettyPageHandler();
} else {
    $handler = new CallbackHandler(function (Exception|Error $exception) use ($log, $whoops) {
        $log->error($exception->getMessage() . "\n" . getExceptionTraceAsString($exception));
        header('HTTP/1.1 500 Internal Error');
        echo json_encode([
            'error' => 'Noe gikk galt',
            'code' => $whoops->sendHttpCode(500),
        ]);
    });
}

$whoops->pushHandler($handler);
$whoops->register();

$app = new App();
$app->run();
