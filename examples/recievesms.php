<?php
require __DIR__.'../vendor/autoload.php';

use \Phpsmstools\Telnet as Telnet;
use \Phpsmstools\Atcommands as AT;
use \Phpsmstools\Smsutils as SMS;


$telnet1 = new Telnet('root','password','192.168.1.1');
$telnet2 = new Telnet('root','password','192.168.1.1');

$telnet1->sendCommand(AT::cmgf(0));
$telnet1->sendCommand(AT::cmgl(4));

$out = $telnet2->readOutput();

preg_match_all('#\+CMGL: (\d+),(\d+),,(\d+)\s+([^\s]*)\s*#', $out, $matches, PREG_SET_ORDER);
$messages = array();
foreach($matches as $sms) {
	$message = SMS::parsePdu($sms[4]);
	$messages[$message['sms_id']][$message['sms_part']] = $message['sms_text'];
}





