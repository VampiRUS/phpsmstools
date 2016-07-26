<?php
namespace Phpsmstools;

class Smsutils {

	static public function parsePdu($str) {
		$result = array();
		if (substr($str,0,2) != '07') return false;
		$result['smsc'] = substr($str,4,12);
		$result['sms_type'] = substr($str,16,2);
		$uhdi = (bool)(ord(hex2bin($result['sms_type'])) & 0x40);

		$sender_length = hexdec(substr($str, 18,2));
		$result['sender_type'] = substr($str, 20, 2);
		if($sender_length%2!=0){
			$sender_length+=1;
		}

		$result['sender'] = substr($str, 22, $sender_length);
		if ($result['sender_type'] != '91') {
			$result['sender'] = decode7Bit($result['sender']);
		}
		$offset = 22+$sender_length;
		$result['time'] = substr($str, $offset+4, 14);
		$offset += 4+14;
		$content_len = hexdec(substr($str, $offset ,2));
		$pdu_len = hexdec(substr($str, $offset + 2 ,2));

		$content = substr($str, $offset+2);
		if($uhdi) {
			$text_offset = $offset + 4+$pdu_len*2;
			$result['type'] = hexdec(substr($str, $offset + 4 ,2));
			$result['sms_id'] = substr($str, $offset + 8 ,$text_offset - $offset - 12 );
			$result['sms_count'] = hexdec(substr($str, $text_offset - 4 ,2));
			$result['sms_part'] = hexdec(substr($str, $text_offset - 2 ,2));
		} else {
			$text_offset = $offset+2;
			$result['sms_id'] = 0;
			$result['sms_count'] = 0;
			$result['sms_part'] = 0;

		}
		$result['sms_text'] = self::usc2Decode(substr($str, $text_offset ));
		return $result;
	}

	static public function decode7Bit($text){
		$ret = '';
		$data = str_split(pack('H*', $text));

		$mask = 0xFF;
		$shift = 0;
		$carry = 0;
		foreach ($data as $char) {
			if ($shift == 7) {
				$ret .= chr($carry);
				$carry = 0;
				$shift = 0;
			}

			$a = ($mask >> ($shift+1)) & 0xFF;
			$b = $a ^ 0xFF;

			$digit = ($carry) | ((ord($char) & $a) << ($shift)) & 0xFF;
			$carry = (ord($char) & $b) >> (7-$shift);
			$ret .= chr($digit);

			$shift++;
		}
		if ($carry) $ret .= chr($carry);
		return $ret;
}


	static public function usc2Decode($msg) {
		$msg = hex2bin($msg);
		$result =  iconv("UCS-2BE", "UTF8", $msg);
		return $result;
	}

}