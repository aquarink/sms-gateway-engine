<?php
#################################################################################
#
#			PROJECT				: MOBIWIN SMS GATEWAY REVAMP 2019
#			AUTHOR				: JURI PEBRIANTO
#			EMAIL				: juripebrianto@gmail.com
#			FUNCTION			: Read mo spool by telco by spool folder
#			MODIFIED			: 
#
#################################################################################

// OTHER
$telco = 'xl';
$shortname = '';
$prepaid = '0';

require dirname(dirname(dirname(__FILE__)))."/config/Connection.php";
require dirname(dirname(dirname(__FILE__)))."/config/telco/".$telco.".php";
require dirname(dirname(dirname(__FILE__)))."/config/functions/FileController.php";

$spool = range (1,XL_MO_SPOOL_FOLDER);
srand ((double)microtime()*1000000);
shuffle ($spool);

$spoolFolder = $spool[0];

if (PHP_SAPI === 'cli') {
    if(isset($argv[1])) {
    	$argument = $argv[1];
    } else {
    	$argument = $spoolFolder;
    }
} else {
	if(isset($_GET['argv'])) {
    	$argument = $_GET['argv'];
    } else {
    	$argument = $spoolFolder;
    }
    
}

// ENGINE FOLDER
$EngineFolder = dirname(dirname(dirname(__FILE__)));
$spoolFolder = dirname(dirname(dirname(__FILE__)))."/files/mo/".$telco."/spools/".$argument;

if (!file_exists($spoolFolder)) {
    mkdir($spoolFolder, 0777, true);
}

