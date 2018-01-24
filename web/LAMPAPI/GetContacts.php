<?php
	$inData = getRequestInfo();
	$secrets = readSecrets();
	$conn = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

	if ($conn->connect_error) 
	{
		returnWithError($conn->connect_error);	
	}
	else 
	{
		$sql    = "SELECT * FROM Contacts where userID=" . $inData["userID"];
		$result = $conn->query($sql);
		$count = 0;
		$searchResults = "";

		while($row = $result->fetch_assoc())
		{
			if($count > 0)
			{
				$searchResults .= ",";
			}
			$count++;

			$id = $row['id'];
			$firstName = $row['firstName'];
			$lastName = $row['lastName'];
			$phoneNumber = $row['phoneNumber'];
			$emailAddress = $row['emailAddress'];

			$searchResults .= '[{\"contactID\":'.$id.',\"firstName\":\"'.$firstName.'\",\"lastName\":\"'.$lastName.'\",\"phoneNumber\":'.$phoneNumber.',\"emailAddress\":\"'.$emailAddress.'\"}]';
		}

		returnWithInfo($searchResults);
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
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson($retValue);
	}

	function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson($retValue);
	}
?>
