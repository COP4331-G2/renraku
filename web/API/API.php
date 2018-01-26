<?php

require 'Connection.php';

establishConnection();

/**
 * Retrieve JSON and establish MySQL connection
 */
function establishConnection()
{
    // Receive decoded JSON info from client
    $inData = getRequestInfo();

    // Get MySQL authentication information via a secrets file
    $secrets = readSecrets();

    // Establish MySQL database connection
    $conn = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

    // Unicorn Magic
    // Use the JSON payload 'function' field to call a PHP function
    $function = $inData['function'];
    if (is_callable($function)) {
        $function($conn, $inData);
    } else {
        returnWithError("JSON payload tried to call undefined PHP function $function()");
    }

    // Ensure that the connection is closed
    $conn->close();
}

/**
 * Verify username/password information and (perhaps) login to a user's account
 *
 * @param mysqli $conn MySQL connection instance
 * @param object $inData Decoded JSON stdClass object
 *
 */
function loginAttempt($conn, $inData)
{
    if ($conn->connect_error) {
        returnWithError($conn->connect_error);
    } else {
        $username = protectInjection($inData['username']);
        $password = $inData['password'];

        $result = $conn->query("SELECT * FROM Users WHERE username='$username'");

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                returnWithInfo($row['id']);
            } else {
                returnWithError("Password incorrect");
            }
        } else {
            returnWithError("Username not found");
        }
    }
}

/**
 * Create a new user account
 *
 * @param mysqli $conn MySQL connection instance
 * @param object $inData Decoded JSON stdClass object
 *
 */
function createUser($conn, $inData)
{
    $username = protectInjection($inData["username"]);
    $password = $inData["password"];

    if ($conn->connect_error) {
        returnWithError($conn->connect_error);
    } else if (strlen($username) > 60) {
        returnWithError("Username needs to be equal to or less than 60 characters.");
    } else if (strlen($username) <= 0) {
        returnWithError("Username cannot be empty.");
    } else if (strlen($password) <= 0) {
        returnWithError("Password cannot be empty.");
    }

    $result = $conn->query("SELECT username FROM Users WHERE username='$username'");

    if ($result->num_rows != 0) {
        returnWithError("Username already exists!");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $result = $conn->query("INSERT INTO Users (username, password) VALUES ('$username', '$hashedPassword')");

    if ($result) {
        returnWithError("");
    } else {
        returnWithError($conn->error);
    }
}

/**
 * Delete a user account
 */
function deleteUser()
{
    // Not yet implemented
}

/**
 * Add a contact to a user's account
 *
 * @param mysqli $conn MySQL connection instance
 * @param object $inData Decoded JSON stdClass object
 *
 */
function addContact($conn, $inData)
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

/**
 * Delete a contact from a user's account
 *
 * @param mysqli $conn MySQL connection instance
 * @param object $inData Decoded JSON stdClass object
 *
 */
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

/**
 * Get all contacts from a user's account foratted as a JSON string
 *
 * @param mysqli $conn MySQL connection instance
 * @param object $inData Decoded JSON stdClass object
 *
 */
function getContacts($conn, $inData)
{
    $userID = $inData['userID'];

    if ($conn->connect_error) {
        returnWithError($conn->connect_error);
    } else {
        $result        = $conn->query("SELECT * FROM Contacts WHERE userID=$userID");
        $count         = 0;
        $searchResults = [];

        // Iterate through all found contacts to setup their information
        while ($row = $result->fetch_assoc()) {
            // Column information for Contacts
            $array = [
                'contactId'    => $row['id'],
                'firstName'    => $row['firstName'],
                'lastName'     => $row['lastName'],
                'phoneNumber'  => $row['phoneNumber'],
                'emailAddress' => $row['emailAddress'],
            ];

            // Append this information to the searchResults array
            $searchResults[] = $array;
        }

        // Return the built searchResults array
        returnWithInfo($searchResults);
    }
}

/**
 * Remove single-quotes and semicolons from a string to protect against SQL injection
 *
 * @param string $string A SQL-formatted string
 *
 * @return string
 */
function protectInjection($string)
{
    $result  = str_replace("'", "", $string);
    $result2 = str_replace(";", "", $result);
    return trim($result2);
}
