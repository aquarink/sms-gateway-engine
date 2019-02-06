<?php
#################################################################################
#
#			PROJECT				: MOBIWIN SMS GATEWAY REVAMP 2019
#			AUTHOR				: JURI PEBRIANTO
#			EMAIL				: juripebrianto@gmail.com
#			FUNCTION			: Create File
#			MODIFIED			: 
#
#################################################################################

// ENGINE FOLDER
require dirname(dirname(dirname(__FILE__)))."/config/Base.php";

function CreateIncomingFile($path,$filename,$text,$createFolder)
{
	# code... 2019
	if($createFolder) {
		if (!file_exists($path)) {
		    mkdir($path, 0777, true);
		}
	}
	
	$incomingFile = fopen ($path."/".$filename.".sms", "w");
	fwrite($incomingFile, $text);

    if (fclose($incomingFile)) {
        return true;
    } else {
        return false;
    }
}

function CreateLogFile($path,$filename,$text,$createFolder)
{
	# code... 2019
	if($createFolder) {
		if (!file_exists($path)) {
		    mkdir($path, 0777, true);
		}
	}

	$logFile = fopen ($path."/".$filename.".log", "a");
	fwrite($logFile, $text);

    if (fclose($logFile)) {
        return true;
    } else {
        return false;
    }
}

function DeleteFile($filename) 
{
	# code... 2019
	if(unlink($filename)) {
		return true;
	} else {
		return false;
	}
}