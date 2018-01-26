<?php

require 'Connection.php';

$inData = getRequestInfo();

$secrets = readSecrets();
$conn    = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

insertContact($conn, $inData);

$conn->close();

function insertContact($conn, $inData)
{
    $firstName    = $inData["firstName"];
    $lasttName    = $inData["lastName"];
    $phoneNumber  = $inData["phoneNumber"];
    $emailAddress = $inData["emailAddress"];
    $userID       = $inData["userID"];

    if ($conn->connect_error) {
        returnWithError($conn->connect_error);
    } else {
        $result = $conn->query("INSERT INTO Contacts (firstName, lastName, phoneNumber, emailAddress, userID) VALUES ('$firstName', '$lastName', '$phoneNumber', '$emailAddress', '$userID')");

        if (!$result) {
            returnWithError($conn->error);
        } else {
            returnWithError("");
        }
    }
}