// READ MO
// while(true) {

    if ($handle = opendir($spoolFolder.'/')) {
        while (false !== ($files = readdir($handle))) {
            if ($files != '.' && $files != '..') {
                // READ FILE
                $theFile = fopen($spoolFolder . "/" . $files, "r");
                if ($theFile) {
                    $dataFileMO = fread($theFile, filesize($spoolFolder . "/" . $files));
                    $explMO = explode("@#@", $dataFileMO);
                    // print_r($explMO);
                    // Array ( [0] => sms [1] => 23232323 [2] => 0- [3] => Jodoh [4] => xl [5] => 99595 [6] => 11161148075604805124 [7] => 20190102095229 [8] => 100001200401141211091148612851 [9] => 2019-01-02 09:52:29 )

                    // ARRAY DATA
                    $moText = array(
				    	':mo_origin' 	=> $explMO[0] , 
				    	':msisdn' 		=> $explMO[1] , 
				    	':prepaid' 		=> str_replace('-', '', $explMO[2]) , 
				    	':sms' 			=> $explMO[3] , 
				    	':telco' 		=> $explMO[4] , 
				    	':shortcode'	=> $explMO[5] , 
				    	':trx_id' 		=> $explMO[6] , 
				    	':trx_date' 	=> $explMO[7] , 
				    	':session_id' 	=> $explMO[8] , 
				    	':datetime' 	=> $explMO[9]
				    );

                    // LOG CONF
				    $dbLogPath = XL_DB_LOG_FOLDER_PATH;
					$dbLogFilename = "db-".date('Y-m-d');

                    try {
					    $database = new Connection();
					    $dbLogs = $database->openConnectionLogs();
					    $dbConfig = $database->openConnectionConfig();
					 	
					 	$dateData = substr($explMO[7], 0,6);
					    // inserting data into create table using prepare statement to prevent from sql injections

					    // MO INCOMING LOGS TABLE NAME
					    $moIncomingTable = "smsgw_logs.mo_incoming_log_".$dateData."";

					    // CHECK MO_INCOMING_LOG_[DATE] TABLE EXIST
					    $checkTableMoIncoming = $dbLogs->prepare("SELECT 1 FROM ".$moIncomingTable." LIMIT 1");

					    if($checkTableMoIncoming->execute() == 0) {
					    	$createMoIncomingTable = "CREATE TABLE ".$moIncomingTable." (
					  			id_mo bigint(20) NOT NULL AUTO_INCREMENT,
						  		mo_origin varchar(10) NOT NULL DEFAULT '',
						  		msisdn varchar(30) NOT NULL DEFAULT '',
						  		prepaid int(2) NOT NULL DEFAULT '0',
						  		sms text NOT NULL,
						  		telco varchar(20) NOT NULL DEFAULT '',
						  		shortcode varchar(10) NOT NULL DEFAULT '',
						  		trx_id varchar(50) NOT NULL DEFAULT '0',
						  		trx_date varchar(50) NOT NULL DEFAULT '0',
					 		 	session_id varchar(50) NOT NULL DEFAULT '0',
						  		datetime datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						  		PRIMARY KEY (id_mo)
							)";

							$execCreateMoIncomingTable = $dbLogs->prepare($createMoIncomingTable);
							$execCreateMoIncomingTable->execute();
					    }


					    $moLog = $dbLogs->prepare("INSERT INTO ".$moIncomingTable." (mo_origin, msisdn, prepaid, sms, telco, shortcode, trx_id, trx_date, session_id, datetime) VALUES (:mo_origin, :msisdn, :prepaid, :sms, :telco, :shortcode, :trx_id, :trx_date, :session_id, :datetime)") ;
					 
					    // inserting a record
					    $moLog->execute($moText);

					    // LOG DATA
					    $dbLogText = implode("\n", array_map(
						    function ($v, $k) {
						        if(is_array($v)){
						            return $k.'[]='.implode('&'.$k.'[]=', $v);
						        } else{
					            	return $k.'='.$v;
						        }
						    }, 
						    $moText, 
						    array_keys($moText)
						))."\nSUCCESS EXECUTE DATABASE\n============================================== \n\n\n";
						// EXEC CREATE LOG
						CreateLogFile($dbLogPath,$dbLogFilename,$dbLogText,true);

						// CHECK IOD
						$explSms = explode(' ', $explMO[3]);
						if(strtolower($explSms[0]) == 'reg') {
							$keywordIs = strtolower($explSms[1]);
						} else {
							$keywordIs = strtolower($explSms[0]);
						}

						$checkKeyword = $dbLogs->prepare("SELECT * FROM smsgw_config.keyword WHERE keyword = :keyword");
					    $checkKeyword->bindParam(':keyword', strtolower($keywordIs));
					    $checkKeyword->execute();

					    if ($checkKeyword->rowCount() > 0) {
							$keywordData = $checkKeyword->fetchAll(PDO::FETCH_ASSOC);

							foreach ($keywordData as $key => $value) {
								# code... 2019
								$iodFile = $EngineFolder.'/config/iod/'.strtolower($value['keyword']).'.php';

								if (file_exists($iodFile)) {
								    // INCLUDE IOD FILE
									include $iodFile;
									// END INCLUDE IOD FILE

									//
									// END READ FILE
				                    // Close Read File Session
				                    fclose($theFile);

									// CREATE MT
									if(isset($replyMT)) {
										if(count($replyMT) > 0) {
											// print_r($replyMT); exit();
											for($i=0; $i < count($replyMT); $i++) {

												// Create MT FILE SPOOL
												$pathMT = $EngineFolder.'/files/mt/'.$replyMT[$i]['TELCO'].'/spools/'.$argument;
												$filenameMT = $argument.'-mt-'.$replyMT[$i]['SESSION_ID'];
												$textMT = implode('@#@', $replyMT[$i]);
												CreateIncomingFile($pathMT,$filenameMT,$textMT,true)
											}

											// Delete File MO SPOOL
											if(DeleteFile($spoolFolder . "/" . $files)) {
												$deleteFileStatus = "Delete MO File From Spool Success";
											} else {
												$deleteFileStatus = "Delete MO File From Spool Failed";
											}
										} else {
											$mtFileStatus = "Create MT File Spool Empty MO";
											// Delete Empty File MO SPOOL
											if(DeleteFile($spoolFolder . "/" . $files)) {
												$deleteFileStatus = "Delete Empty MO File From Spool Success";
											} else {
												$deleteFileStatus = "Delete Empty MO File From Spool Failed";
											}
										}
										// 
										$print = array(
											'MT_STATUS' => $mtFileStatus,
											'DELETE_MO' => $deleteFileStatus
										);

										print_r($print);
									}
								} else {
								    // LOG DATA
								    $otherLogText = $iodFile."\nNOT FOUND, CREATE IOD CONFIG\n============================================== \n\n\n";
									// 
									$otherLogPath = $EngineFolder.'/files/other';
									$otherLogFilename = "other-".date('Y-m-d');
									CreateLogFile($otherLogPath,$otherLogFilename,$otherLogText,true);
								}
							}
						} else {
					        // LOG DATA
						    $otherLogText = implode("\n", array_map(
							    function ($v, $k) {
							        if(is_array($v)){
							            return $k.'[]='.implode('&'.$k.'[]=', $v);
							        } else{
						            	return $k.'='.$v;
							        }
							    }, 
							    $moText, 
							    array_keys($moText)
							))."\nIOD ".strtoupper($keywordIs)." NOT EXIST, CHECK CMS\n============================================== \n\n\n";
							// 
							$otherLogPath = $EngineFolder.'/files/other';
							$otherLogFilename = "other-".date('Y-m-d');
							CreateLogFile($otherLogPath,$otherLogFilename,$otherLogText,true);
					    }

						// END CHECK IOD

					} catch (PDOException $e) {
					    // LOG DATA
					    $dbLogText = implode("\n", array_map(
						    function ($v, $k) {
						        if(is_array($v)){
						            return $k.'[]='.implode('&'.$k.'[]=', $v);
						        } else{
					            	return $k.'='.$v;
						        }
						    }, 
						    $moText, 
						    array_keys($moText)
						))."\nFAILED EXECUTE DATABASE : ".$e->getMessage()."\n============================================== \n\n\n";
						// EXEC CREATE LOG
						CreateLogFile($dbLogPath,$dbLogFilename,$dbLogText,true);
					}

                    // END READ FILE
                    // Close Read File Session
                    // fclose($theFile);
                }
            }
        }

        closedir($handle);
    }
// }