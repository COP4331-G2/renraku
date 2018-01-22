<?php
	$inData = getRequestInfo();
	
	$firstName = $inData["firstName"];
	$lasttName = $inData["lastName"];
	$phoneNumber = $inData["phoneNumber"];
	$emailAdress = $inData["emailAdress"];
	$userId = $inData["userId"];

	$conn = new mysqli("small.c17vnanzumzs.us-east-1.rds.amazonaws.com", "root", "mypassword", "small");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{   
		
		$sql = "insert into Contacts (firstName,lastName,phoneNumber,emailAdress,UserId) VALUES ('" . $firstName .  "','" . $lasttName . "','" . $phoneNumber . "','" . $emailAdress . "'," . $userId . ")";
		if( $result = $conn->query($sql) != TRUE )
		{
			returnWithError( $conn->error );
		}
		$conn->close();
	}
	
	returnWithError("");
	
	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
?>