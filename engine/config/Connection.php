<?php
#################################################################################
#
#			PROJECT				: MOBIWIN SMS GATEWAY REVAMP 2019
#			AUTHOR				: JURI PEBRIANTO
#			EMAIL				: juripebrianto@gmail.com
#			FUNCTION			: Connection Class configuration
#			MODIFIED			: 
#
#################################################################################


Class Connection {

	private $serverLogs 	= "mysql:host=localhost;dbname=smsgw_logs";
	private $serverConfig 	= "mysql:host=localhost;dbname=smsgw_config";

	private $user 			= "root";
	private $pass 			= "";
	private $options  		= array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,);
	protected $con;
	protected $con2;


	public function openConnectionLogs() {
		try {
			$this->con = new PDO($this->serverLogs, $this->user,$this->pass,$this->options);
			return $this->con;
		}
		catch (PDOException $e) {
			echo "Check database Logs : " . $e->getMessage(); exit();
		}
	}

	public function openConnectionConfig() {
		try {
			$this->con2 = new PDO($this->serverConfig, $this->user,$this->pass,$this->options);
			return $this->con2;
		}
		catch (PDOException $e) {
			echo "Check database Config : " . $e->getMessage(); exit();
		}
	}

	public function closeConnection() {
		$this->con = null;
		$this->con2 = null;
	}
}

?>