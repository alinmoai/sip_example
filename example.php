<?php
require_once('PhpSIP.class.php');

/* Sends NOTIFY to reset Linksys phone */

function getInviteInstance($sourceIP, $from, $to) {
  $API = new PhpSIP($sourceIP);
  $API->setDebug(true);

  $API->setMethod('INVITE');
  $API->setFrom($from);
  $API->setUri($to);

  $API->addHeader('Allow: INVITE, ACK, CANCEL, BYE, PRACK, NOTIFY, REFER, SUBSCRIBE, OPTIONS, UPDATE, INFO');
  $API->addHeader('Supported: replaces,timer,path');
  $API->addHeader('Session-Expires: 1800;refresher=uac');
  $API->addHeader('Min-SE: 900');
  $API->addHeader('Alert-Info: <urn:alert:tone:internal>');

  // 把Header換成正式環境
  $API->user_agent = "OmniPCX Enterprise R11.2.2 l2.300.40";

  // 把body換成正式環境
  $body = "v=0\r\n";
  $body.= "o=OXE 0 0 IN IP4 ".$API->src_ip."\r\n";
  $body.= "s=-\r\n";
  $body.= "c=IN IP4 ".$API->src_ip."\r\n";
  $body.= "t=0 0\r\n";
  $body.= "m=audio 32564 RTP/AVP 18 97\r\n";
  $body.= "a=sendrecv\r\n";
  $body.= "a=rtpmap:18 G729/8000\r\n";
  $body.= "a=fmtp:18 annexb=no\r\n";
  $body.= "a=ptime:20\r\n";
  $body.= "a=maxptime:40\r\n";
  $body.= "a=rtpmap:97 telephone-event/8000\r\n";
  // $API->setBody($body);

  return $API;
}

try
{
  $isDebug = false;
  $sourceIP = '192.168.100.222'; 

  $fromIP = "localhost";
  $toIP = "localhost";
  $extensionNumber = '6010';
  $setupNumber = rand(30001, 31000); // 第一次call用的from 
  $phoneNumber = '0987654321'; // 客戶電話號碼的from

  // $to = "\"$extensionNumber *\" <sip:$extensionNumber@$toIP;user=phone>"; // extension number
  $setupFrom = "\"$setupNumber *\" <sip:$setupNumber@$fromIP;user=phone>";  // 第一次call用的from 
  $phoneFrom = "\"$phoneNumber\" <sip:$phoneNumber@$fromIP;user=phone>";  // 客戶電話號碼的from
  $to = "sip:$extensionNumber@$toIP"; // extension number


  echo "staring setup call\n";

  $setupAPI = getInviteInstance($sourceIP, $setupFrom, $to);
  $res = $setupAPI->send();

  echo "\nto = ".$setupAPI->to."\n";
  if($res == 200 || isDebug) {
    echo "response: $res, setup success\n\n";

    echo "staring refer call\n";
    $referAPI = getInviteInstance($sourceIP, $phoneFrom, $to);
    
    $setupTo = $setupAPI->to;
    $setupFromTag = $setupAPI->from_tag;
    $setupToTag = $setupAPI->to_tag;
    $setupCallId = $setupAPI->call_id;

    $referAPI->addHeader("Replaces: $setupCallId;from-tag=$setupFromTag;to-tag=$setupToTag");
    $referAPI->addHeader("Referred-By: \"$setupFrom *\" $setupTo");

    $res = $referAPI->send();

    if ($res == 200 || isDebug) {
        echo "refer call success, bye to setup call\n\n";
        $setupAPI->setMethod('BYE');
        $res = $setupAPI->send();

        echo "response: $res, refer call success\n\n";
    } else {
        echo "response: $res, refer failed\n\n";
    }
  } else {
    echo "response: $res, setup failed\n\n";

  }
} catch (Exception $e) {
  
  echo $e;
}


?>
