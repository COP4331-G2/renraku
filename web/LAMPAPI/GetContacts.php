<?php

require 'Connection.php';

$inData = getRequestInfo();

$secrets = readSecrets();
$conn    = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

getContacts($conn, $inData);

$conn->close();

function getContacts($conn, $inData)
{
    $userID = $inData['userID'];

    if ($conn->connect_error) {
        returnWithError($conn->connect_error);
    } else {
        $result        = $conn->query("SELECT * FROM Contacts WHERE userID=$userID");
        $count         = 0;
        $searchResults = "";

        while ($row = $result->fetch_assoc()) {
            if ($count > 0) {
                $searchResults .= ",";
            }
            $count++;

            // Column information for Contacts
            $id           = $row['id'];
            $firstName    = $row['firstName'];
            $lastName     = $row['lastName'];
            $phoneNumber  = $row['phoneNumber'];
            $emailAddress = $row['emailAddress'];

            $searchResults .= '{"contactID":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","phoneNumber":' . $phoneNumber . ',"emailAddress":"' . $emailAddress . '"}';
        }

        returnWithInfo($searchResults);
    }
}

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj)
{
    header('Content-type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, origin');
    echo $obj;
}

function returnWithError($err)
{
    $retValue = '{"error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($searchResults)
{
    $retValue = '{"results":[' . $searchResults . '],"error":""}';
    sendResultInfoAsJson($retValue);
}
