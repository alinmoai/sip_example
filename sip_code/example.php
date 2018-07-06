<?php
require_once('PhpSIP.class.php');

/* Sends NOTIFY to reset Linksys phone */

function startCallTask($api, $sourceIP, $fromIP, $toIP, $callerID, $extensionNumber, $isDebug) {  
    $api = getCallAPI($api, $sourceIP, $fromIP, $toIP, $callerID, $extensionNumber, $isDebug);

    $res = $api->send();
    if($isDebug) {
        echo "res: $res\n";    
    }
    return $api;
}

function getCallAPI($api, $sourceIP, $fromIP, $toIP, $callerID, $extensionNumber, $isDebug) {  
    // if(strlen($callerID) > 9) {
    //     $from = "\"$callerID\" <sip:$callerID@$fromIP;user=phone>"; 
    // } else {
    //     $from = "\"$callerID *\" <sip:$callerID@$fromIP;user=phone>"; 
    // }
    $from = "sip:$callerID@$fromIP";

    $to = "sip:$extensionNumber@$toIP"; // extension number

    $api->setMethod('INVITE');
    $api->setFrom($from);
    $api->setTo($to);
    $api->setUri($to);
    $api->setDebug($isDebug);
    
    $api = addHeader($api);
    $api = setBody($api);

    return $api;
}

function addHeader($api){
    $api->addHeader('Allow: INVITE, ACK, CANCEL, BYE, PRACK, NOTIFY, REFER, SUBSCRIBE, OPTIONS, UPDATE, INFO');
    $api->addHeader('Supported: replaces,timer,path');
    $api->addHeader('Session-Expires: 1800;refresher=uac');
    $api->addHeader('Min-SE: 900');
    $api->addHeader('Alert-Info: <urn:alert:tone:internal>');
    $api->user_agent = "OmniPCX Enterprise R11.2.2 l2.300.40";
    return $api;
}

function setBody($api) {
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

    return $api;
}

function startRegisterTask($api, $setupNumber, $extensionNumber, $fromIP, $isDebug) {
    $setupFrom = "<sip:$setupNumber@$fromIP>";  // 第一次call用的from 
    $to = "sip:$setupNumber@$fromIP"; // extension number

    $api->setMethod('REGISTER');
    $api->setFrom($setupFrom);
    $api->setUri("sip:$fromIP");
    $api->setTo($to);
    $api->setDebug($isDebug);

    $api->addHeader('Supported: replaces, timer');
    $api->addHeader('Expires: 120');

    // $api->user_agent = "Asterisk PBX 13.1.0~dfsg-1.1ubuntu4.1";

    // $api->user_agent = "OmniPCX Enterprise R11.2.2 l2.300.40";
    
    $res = $api->send();
    if($isDebug) {
        echo "res: $res\n";    
    }
    
    return $api;
}

function relationCall($phoneAPI, $setupAPI) {
    $setupCallId = $setupAPI->call_id;
    $setupFrom = $setupAPI->from;
    $setupFromTag = $setupAPI->from_tag;
    $setupTo = $setupAPI->to;
    $setupToTag = $setupAPI->to_tag;
    
    $phoneAPI->addHeader("Replaces: $setupCallId;from-tag=$setupFromTag;to-tag=$setupToTag");
    $phoneAPI->addHeader("Referred-By: $setupFrom");
    return $phoneAPI;
}


// 之後可以換成RPT發出bye
function byeSound($api) {
    $api->setMethod('BYE');
    $api->send();
    return $api;
}

try
{  
    $isDebug = true;

    $sourceIP = '192.168.98.2';  // 執行php的ip , 跟asterisk相同
    $fromIP = "localhost";  
    $toIP = "192.168.98.2:5060"; 

    $setupNumber = '32002';
    $extensionNumber = '30206';
    $phoneNumber = '0988888988';

    $setupAPI = new PhpSIP($sourceIP);
    // $setupAPI = startRegisterTask($setupAPI, $setupNumber, $extensionNumber, $fromIP, $isDebug);
    $setupApi = startCallTask($setupAPI, $sourceIP, $fromIP, $toIP, $setupNumber, $extensionNumber, $isDebug);

    // $setupApi->setMethod('INVITE');
    // $setupApi = addHeader($setupApi);
    // $setupApi = setBody($setupApi);
    // $setupApi = relationCall($setupApi, $setupApi);
    // $from = "\"$phoneNumber\" <sip:$phoneNumber@$fromIP;user=phone>"; 
    // $setupApi->setFrom($from);
    // $setupApi->send();
    // byeSound($setupApi);

    $phoneApi = new PhpSIP($sourceIP);
    $phoneApi = relationCall($phoneApi, $setupApi);
    $phoneApi = startCallTask($phoneApi, $sourceIP, $fromIP, $toIP, $phoneNumber, $extensionNumber, $isDebug);

    byeSound($setupAPI);
    byeSound($phoneApi);

} catch (Exception $e) {
    echo $e;
}
?>
