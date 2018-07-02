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


  return $API;
}

try
{
  $sourceIP = '192.168.100.222'; 
  $fromIP = "localhost";
  $toIP = "localhost";
  $extensionNumber = '6001';
  $setupNumber = rand(30001, 31000);
  $phoneNumber = '0987654321';

  $to = "\"$extensionNumber *\" <sip:$extensionNumber@$toIP;user=phone>"; // extension number
  $setupFrom = "\"$extensionNumber *\" <sip:$setupNumber@$fromIP;user=phone>";  // 第一次call用的from 
  $phoneFrom = "\"$phoneNumber\" <sip:$phoneNumber@$fromIP;user=phone>";  // 客戶電話號碼的from

  echo "\n".$to."\n";
  echo $setupFrom."\n";
  echo $phoneFrom."\n";

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
