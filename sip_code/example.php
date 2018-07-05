<?php
require_once('PhpSIP.class.php');

/* Sends NOTIFY to reset Linksys phone */

function startCallTask($api, $sourceIP, $fromIP, $toIP, $callerID, $extensionNumber, $isDebug) {
    $extensionNumber = '4016';
  
    $from = "\"$setupNumber *\" <sip:$callerID@$fromIP;user=phone>";  
    $to = "sip:$extensionNumber@$toIP"; // extension number

    $api->setMethod('INVITE');
    $api->setFrom($from);
    $api->setUri($to);
    $api->setDebug($isDebug);

    $api->addHeader('Allow: INVITE, ACK, CANCEL, BYE, PRACK, NOTIFY, REFER, SUBSCRIBE, OPTIONS, UPDATE, INFO');
    $api->addHeader('Supported: replaces,timer,path');
    $api->addHeader('Session-Expires: 1800;refresher=uac');
    $api->addHeader('Min-SE: 900');
    $api->addHeader('Alert-Info: <urn:alert:tone:internal>');
    $api->user_agent = "OmniPCX Enterprise R11.2.2 l2.300.40";

    // 把body換成正式環境
    $body = "v=0\r\n";
    $body.= "o=OXE 0 0 IN IP4 ".$api->src_ip."\r\n";
    $body.= "s=-\r\n";
    $body.= "c=IN IP4 ".$api->src_ip."\r\n";
    $body.= "t=0 0\r\n";
    $body.= "m=audio 32564 RTP/AVP 18 97\r\n";
    $body.= "a=sendrecv\r\n";
    $body.= "a=rtpmap:18 G729/8000\r\n";
    $body.= "a=fmtp:18 annexb=no\r\n";
    $body.= "a=ptime:20\r\n";
    $body.= "a=maxptime:40\r\n";
    $body.= "a=rtpmap:97 telephone-event/8000\r\n";
    $api->setBody($body);

    $res = $api->send();
    if($isDebug) {
        echo "res: $res\n";    
    }
    return $api;
}

function startRegisterTask($api, $setupNumber, $fromIP, $isDebug) {
    
    $extensionNumber = '4016';
    $setupNumber = 32001;

    $setupFrom = "<sip:$setupNumber@$fromIP>";  // 第一次call用的from 
    $to = "sip:$setupNumber@$fromIP"; // extension number

    $api->setMethod('REGISTER');
    $api->setFrom($setupFrom);
    $api->setUri("sip:$fromIP");
    $api->setTo($to);
    $api->setDebug($isDebug);

    $api->addHeader('Supported: replaces, timer');
    $api->addHeader('Expires: 120');

    $api->user_agent = "Asterisk PBX 13.1.0~dfsg-1.1ubuntu4.1";
    
    $res = $api->send();
    if($isDebug) {
        echo "res: $res\n";    
    }
    
    return $api;
}

try
{  
    $isDebug = true;

    $sourceIP = '192.168.98.2';
    $fromIP = "192.168.99.200";
    $toIP = "192.168.99.200";

    $setupNumber = '32001';
    $extensionNumber = '4016';

    $setupAPI = new PhpSIP($sourceIP);

    $setupAPI = startRegisterTask($setupAPI, $setupNumber, $fromIP, $isDebug);
    $setupApi = startCallTask($setupAPI, $sourceIP, $fromIP, $toIP, $setupNumber, $extensionNumber, $isDebug);
} catch (Exception $e) {
    echo $e;
}
?>
