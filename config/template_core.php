<?php
// show error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// set your default time-zone
date_default_timezone_set(date_default_timezone_get());

$date = new DateTime();

// variables used for jwt
$key = "example_key";
$iss = "http://example.org";
$aud = "http://example.com";
$iat = $date->getTimestamp();
$nbf = $date->getTimestamp();
$exp = $date->add(new \DateInterval('PT3600S'))->getTimestamp();

$pepper = "example_pepper";
