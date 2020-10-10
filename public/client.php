<?php
require '../bootstrap.php';

$client       = getenv('OKTACLIENTID');
$clientSecret = getenv('OKTASECRET');
$scope        = getenv('SCOPE');
$issuer       = getenv('OKTAISSUER');

//Obtain an access token
$token = obtainToken($issuer, $client, $clientSecret, $scope);

//test requests
getAllUsers($token);
getUser($token, 1);

//end of client.php flow

function obtainToken($issuer, $clientId, $clientSecret, $scope) {
	echo "Obtaining required token...";

	//prepare the request
	$uri = $issuer . '/v1/token';
	$token = base64_encode("$clientId:$clientSecret");
	$payload = http_build_query([
		'grant_type' => 'client_credentials',
		'scope' => $scope
	]);

	//build the curl request
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $uri);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/x-www-form-urlencoded',
		"Authorization: Basic $token"
	]);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	//process and return the response
	$response = curl_exec($ch);
	$response = json_decode($response, true);
	if (!isset($response['access_token']) || !isset($response['token_type'])) {
		exit('Operation failed, exiting.');
	}

	echo "Success!\n";
	return $response['token_type'] . " " . $response['access_token'];
}

function getAllUsers($token) {
	echo "Preparing to get all users...";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8000/person");
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		"Authorization: $token"
	]);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);

	var_dump($response);
}

function getUser($token, $id) {
	echo "Preparing to get user with id = $id";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8000/person" . $id);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		"Authorization: $token"
	]);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);

	var_dump($response);
}