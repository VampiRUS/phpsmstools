<?php
namespace Phpsmstools;
use Exception;


class Telnet {

	private $login = null;
	private $password = null;
	private $ip = null;
	private $socket = null;
	private $tty = "/dev/ttyUSB2";

	public function __construct($login, $password, $ip){
		$this->login = $login;
		$this->password = $password;
		$this->ip = $ip;
		$this->connect();
		$this->login();
	}

	public function setTty($dev){
		$this->tty = $dev;
	}

	private function connect(){
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($this->socket === false) {
			throw new Exception(socket_strerror(socket_last_error()), 1);

		}
		socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
		$result = socket_connect($this->socket, $this->ip, 23);
		if (!$result) {
			throw new Exception("Error Connent", 2);
		}
	}

	private function login(){
		//TODO проверка на Login incorrect
		while($out = $this->read()){}
		$this->write($this->login);
		while($out = $this->read()){}
		$this->write($this->password);
		while($out = $this->read()){}
	}

	private function write($text){
		socket_write($this->socket, "{$text}\n");
	}

	private function read($len = 1024) {
		return socket_read($this->socket, $len);
	}

	public function sendCommand($command){
		$this->write('echo -e "'.addcslashes($command, '"').'\r\n" > '.$this->tty);
	}

	public function readOutput(){
		$this->write("cat ".$this->tty);
		$out = '';
		for($i = 0; $i<25; $i++){
			$out.= $this->read();
		}

		socket_write($this->socket, "\x03");

		return trim(str_replace(array("cat ".$this->tty, "root@DD-WRT:~#"), "", $out));
	}

	public function __destruct(){
		$this->write('exit');
		socket_close($this->socket);
	}

}