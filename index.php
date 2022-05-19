<?php
define('ROOT', __DIR__);
define('CONFIG_PATH', ROOT . '/config');

require_once CONFIG_PATH . '/url.php';
require_once './vendor/autoload.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\HttpKernel;

$locator = new FileLocator([CONFIG_PATH]);
$loader = new YamlFileLoader($locator);
$routes = $loader->load('routes.yml');

$context = new RequestContext();
$request = Request::createFromGlobals();
$context->fromRequest($request);
$matcher = new UrlMatcher($routes, $context);
$controllerResolver = new HttpKernel\Controller\ControllerResolver();
$argumentResolver = new HttpKernel\Controller\ArgumentResolver();

try {
	$matcher = $matcher->match($request->getPathInfo());
	$request->attributes->add($matcher);

	$controller = $controllerResolver->getController($request);
	$arguments = $argumentResolver->getArguments($request, $controller);

	call_user_func_array($controller, $arguments);
} catch (\Exception $e) {
	$encoder = new XmlEncoder();
	$response = new Response(
		$encoder->encode(['status' => 'error', 'code' => Response::HTTP_NOT_FOUND], 'xml'),
		Response::HTTP_NOT_FOUND
	);
	$response->headers->set('Content-Type', 'text/xml');
	$response->send();
}