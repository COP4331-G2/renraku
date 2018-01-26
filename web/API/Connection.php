<?php

/**
 * Reads MySQL database login information through a 'secrets' file
 *
 *  @return array (array containing database login information)
 */
function readSecrets()
{
    $secretsFile = fopen("../secrets", "r");

    while (!feof($secretsFile)) {
        $secretsString = fgets($secretsFile);
    }

    fclose($secretsFile);

    $secretsArray = explode(",", $secretsString);

    $secrets['host']     = $secretsArray[0];
    $secrets['username'] = $secretsArray[1];
    $secrets['passwd']   = $secretsArray[2];
    $secrets['dbname']   = $secretsArray[3];

    return $secrets;
}

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($json)
{
    header('Content-type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, origin');
    echo $json;
}

function returnWithError($error)
{
    $json = json_encode(['error' => $error]);
    sendResultInfoAsJson($json);
}

function returnWithInfo($searchResults)
{
    $json = json_encode(['results' => $searchResults]);
    sendResultInfoAsJson($json);
}
