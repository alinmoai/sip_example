<?php
require_once('PhpSIP.class.php');

/* Sends NOTIFY to reset Linksys phone */

function getInviteInstance($sourceIP, $from, $to) {
  $API = new PhpSIP($sourceIP);
  $API->setDebug(true);

  $API->setMethod('INVITE');
  $API->setFrom("sip:$from@localhost");
  $API->setUri("sip:$to@localhost");

  $API->addHeader('Allow: INVITE, ACK, CANCEL, BYE, PRACK, NOTIFY, REFER, SUBSCRIBE, OPTIONS, UPDATE, INFO');
  $API->addHeader('Supported: replaces,timer,path');
  $API->addHeader('Session-Expires: 1800;refresher=uac');
  $API->addHeader('Min-SE: 900');
  $API->addHeader('Alert-Info: <urn:alert:tone:internal>');

  $API->setBody("v=0
o=OXE 1529993935 1529993935 IN IP4 10.10.11.151
s=-
c=IN IP4 10.10.11.194
t=0 0
m=audio 32564 RTP/AVP 18 97
a=sendrecv
a=rtpmap:18 G729/8000
a=fmtp:18 annexb=no
a=ptime:20
a=maxptime:40
a=rtpmap:97 telephone-event/8000");

  return $API;
}

try
{
  $sourceIP = '192.168.100.222'; 
  $to = '6001'; // extension number
  $setupFrom = rand(30001, 31000);  // 第一次call用的from 
  $phoneFrom = '0987654321';  // 客戶電話號碼的from

  echo "staring setup call\n";

  $setupAPI = getInviteInstance($sourceIP, $setupFrom, $to);
  $res = $setupAPI->send();

  echo "\nto = ".$setupAPI->to."\n";
  if($res == 200) {
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

    if ($res == 200) {
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
