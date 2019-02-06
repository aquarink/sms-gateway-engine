<?php
#################################################################################
#
#			PROJECT				: MOBIWIN SMS GATEWAY REVAMP 2019
#			AUTHOR				: JURI PEBRIANTO
#			EMAIL				: juripebrianto@gmail.com
#			FUNCTION			: IOD Provide MO to MT
#			MODIFIED			: 
#
#################################################################################

// ARRAY MO $explMO
$_mo_origin 	= $explMO[0]; 
$_msisdn 		= $explMO[1]; 
$_prepaid 		= str_replace('-', '', $explMO[2]); 
$_sms 			= $explMO[3]; 
$_telco 		= $explMO[4]; 
$_shortcode		= $explMO[5];
$_trx_id 		= $explMO[6];
$_trx_date 		= $explMO[7]; 
$_session_id 	= $explMO[8]; 
$_datetime 		= $explMO[9];

// CONNECTION $dbLogs and $dbConfig


// MT PARAM
$replyMT = array(
	'MO_ORIGIN' 	=> $_mo_origin,
	'MSISDN' 		=> $_msisdn,
	'PREPAID'		=> $_prepaid,
	'SMS'			=> $_sms,
	'TELCO'			=> $_telco,
	'SHORTCODE'		=> $_shortcode,
	'TRX_ID'		=> $_trx_id,
	'TRX_DATE'		=> $_trx_date,
	'SESSION_ID'	=> $_session_id,
	'DATE_TIME'		=> $_datetime,
	'REPLY_MT'		=> 'Test reply mt '. $_msisdn
);

?>