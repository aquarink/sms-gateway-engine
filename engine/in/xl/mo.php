<?php
#################################################################################
#
#			PROJECT				: MOBIWIN SMS GATEWAY REVAMP 2019
#			AUTHOR				: JURI PEBRIANTO
#			EMAIL				: juripebrianto@gmail.com
#			FUNCTION			: Receive parameter SMS from Telco
#			MODIFIED			: 
#
#################################################################################

// mo.php?X-Source-Addr=119957018&X-Dest-Addr=99595&X-Pull-Trx-Id=11161148075604805124&_SC=Jodoh&_TID=100001200401141211091148612851

// OTHER
$telco = 'xl';
$shortname = '';
$prepaid = '0';

// ENGINE FOLDER
require dirname(dirname(dirname(__FILE__)))."/config/functions/FileController.php";
require dirname(dirname(dirname(__FILE__)))."/config/telco/".$telco.".php";

// echo XL_MO_SPOOL_FOLDER_PATH;
// exit();

// INITIALIZE
if(isset($_GET['X-Source-Addr'])) {
	if(substr($_GET['X-Source-Addr'], 0,1) == '0') {
		$mo['msisdn'] = '62'.substr($_GET['X-Source-Addr'], 1);
	} else {
		$mo['msisdn'] = $_GET['X-Source-Addr'];
	}
} else {
	$mo['msisdn'] = '';
}

if(isset($_GET['X-Dest-Addr'])) {
	$mo['shortcode'] = $_GET['X-Dest-Addr'];
} else {
	$mo['shortcode'] = '';
}

if(isset($_GET['X-Pull-Trx-Id'])) {
	$mo['trxid'] = $_GET['X-Pull-Trx-Id'];
} else {
	$mo['trxid'] = '';
}

if(isset($_GET['_SC'])) {
	$mo['sms'] = $_GET['_SC'];
} else {
	$mo['sms'] = '';
}

if(isset($_GET['_TID'])) {
	$mo['sessionid'] = $_GET['_TID'];
} else {
	$mo['sessionid'] = $mo['msisdn'].date("YmdHis").rand();
}


// print_r($mo); exit();
header ("Content-Type:text/xml");

// CHECK ELEMENT
if($mo['msisdn'] == '' || $mo['shortcode'] == '' || $mo['trxid'] == '' || $mo['sms'] == '' || $mo['sessionid'] == '') {
	// echo "
	// <MO>
	//   	<status>1</status>
	//   	<message>MO Failed Created</message>
	//   	<date>
	//     	<days>
	//       		<day>".date('d')."</day>
	//     	</days>
	//     	<months>
	//       		<month>".date('m')."</month>
	// 		</months>
	//     	<years>
	//       		<year>".date('Y')."</year>
	//     	</years>
	// 		<times>
	//       		<time>".date('H:i:s')."</time>
	//     	</times>
	//   	</date>
	// </MO>";
} else {

	// MO EXEC
	$spool = range (1,XL_MO_SPOOL_FOLDER);
	srand ((double)microtime()*1000000);
	shuffle ($spool);

	$spoolFolder = $spool[0];

	// SMS
	$smsTextArr = array(
		'origin' => 'sms',
		'msisdn' => $mo['msisdn'],
		'prepaid' => $prepaid.'-'.$shortname,
		'sms' => $mo['sms'],
		'telco' => $telco,
		'shortcode' => $mo['shortcode'],
		'trxid' => $mo['trxid'],
		'trxdate' => date("YmdHis"),
		'sessionid' => $mo['sessionid'],
		'datetime' => date("Y-m-d H:i:s")
	);

	// TO FILE
	$moPath = XL_MO_SPOOL_FOLDER_PATH.'/'.$spool[0];
	$moFilename = "mo-".$mo['sessionid'];
	$moText = implode("@#@", $smsTextArr);
	CreateIncomingFile($moPath,$moFilename,$moText,true);

	$moLogPath = XL_MO_LOG_FOLDER_PATH;
	$moLogFilename = $spool[0]."-mo-".date("YmdH");
	// 
	$moLogText = implode("\n", array_map(
	    function ($v, $k) {
	        if(is_array($v)){
	            return $k.'[]='.implode('&'.$k.'[]=', $v);
	        } else{
            	return $k.'='.$v;
	        }
	    }, 
	    $smsTextArr, 
	    array_keys($smsTextArr)
	))."\n============================================== \n\n\n";
	// 
	CreateLogFile($moLogPath,$moLogFilename,$moLogText,true);

	echo "
	<MO>
	  	<status>0</status>
	  	<message>MO Created</message>
	  	<date>
	    	<days>
	      		<day>".date('d')."</day>
	    	</days>
	    	<months>
	      		<month>".date('m')."</month>
			</months>
	    	<years>
	      		<year>".date('Y')."</year>
	    	</years>
			<times>
	      		<time>".date('H:i:s')."</time>
	    	</times>
	  	</date>
	</MO>";
}