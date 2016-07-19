<?php
namespace Phpsmstools;

class AtCommands {

	static public function cmgf($type){
		return "AT+CMGF=".$type;
	}

	static public function cmgl($code){
		return "AT+CMGL=".$code;
	}

	static public function cmgd($param){
		return "AT+CMGD=".$param;
	}

	static public function cusd($query){
		return "AT+CUSD=1,\"$query\",15";
	}

}