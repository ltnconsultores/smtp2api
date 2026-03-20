#!/usr/bin/php
<?php
require_once("config.php");

define("EX_OK",0);
define("EX_UNAVAILABLE",69);
define("EX_TEMPFAIL",75);

$saslUser = $argv[1];	// $argv[1] containss authenticated SASL user
// $argv[2] contains SMTP client IP
// $argv[3] contains body size

$stdin = fopen("php://stdin","r");
$rawbody = stream_get_contents($stdin);
fclose($stdin);

if (strlen($rawbody) < 64) {
	echo "Empty or too small message. Exiting gracefully" . PHP_EOL;
	eit(EX_OK);
}

if (APIMF_BACKEND == "MICROSOFT") {
	$ch = curl_init("https://login.microsoftonline.com/" . APIMF_MS_TENANTID . "/oauth2/v2.0/token");
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch,CURLOPT_POSTFIELDS,"client_id=" . APIMF_MS_CLIENTID . "&scope=https%3A%2F%2Fgraph.microsoft.com%2F.default&client_secret=" . APIMF_MS_CLIENTSECRET . "&grant_type=client_credentials");
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	$data = curl_exec($ch);
	$code = curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
	curl_close($ch);
	$data = json_decode($data);
	if (isset($data->error)) {
		echo "Graph Authentication Error: " . $data->error_description . PHP_EOL;
		exit(EX_TEMPFAIL);
	}
	$authToken = $data->access_token;
	unset($reply);

	$ch = curl_init("https://graph.microsoft.com/v1.0/users/{$saslUser}/sendMail");
	curl_setopt($ch,CURLOPT_POST,1);
	$eb = base64_encode($rawbody);
	curl_setopt($ch,CURLOPT_POSTFIELDS,$eb);
	$headers = [
		"Authorization: Bearer {$authToken}",
		"Content-Length: " . strlen($eb),
		"Content-Type: text/plain",
	];
	unset($eb);
	curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	$data = curl_exec($ch);
	$code = curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
	curl_close($ch);

	if ($code != 202) {
		echo "Failed to send message: {$code}: {$data}" . PHP_EOL;
		exit(APIMF_ONERROR_DEFER ? EX_TEMPFAIL : EX_OK);
	}

	exit(0);
} else if (APIMF_BACKEND == "GOOGLE") {
	$ch = curl_init("https://gmail.googleapis.com/gmail/v1/users/me/messages/send");
	curl_setopt($ch,CURLOPT_POST,1);
	$eb = '{"raw":"' . rtrim(strtr(base64_encode($rawbody),"+/","-_"),"=") . '"}';
	curl_setopt($ch,CURLOPT_POSTFIELDS,$eb);
	$headers = [
		"Authorization: Bearer " . APIMF_GOOGLE_AUTHTOKEN,
		"Content-Length: " . strlen($eb),
		"Content-Type: application/json",
	];
	unset($eb);
	curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	$data = curl_exec($ch);
	$code = curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
	curl_close($ch);

	if ($code != 200) {
		echo "Failed to send message: {$code}: {$data}" . PHP_EOL;
		exit(APIMF_ONERROR_DEFER ? EX_TEMPFAIL : EX_OK);
	}

	exit(0);
}

echo "Unsupported backend" . PHP_EOL;
exit(EX_OK);
