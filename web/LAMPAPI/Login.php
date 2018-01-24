<?php
	$inData = getRequestInfo();
	$id        = 0;

	$secrets = readSecrets();
	$conn = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

	if ($conn->connect_error) 
	{
		returnWithError($conn->connect_error);	
	}
	else 
	{
		$sql    = "SELECT id FROM Users where username='" . protectInjection($inData["login"]) . "' and password='" . $inData["password"] . "'";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) 
		{
			$row = $result->fetch_assoc();
			$id = $row["id"];
			returnWithInfo($id);
		} 
		else 
		{
			returnWithError("No Records Found");
		}		
	}
	$conn->close();


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

		$secrets['host'] = $secretsArray[0];
		$secrets['username'] = $secretsArray[1];
		$secrets['passwd'] = $secretsArray[2];
		$secrets['dbname'] = $secretsArray[3];

		return $secrets;
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
		$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson($retValue);
	}

	function returnWithInfo($id)
	{
		$retValue = '{"id":' . $id . '}';
		sendResultInfoAsJson($retValue);
	}

	function protectInjection($string)
	{
		$result = str_replace("'", "", $string);
		$result2 = str_replace(";", "", $result);
		return $result2.trim();
	}
?>
