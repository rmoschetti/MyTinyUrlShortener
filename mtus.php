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

//Url where to go if any error happens. It can be left empty
define('_FallbackUrl_',"");


/***************************************/
/*          END CONFIGURATION          */
/***************************************/


WriteHeader();
Init();
WriteFooter();





/***************************************/
/*              FUNCTIONS              */
/***************************************/


function Redirect($Data,$Short) {
	$LongUrl=SearchForShortName($Data,0,$Short,-2);
	if ($LongUrl==false) {
		echo "No shortened url with this name";
		FallBack();
	} else {
		$Data[$LongUrl][2]++;
		WriteDataAsCSV(_FileDataName_,$Data);
		header("Location: ".$Data[$LongUrl][1]);
		exit();
	}
}

function FallBack() {
	if (_FallbackUrl_ != "") {
		header("Location: "._FallbackUrl_);
		exit();
	}
}


function ReadDataAsCSV($FileDataName) {
	$myfile = fopen($FileDataName, "r");
	$FileLines= fgets($myfile);
	$resultsArray = array();
	while(($FileLines= fgets($myfile)) !==false) {
		$FileLines=str_replace("\n","",$FileLines);
		$resultsArray[] = explode(";", $FileLines);
	}
	fclose($myfile);
	return $resultsArray;
}

function WriteDataAsCSV($FileDataName,$Data) {
	$fp = fopen($FileDataName, 'w');
	fwrite($fp,_FileDataHeader_);
	foreach ($Data as $lines) {
		fputcsv($fp, $lines,";");
	}

	fclose($fp);
}

function AddLineToFile($FileDataName,$String) {
	file_put_contents($FileDataName,"\n".$String,FILE_APPEND);
}

function SearchForShortName($Data,$WhereToSearch,$String,$WhatToGiveBack) {
//This function looks the first occurrence in which $Data[$WhereToSearch] is $String.
//It returns $Data[$WhatToGiveBack] if $WhatToGiveBack>=0
//It returns true if $WhatToGiveBack==-1
//It returns the position in $Data if $WhatToGiveBack==-2
//It returns false if no occurrence is found
	for ($i=0; $i<count($Data);$i++)
		if ($Data[$i][$WhereToSearch]===$String) {
			if ($WhatToGiveBack>-1) return $Data[$i][$WhatToGiveBack]; 
			elseif ($WhatToGiveBack==-2) return $i;
			else return true;
		}
	}
	return false;
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
			$ArrayData=ReadDataAsCSV(_FileDataName_);
			$ShortName=$_POST["short"];
			$LongUrl=$_POST["long"];
			if (SearchForShortName($ArrayData,0,$ShortName,-1)) {
				echo "Warning: duplicate short name";
				DisplayTable($ArrayData);
			} else {
				AddLineToFile(_FileDataName_,$ShortName.";".$LongUrl.";"."0");
				$ArrayData[]=[$ShortName,$LongUrl,0];
				DisplayTable($ArrayData);
			}
			
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
	
	} elseif ($Operation=="login") {
		//Show login form
		WriteLogin();
	} else {
		//No requests, either fallback or show login
		Fallback();
		WriteLogin();
	}
	
}



function DisplayTable($Data) {
	$FormInsert="<form action='".basename(__FILE__)."' method='post'><table border='solid'><tr><td>Short name</td><td><input type='text' name='short' required></td></tr><tr><td>Url text</td><td><input type='text' name='long' required></td></tr><tr><td><input type='password' placeholder='Enter Password' name='password' required></td><td><button type='submit'>Add new url</button></td></tr></table><input type='hidden' name='o' value='insert'></form><br><hr><br>";
	
	
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
		FallBack();
		exit();
	}
}

?>