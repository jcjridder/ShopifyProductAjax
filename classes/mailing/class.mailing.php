<?php

$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../../');


if(!defined(MailSendHost)){
  require ($_SERVER['DOCUMENT_ROOT']."/classes/class.standardvalues.php");
}

if(!class_exists("PHPMailer\PHPMailer\PHPMailer")){
  require ($_SERVER['DOCUMENT_ROOT'] . "/classes/mailing/phpmailer/Exception.php");
  require ($_SERVER['DOCUMENT_ROOT'] . "/classes/mailing/phpmailer/OAuth.php");
  require ($_SERVER['DOCUMENT_ROOT'] . "/classes/mailing/phpmailer/SMTP.php");
  require ($_SERVER['DOCUMENT_ROOT'] . "/classes/mailing/phpmailer/PHPMailer.php");
}

class Mailing {
  
  public $mail;
  
  function sendThisEmail($MailTo,$Message,$Subject,$Attachment){

    $hostName = hostName;
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(); 
    $mail->SMTPDebug = 0; // Enable verbose debug output. This wil mean you need to return $mail and catch it where you call the function
    $mail->isSMTP();
    $mail->Host = MailSendHost;
    $mail->SMTPAuth = true;
    $mail->Username = MailSendUserName."@".$hostName;
    $mail->Password = MailSendUserPass;
    $mail->SMTPSecure = MailSMTPSecurity;
    $mail->Port = MailSMTPPort;         
    
    $mail->setFrom(MailSendUserName."@".$hostName, MailSendUserTitle);

    // Recipients array or string.
    // The array is a multi level array existing of several arrays. Example: array(array("email" => "johndoe@example.com", "name" => "John Doe"),array("email" => "chrisfoo@example.com", "name" => "Chris Foo"));
    // The string is split with '~_^'. Example: "johndoe@example.com~_^John Doe";
    if(is_array($MailTo)){
      for($i=0;$i<count($MailTo);$i++){
        $mail->addAddress($MailTo[$i]["email"], $MailTo[$i]["name"]);
      }
    }else{
      $towardsAddress = explode("~_^", $MailTo);
      $mail->addAddress($towardsAddress[0], $towardsAddress[1]);
    }
    $mail->addReplyTo(MailReplyUserName."@".$hostName, MailReplyUserTitle);
    
    
    // Can be an array or string with a path relative fromt this file. like: '../../Uploaded/Exampled.pdf' .
    if($Attachment != ""){
      if(is_array($Attachment)){
        for($a=0;$a<count($Attachment);$a++){
           $mail->AddAttachment($Attachment[$a]);
        }
      }else{
        $mail->AddAttachment($Attachment);
      }
    }

    $mail->isHTML(true);
    $mail->Subject = $Subject;
    $mail->Body = $Message;
    $mail->AltBody = MailAltBody;
    
    // echo MailSendUserName;
    // echo "<br>";
    // echo MailSendUserPass;
    // echo "<br>";
    // echo $hostName;
    // exit();
    if (!$mail->Send()) {
        return "Mailer Error: " . $mail->ErrorInfo;
    } else {
        // return "Message has been sent";
    }

  }
}

?>