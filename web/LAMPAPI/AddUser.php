<?php
	$inData = getRequestInfo();
	
	$username = protectInjection($inData["username"]);
	$password = $inData["password"];

	$conn = new mysqli("mydb.c17vnanzumzs.us-east-1.rds.amazonaws.com", "root", "mypassword", "small");
	if ($conn->connect_error) 
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
						$conn->close();						
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
	
	returnWithError("");
	
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
?>