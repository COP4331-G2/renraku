<?php
	$inData = getRequestInfo();
	
	$username = protectInjection($inData["username"]);
	$password = $inData["password"];

	$secrets = readSecrets();
	$conn = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

	if ( $conn->connect_error ) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{   		
		if(strlen($username) <= 45)
		{		
			if(strlen($password) <= 45)
			{			
				if(strlen($username) > 0)
				{
					if(strlen($password) > 0)
					{
						$sql = "select username from Users where username like username='" . $username . "'";

						$result = $conn->query($sql);	
						
						if( $result = $conn->query($sql) == TRUE )
						{
							if ($result->num_rows == 0) 
							{			
								$sql = "insert into Users (username,password) VALUES ('" . $username .  "','" . $password . "')";

								if( $result = $conn->query($sql) != TRUE )
								{
									returnWithError( $conn->error );	
								}
								else 
								{
									returnWithError("");
								}
							}
							else 
							{
								returnWithError("Username already exists!");
							}					
						}
						else 
						{
							returnWithError( $conn->error );
						}							
					}
					else 
					{
						returnWithError("Password cannot be empty.");
					}
				}
				else 
				{
					returnWithError("Username cannot be empty.");
				}
			}
			else 
			{
				returnWithError("Password needs to be less than 45 characters.");
			}
		}
		else
		{
			returnWithError("Username needs to be less than 45 characters.")
		}
	}
	$conn->close();
		
	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers: Content-Type, origin');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function protectInjection($string)
	{
		$result = str_replace("'", "", $string);
		$result2 = str_replace(";", "", $result);
		return $result2.trim();
	}

	/**
	 * Reads MySQL database login information through a 'secrets' file
	 *
	 *  @return array (array containing database login information)
	 */
	function readSecrets()
	{
		$secretsFile = fopen("../secrets", "r");

		while (!feof($secretsFile)) 
		{
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
?>