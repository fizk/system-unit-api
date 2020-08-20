<?php

chdir(dirname(__DIR__));
require_once './vendor/autoload.php';

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\ServiceManager\ServiceManager;
use Unit\Application;

function exception_error_handler($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

// PUT data workaround
//  for some reason PHP doesn't have $_PUT; and $_POST doesn't contain PUT body
$putdata = fopen("php://input", "r");
$string = '';
while ($data = fread($putdata, 1024)) $string .= $data;
fclose($putdata);
mb_parse_str($string, $result);

$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $result, //$_POST,
    $_COOKIE,
    $_FILES
);

(new Application(
    new ServiceManager(require_once './config/service.php'),
    new SapiEmitter(),
    require_once './config/routes.php'
))->run($request);
