<?php

require '../bootstrap.php';
use Src\Controller\PersonController;

header("Access-Control-Allow-Origin: *");
header("Content-type: Application/json; charset=UTF-8");
header("Access-Control-Allow-Method: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Header: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

/*
All of the endpoints start with /person
Everything else results in a  404 Not Found
*/

if ($uri[1] !== 'person') {
	header("HTTP/1.1 404 Not Found");
	exit();
}

/*The user id is, of course, optional and must be a number*/

$userId = null;
	if (isset($uri[2])) {
		$userId = (int) $uri[2];
	}

	$requestMethod = $_SERVER["REQUEST_METHOD"];

/*Pass the request method and user ID to the PersonController and process the HTTP rquest*/

$controller = new PersonController($dbConnection, $requestMethod, $userId);
$controller->processRequest();	

function authenticate() {
	try {
		switch(true) {
			case array_key_exists('HTTP_AUTHORIZATION', $_SERVER) :
				$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
				break;
			case array_key_exists('Authorization', $_SERVER) :
				$authHeader = $_SERVER['Authorization'];
				break;
			default:
				$authHeader = null;
				break;
		}
		preg_match('/Bearer\s(\S+)/', $authHeader, $matches);
		if (!isset($matches[1])) {
			throw new \Exception('No Bearer Token');
		}
		$jwtVerifier = (new \Okta\JwtVerifier\JwtVerifierBuilder())
			->setIssuer(getenv('OKTAISSUER'))
			->setAudience('api://default')
			->setClientId(getenv('OKTACLIENTID'))
			->build();
		return $jwtVerifier->verify($matches[1]);
	} catch (Exception $e) {
		return false;
	}
}