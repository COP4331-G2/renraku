<?php

require 'Connection.php';

$inData = getRequestInfo();

$secrets = readSecrets();
$conn    = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

deleteContact($conn, $inData);

$conn->close();

function deleteContact($conn, $inData)
{
    $contactID = $inData["id"];

    $userID = $inData["userID"];

    if ($conn->connect_error) {
        returnWithError($conn->connect_error);
    } else {
        $result = $conn->query("DELETE FROM CONTACTS WHERE id='$contactID'");

        if (!$result) {
            returnWithError($conn->error);
        }
    }

    returnWithError("");
}
