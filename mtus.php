<?php
/***************************************/
/*          CONFIGURATION              */
/***************************************/

// Name of the file with the data
define('_FileDataName_',"mtus_data.php");

//First line of the file with the data
define('_FileDataHeader_',"<?php exit(); ?>\n");

//Passcode for administration
define('_Password_',"1234567890");


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

function WriteDataAsCSV($FileDataName,$Data) {
	$fp = fopen($FileDataName, 'w');
	fwrite($fp,_FileDataHeader_);
	foreach ($Data as $lines) {
		fputcsv($fp, $lines);
	}

	fclose($fp);
}

function Init() {
	
	if (isset($_GET["u"])) { 
		//Request for a Url.
		$UrlEnc=$_GET["u"];
		
		
	} elseif (isset($_POST["o"]) && isset($_POST["password"])) {
		//Request for an operation.
		$Operation=$_POST["o"];
		$InsertPsw=$_POST["password"];
		Auth($InsertPsw,_Password_);

		if ($Operation=="show") {
			//Show the url table
			$ArrayData=ReadDataAsCSV(_FileDataName_);
			DisplayTable($ArrayData);
			
		} elseif ($Operation=="insert") {
			//Write a new line
			
		} elseif ($Operation=="delete") {
			//Remove a line
			$ArrayData=ReadDataAsCSV(_FileDataName_);
			$ToRemove=$_POST["ElDelete"];
			for ($i=count($ToRemove)-1;$i>=0;$i--) {
				array_splice($ArrayData,$ToRemove[$i],1);
			}
			WriteDataAsCSV(_FileDataName_,$ArrayData);
			DisplayTable($ArrayData);
		}
	
	} else {
		//No requests, show login form
		WriteLogin();
		
	}
	
}



function DisplayTable($Data) {
	$FormInsert="<form><table border='solid'><tr><td>Short name</td><td><input type='text' name='short' required></td></tr><tr><td>Url text</td><td><input type='text' name='long' required></td></tr><tr><td><input type='password' placeholder='Enter Password' name='password' required></td><td><button type='submit'>Login</button></td></tr></table><input type='hidden' name='o' value='insert'></form><br><hr><br>";
	
	
	$HeaderTable="<form action='".basename(__FILE__)."' method='post'><table border='solid'><thead><tr><td>Id</td><td>Short url</td><td>Long url</td><td>Clicks</td><td>Delete?</td></tr></thead><tbody>";
	$FooterTable="</tbody></table><br><input type='hidden' name='o' value='delete'><input type='password' placeholder='Enter Password' name='password' required><button type='submit'>Delete Selected</button></form>";
	
	echo $FormInsert;
	echo $HeaderTable;
	for ($i=0; $i<count($Data);$i++) {
		echo "<tr>";
		echo "<td>".$i."</td>";
		echo "<td>".$Data[$i][0]."</td>";
		echo "<td>".$Data[$i][1]."</td>";
		echo "<td>".$Data[$i][2]."</td>";
		echo "<td><input type='checkbox' name='ElDelete[]' value='".$i."'></td>";
		echo "</tr>";
	}
	
	echo $FooterTable;
}


function WriteHeader() {
	echo "<html><head></head><body><h2>My Tiny Url Shortener</h2>";
}

function WriteFooter() {
	echo "</body></html>";
}

function WriteLogin() {
	echo "<form action='".basename(__FILE__)."' method='post'><input type='hidden' name='o' value='show'><input type='password' placeholder='Enter Password' name='password' required><button type='submit'>Login</button></form>";
}

function Auth($InsertPsw,$StoredPsw) {
	if ($InsertPsw!==$StoredPsw) {
		echo "Authentication error";
		exit();
	}
}

?>