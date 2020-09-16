<?php

chdir(dirname(__DIR__));
require_once './vendor/autoload.php';

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\ServiceManager\ServiceManager;
use Unit\Application;

set_error_handler("exception_error_handler");

// read stdin and parse it as form-data, because
// PHP can't do that with put/patch requests :(
mb_parse_str(read_resource(fopen("php://input", "r")), $bodyQuery);

// Create a PSR-7 message out of the incoming CGI data
// everything comes from the http-server, except the
// post/put/patch data, which has to be read from stdin
$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    array_map_recursive('type_cast', $bodyQuery), //$_POST
    $_COOKIE,
    $_FILES
);

// Fetch service- and router config and pass that to the
// 'application', that will run the API
(new Application(
    new ServiceManager(require_once './config/service.php'),
    new SapiEmitter(),
    require_once './config/routes.php'
))->run($request);


function exception_error_handler($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}

function array_map_recursive(callable $callback, array $array)
{
    $func = function ($item) use (&$func, &$callback) {
        return is_array($item) ? array_map($func, $item) : call_user_func($callback, $item);
    };

    return array_map($func, $array);
}

function type_cast ($i)
{
    if (is_numeric($i)) return $i + 0;
    if (strtolower($i) === 'true') return true;
    if (strtolower($i) === 'false') return false;
    if (strtolower($i) === 'null') return null;
    return $i;
}

function read_resource (/*resource*/$resource): string
{
    $result = '';
    while ($data = fread($resource, 1024)) $result .= $data;
    fclose($resource);

    return $result;
}
