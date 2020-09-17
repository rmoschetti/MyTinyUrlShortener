<?php
/***************************************/
/*          CONFIGURATION              */
/***************************************/

// Name of the file with the data
$FileDataName="mtus_data.php";

//First line of the file with the data
$FileDataHeader="<?php exit(); ?>\n";

//Passcode for administration
$Password="1234567890";


/***************************************/
/*          END CONFIGURATION          */
/***************************************/


WriteHeader();
Init();
WriteFooter();





/***************************************/
/*              FUNCTIONS              */
/***************************************/


function ReadDataAsCSV($FileDataName) {
	$myfile = fopen($FileDataName, "r");
	$FileLines= fgets($myfile);
	$resultsArray = array();
	while(($FileLines= fgets($myfile)) !==false) {
		$resultsArray[] = explode(";", $FileLines);
	}
	fclose($myfile);
	return $resultsArray;
}

function Init() {
	
	
	if (isset($UrlEnc)) { 
		//Request for a Url.
		$UrlEnc=$_GET["u"];
		
		
	} elseif (isset($UrlEnc)) {
		//Request for an operation.
		$Operation=$_POST["o"];
		
		if ($Operation=="show") {
			//Show the url table
		
		} elseif ($Operation=="write") {
			//Write a new line
			
		} elseif ($Operation=="delete") {
			//Remove a line

		}
	
	} else {
		//No requests, show login form
		WriteLogin();
		
	}
	
}


function WriteHeader() {
	echo "<html><head></head><body><h2>My Tiny Url Shortener</h2>";
}

function WriteFooter() {
	echo "</body></html>";
}

function WriteLogin() {
	echo "<form action='__FILE__'><input type='hidden' name='o' value='show'><input type='password' placeholder='Enter Password' name='password' required><button type='submit'>Login</button></form>";
}

?>