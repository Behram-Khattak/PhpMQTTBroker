<?php

require('vendor/autoload.php');

use \PhpMqtt\Client\MqttClient;
use  \PhpMqtt\Client\ConnectionSettings;

/**
 * This function is used to generate a unique identifier,
 * each time this code is executed.
 */
function generateUuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

$server   = getenv('MQTT_HOST_URL');
// TLS port
$port     = getenv('MQTT_TLS_PORT');
$clientId = generateUuid();
$username = getenv('MQTT_WEB_CLIENT_USERNAME');
$password = getenv('MQTT_WEB_CLIENT_PASSWORD');
$clean_session = false;

$connectionSettings  = (new ConnectionSettings)
  ->setUsername($username)
  ->setPassword($password)
  ->setKeepAliveInterval(60)
  ->setConnectTimeout(3)
  ->setUseTls(true)
  ->setTlsSelfSignedAllowed(true);

$mqtt = new MqttClient($server, $port, $clientId, MqttClient::MQTT_3_1_1);
$mqtt->connect($connectionSettings, $clean_session);


$mqtt->subscribe('php/mqtt', function ($topic, $message) {
    printf("Received message on topic [%s]: %s\n", $topic, $message);
}, 0);

$payload = array(
    'from' => 'php-mqtt client',
    'message' => 'Hello MQTT !',
    'date' => date('Y-m-d H:i:s')
  );

$mqtt->publish('php/mqtt', json_encode($payload), 0);

$mqtt->loop(true);
  