<?php
/***************************************/
/*          CONFIGURATION              */
/***************************************/

// Name of the file with the data
define('_FileDataName_',"mtus_data.php");

//First line of the file with the data
define('_FileDataHeader_',"<?php exit(); ?>");

//Passcode for administration - note for you, it is "1234567890"
define('_Password_','$2y$10$DRtepbPgkUYv5GYhkJ5Qo.gQxK1nBron2caWuHtJ/pQYyyS83U4rO');

//Set true if the password is hashed, false if it is not.
define('_PasswordHash_',true);

//Url where to go if any error happens. It can be left empty
define('_FallbackUrl_',"");

//Check if url has to be encoded and decoded
define('_UrlEncode_',true);


/***************************************/
/*          END CONFIGURATION          */
/***************************************/


CreateNewDataFile(_FileDataName_);
WriteHeader();
Init();
WriteFooter();





/***************************************/
/*              FUNCTIONS              */
/***************************************/

function CreateNewDataFile($FileDataName) {
	if (!file_exists($FileDataName)) {
		file_put_contents($FileDataName,_FileDataHeader_);
	}
}

function Redirect($Data,$Short) {
	$LongUrl=SearchForShortName($Data,0,$Short,-2);
	if ($LongUrl==-1) {
		echo "No shortened url with name ".$Short;
		FallBack();
	} else {
		$Data[$LongUrl][2]++;
		WriteDataAsCSV(_FileDataName_,$Data);
		$RedUrl=$Data[$LongUrl][1];
		if (_UrlEncode_) $RedUrl=urldecode($RedUrl);
		header("Location: ".$RedUrl);
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
	if ($myfile===false) {
		return array();
	}
	$FileLines= fgets($myfile);
	$resultsArray = array();
	while(($FileLines= fgets($myfile)) !==false) {
		$FileLines=str_replace("\n","",$FileLines);
		if (strlen($FileLines)>3) {
			$resultsArray[] = explode(";", $FileLines);
			$resultsArray[count($resultsArray)-1][1]=$resultsArray[count($resultsArray)-1][1];
		}
	}
	fclose($myfile);
	return $resultsArray;
}

function WriteDataAsCSV($FileDataName,$Data) {
	$fp = fopen($FileDataName, 'w');
	fwrite($fp,_FileDataHeader_."\n");
	foreach ($Data as $lines) {
		fputcsv($fp, $lines,";");
	}

	fclose($fp);
}


function SearchForShortName($Data,$WhereToSearch,$String,$WhatToGiveBack) {
//This function looks the first occurrence in which $Data[$WhereToSearch] is $String.
//It returns $Data[$WhatToGiveBack] if $WhatToGiveBack>=0
//It returns true if $WhatToGiveBack==-1
//It returns the position in $Data if $WhatToGiveBack==-2
//It returns false if no occurrence is found
	for ($i=0; $i<count($Data);$i++) {
		if ($Data[$i][$WhereToSearch]===$String) {
			if ($WhatToGiveBack>-1) return $Data[$i][$WhatToGiveBack]; 
			elseif ($WhatToGiveBack==-2) return $i;
			else return 1;
		}
	}
	return -1;
}

function Init() {
	
	if (isset($_GET["u"])) { 
		//Request for a Url.
		$UrlEnc=$_GET["u"];
		$ArrayData=ReadDataAsCSV(_FileDataName_);
		Redirect($ArrayData,$UrlEnc);
		
	} elseif (isset($_GET["o"]) && $_GET["o"]=="login") {
		//Show login form
		WriteLogin();

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
			if (SearchForShortName($ArrayData,0,$ShortName,-1)==1) {
				echo "Warning: duplicate short name";
				DisplayTable($ArrayData);
			} else {
				if (_UrlEncode_) $LongUrl=urlencode($LongUrl);
				$ArrayData[]=[$ShortName,$LongUrl,0];
				WriteDataAsCSV(_FileDataName_,$ArrayData);
				//AddLineToFile(_FileDataName_,$ShortName.";".$LongUrl.";"."0");
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
			
		} else {
			Fallback();
			WriteLogin();
		}
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
		$LongUrl=$Data[$i][1];
		if (_UrlEncode_) $LongUrl=urldecode($LongUrl);
		echo "<tr>";
		echo "<td>".$i."</td>";
		echo "<td><a href='". $_SERVER['REQUEST_URI']."?u=".$Data[$i][0]."'>".$Data[$i][0]."</a></td>";
		echo "<td style='word-wrap:break-word;'><a href='".$LongUrl."'>".$LongUrl."</a></td>";
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
	if (_PasswordHash_ && password_verify($InsertPsw,$StoredPsw)) return 1;
	if (!_PasswordHash_ && $InsertPsw===$StoredPsw) return 1;
	
	echo "Authentication error";
	FallBack();
	exit();

}

?>