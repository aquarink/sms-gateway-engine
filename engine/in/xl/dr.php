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

// dr.php?_tid=100001200301141211114754903141&status_id=101&dtdone=20141211114754&errorcode=0030000000002208&errordescription=000000

// OTHER
$telco 			= 'xl';
$dr['trxid']	= date("YmdHis").rand(0,10000);
$dr['trxdate']	= date("YmdHis");

// ENGINE FOLDER
require dirname(dirname(dirname(__FILE__)))."/config/functions/FileController.php";
require dirname(dirname(dirname(__FILE__)))."/config/telco/".$telco.".php";

// INITIALIZE
if(isset($_GET['_tid'])) {
	$dr['sessionid'] = $_GET['_tid'];
} else {
	$dr['sessionid'] = '';
}

if(isset($_GET['status_id'])) {
	$dr['drstatus'] = $_GET['status_id'];
} else {
	$dr['drstatus'] = '';
}

if(isset($_GET['dtdone'])) {
	$dr['drdate'] = $_GET['dtdone'];
} else {
	$dr['drdate'] = '';
}

if(isset($_GET['errorcode'])) {
	$dr['errorcode'] = $_GET['errorcode'];
} else {
	$dr['errorcode'] = '';
}

if(isset($_GET['errordescription'])) {
	$dr['errordesc'] = $_GET['errordescription'];
} else {
	$dr['errordesc'] = '';
}

if(isset($_GET['sid'])) {
	if(!empty($_GET['sid'])) {
		$dr['sidsuccess'] = $_GET['sid'];
	} else {
		$dr['sidsuccess'] = '';
	}
} else {
	$dr['sidsuccess'] = '';
}

if($dr['sidsuccess'] <> "") {
	$dr['chargesuccess'] = $config_xl[ $sid_success ];
}
else $charge_success = "";


// print_r($mo); exit();
header ("Content-Type:text/xml");

// CHECK ELEMENT
$dr['trxdate']	= date("YmdHis");
if($dr['sessionid'] == '' || $dr['drstatus'] == '' || $dr['drdate'] == '' || $dr['errorcode'] == '' || $dr['errordesc'] == '') {
	// echo "
	// <DR>
	//   	<status>1</status>
	//   	<message>DR Failed Created</message>
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
	// </DR>";
} else {

	// MO EXEC
	$spool = range (1,XL_DR_SPOOL_FOLDER);
	srand ((double)microtime()*1000000);
	shuffle ($spool);

	$spoolFolder = $spool[0];

	// SMS
	$smsTextArr = array(
		'sessionid' => $dr['sessionid'],
		'drstatus' => $dr['drstatus'],
		'drdate' => $dr['drdate'],
		'errorcode' => $dr['errorcode'],
		'errordesc' => $dr['errordesc'],
		'telco' => $telco,
		'trxid' => $dr['trxid'],
		'trxdate' => date("YmdHis"),
		'datetime' => date("Y-m-d H:i:s")
	);

	// TO FILE
	$drPath = XL_DR_SPOOL_FOLDER_PATH.'/'.$spool[0];
	$drFilename = "mo-".$dr['sessionid'];
	$drText = implode("@#@", $smsTextArr);
	CreateIncomingFile($drPath,$drFilename,$drText,true);

	$drLogPath = XL_DR_LOG_FOLDER_PATH;
	$drLogFilename = $spool[0]."-mo-".date("YmdH");
	// 
	$drLogText = implode("\n", array_map(
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
	CreateLogFile($drLogPath,$drLogFilename,$drLogText,true);

	echo "
	<DR>
	  	<status>0</status>
	  	<message>DR Created</message>
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
	</DR>";
}