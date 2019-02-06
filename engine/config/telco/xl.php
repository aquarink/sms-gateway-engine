<?php
#################################################################################
#
#			PROJECT				: MOBIWIN SMS GATEWAY REVAMP 2019
#			AUTHOR				: JURI PEBRIANTO
#			EMAIL				: juripebrianto@gmail.com
#			FUNCTION			: Telco configuration
#			MODIFIED			: 
#
#################################################################################

// ENGINE FOLDER
$EngineFolder = dirname(dirname(dirname(__FILE__)));

define('MO_FILE_PATH', $EngineFolder."/files/mo");

// Telco Config
define('XL_TPS', 10);
define('XL_PULL_SPOOL_EXEC', 5);
define('XL_PUSH_SPOOL_EXEC', XL_TPS - XL_PULL_SPOOL_EXEC);
define('XL_MO_SPOOL_FOLDER', 10);
define('XL_MO_SPOOL_FOLDER_PATH', $EngineFolder."/files/mo/xl/spools");
define('XL_MO_LOG_FOLDER_PATH', $EngineFolder."/files/mo/xl/logs");

define('XL_DR_SPOOL_FOLDER', 10);
define('XL_DR_SPOOL_FOLDER_PATH', $EngineFolder."/files/dr/xl/spools");
define('XL_DR_LOG_FOLDER_PATH', $EngineFolder."/files/dr/xl/logs");

define('XL_DB_LOG_FOLDER_PATH', $EngineFolder."/files/db/xl/logs");