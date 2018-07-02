<?php
require_once('PhpSIP.class.php');

/* Sends NOTIFY to reset Linksys phone */

function getInviteInstance($input, $from, $to) {
    $sourceIP = '192.168.100.222';

  $extensionNumber = '6001';

  $oriFrom = rand(30001, 31000);
  $userPhoneNumber = '0987654321';


  echo "staring setup call\n";
  $setupAPI = new PhpSIP($sourceIP);
  $setupAPI->setDebug(true);
  // $setupAPI->addHeader('Event: resync');
  $setupAPI->setMethod('INVITE');
  $setupAPI->setFrom("sip:$oriFrom@localhost");
  $setupAPI->setUri("sip:$extensionNumber@localhost");

  $setupAPI->addHeader('Allow: INVITE, ACK, CANCEL, BYE, PRACK, NOTIFY, REFER, SUBSCRIBE, OPTIONS, UPDATE, INFO');
  $setupAPI->addHeader('Supported: replaces,timer,path');
  $setupAPI->addHeader('Session-Expires: 1800;refresher=uac');
  $setupAPI->addHeader('Min-SE: 900');
  $setupAPI->addHeader('Alert-Info: <urn:alert:tone:internal>');

  $setupAPI->setBody("v=0
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

  return $setupAPI;
}

try
{
  $sourceIP = '192.168.100.222';

  $extensionNumber = '6001';

  $oriFrom = rand(30001, 31000);
  $userPhoneNumber = '0987654321';


  echo "staring setup call\n";
  $setupAPI = new PhpSIP($sourceIP);
  $setupAPI->setDebug(true);
  // $setupAPI->addHeader('Event: resync');
  $setupAPI->setMethod('INVITE');
  $setupAPI->setFrom("sip:$oriFrom@localhost");
  $setupAPI->setUri("sip:$extensionNumber@localhost");

  $setupAPI->addHeader('Allow: INVITE, ACK, CANCEL, BYE, PRACK, NOTIFY, REFER, SUBSCRIBE, OPTIONS, UPDATE, INFO');
  $setupAPI->addHeader('Supported: replaces,timer,path');
  $setupAPI->addHeader('Session-Expires: 1800;refresher=uac');
  $setupAPI->addHeader('Min-SE: 900');
  $setupAPI->addHeader('Alert-Info: <urn:alert:tone:internal>');

  $setupAPI->setBody("v=0
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

  $res = $setupAPI->send();

  // if($res == 200) {
  //   echo "response: $res, setup success\n\n";

  //   echo "staring refer call\n";
  //   $referAPI = new PhpSIP($sourceIP);
  //   $referAPI->setDebug(true);
  //   // $referAPI->addHeader('Event: resync');
  //   $referAPI->setMethod('INVITE');
  //   $referAPI->setFrom("sip:userPhoneNumber@localhost");
  //   $referAPI->setUri("sip:$extensionNumber@localhost");
  //   $res = $referAPI->send();

  //   if ($res == 200) {
  //       echo "refer call success, bye to setup call\n\n";
  //       $setupAPI->setMethod('send()BYE');
  //       $res = $setupAPI->;

  //       echo "response: $res, refer call success\n\n";
  //   } else {
  //       echo "response: $res, refer failed\n\n";
  //   }
  // } else {
  //   echo "response: $res, setup failed\n\n";

  // }
} catch (Exception $e) {
  
  echo $e;
}


?>
