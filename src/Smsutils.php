<?php
namespace Phpsmstools;

class Smsutils {

	static public function parseRawSms($str) {
		$result = array();
		if (substr($str,0,2) != '07') return false;
		$bin_data = hex2bin($str);
		$result['smsc'] = substr($str,4,12);
		$sms_pos = substr($bin_data, 9,1);
		$sender_length = hexdec(substr($str, 18,2));
		if($sender_length%2!=0){
			$sender_length+=1;
		}
		$result['sender'] = self::decode7Bit(substr($str, 22, $sender_length));
		$offset = 22+$sender_length;
		$result['time'] = substr($str, $offset+4, 14);
		$offset += 4+14;
		$pdu_len = hexdec(substr($str, $offset + 2 ,2));
		$text_offset = $offset + 4+$pdu_len*2;
		$result['type'] = hexdec(substr($str, $offset + 4 ,2));
		$result['sms_id'] = substr($str, $offset + 8 ,$text_offset - $offset - 12 );
		$result['sms_count'] = hexdec(substr($str, $text_offset - 4 ,2));
		$result['sms_part'] = hexdec(substr($str, $text_offset - 2 ,2));
		$result['sms_text'] = self::rawSmsTextDecode(substr($str, $text_offset ));
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

                $a      =       ($mask >> ($shift+1)) & 0xFF;
                $b      =       $a ^ 0xFF;

                $digit = ($carry) | ((ord($char) & $a) << ($shift)) & 0xFF;
                $carry = (ord($char) & $b) >> (7-$shift);
                $ret .= chr($digit);

                $shift++;
        }
        if ($carry) $ret .= chr($carry);
        return $ret;
}


	static public function rawSmsTextDecode($msg) {
		$msg = hex2bin($msg);
		$result =  iconv("UCS-2BE", "UTF8", $msg);
		return $result;
	}

}