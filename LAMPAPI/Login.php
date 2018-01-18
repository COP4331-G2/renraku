<?php

$inData = getRequestInfo();

$id        = 0;
$firstName = "";
$lastName  = "";

$conn = new mysqli("mydb.c17vnanzumzs.us-east-1.rds.amazonaws.com", "root", "mypassword", "mydb");
if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    $sql    = "SELECT ID,firstName,lastName FROM Users where Login='" . $inData["login"] . "' and Password='" . $inData["password"] . "'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row       = $result->fetch_assoc();
        $firstName = $row["firstName"];
        $lastName  = $row["lastName"];
        $id        = $row["ID"];
    } else {
        returnWithError("No Records Found");
        $conn->close();
        die;
    }
    $conn->close();
}

returnWithInfo($firstName, $lastName, $id);

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj)
{
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err)
{
    $retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($firstName, $lastName, $id)
{
    $retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
    sendResultInfoAsJson($retValue);
}
