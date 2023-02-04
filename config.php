<?php
require_once __DIR__ . '/vendor/autoload.php';
error_reporting(E_ERROR);

define('API_URL', 'https://api.hubapi.com/');
define('API_TOKEN', 'xxx');

$client = new \Google_Client();
$client->setApplicationName('Google Sheets API');
$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType('offline');
// credentials.json is the key file we downloaded while setting up our Google Sheets API
$path = __DIR__ . '/credentials.json';
$client->setAuthConfig($path);
$service = new \Google_Service_Sheets($client);

function doCurl($endpoint, $params = [])
{

    $curl = curl_init();

    $fp = fopen(__DIR__ . '/errorlog.txt', 'w');

    $config_arr = [
        CURLOPT_URL => API_URL . $endpoint,
        // 
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_VERBOSE => 1,
        CURLOPT_STDERR => $fp,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . API_TOKEN,
            'Accept: application/json'
        ),
        //CURLOPT_AUTOREFERER => TRUE,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
    ];

    if (count($params) > 0) {
        $config_arr[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
        $config_arr[CURLOPT_POSTFIELDS] = json_encode($params);
        $config_arr[CURLOPT_POST] = 1;
    } else {
        $config_arr[CURLOPT_CUSTOMREQUEST] = 'GET';
    }

    curl_setopt_array($curl, $config_arr);

    $response = curl_exec($curl);
    echo $response;
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    $response = json_decode($response, true);
    //var_dump($response);
    return $response;

}

function logg($msg, $file = 'out.log')
{
    if ((is_array($msg) || is_object($msg)) && !is_string($msg)) {
        $msg = json_encode($msg);
    }
    $str = sprintf("[%s] %s \n", date('Y-m-d H:i:s'), $msg);
    file_put_contents('./' . $file, $str, FILE_APPEND);
    echo $str;
}

?>