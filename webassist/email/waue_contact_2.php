<?php
$MailAttachments = "";
$MailBCC         = "";
$MailCC          = "";
$MailTo          = "";
$MailBodyFormat  = "";
$MailBody        = "";
$MailImportance  = "";
$MailFrom        = "web@hayesinterpretingservices.com";
$MailSubject     = "Web -  Form Request";
$_SERVER["QUERY_STRING"] = "";

//Global Variables

  $WA_MailObject = WAUE_Definition("","","","","","");

if ($RecipientEmail)     {
  $WA_MailObject = WAUE_AddRecipient($WA_MailObject,$RecipientEmail);
}
else      {
  //To Entries
}

//Additional Headers

//Attachment Entries

//BCC Entries

//CC Entries
  $WA_MailObject = WAUE_AddCC($WA_MailObject,"lehall@dmacc.edu");

//Body Format
  $WA_MailObject = WAUE_BodyFormat($WA_MailObject,0);

//Set Importance
  $WA_MailObject = WAUE_SetImportance($WA_MailObject,"1");

//Start Mail Body
//End Mail Body

$WA_MailObject = WAUE_SendMail($WA_MailObject,$MailAttachments,$MailBCC,$MailCC,$MailTo,$MailImportance,$MailFrom,$MailSubject,$MailBody,"waue_contact_2");

if (isset($_SESSION["waue_contact_2_Status"])) {
  $MailLogBindings = new WAUE_Log_Bindings();
  //Start Log Bindings
  //End Log Bindings
  $MailLogBindings->SuccessOrFailure->MailRef = "waue_contact_2";
  $MailLogBindings->Success->MailRef = "waue_contact_2";
  $MailLogBindings->Failure->MailRef = "waue_contact_2";
  $MailLogBindings->processLog(($_SESSION["waue_contact_2_Status"] == "Failure"));
}
$WA_MailObject = null;
?>