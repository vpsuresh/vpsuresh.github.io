<?php require_once("webassist/form_validations/wavt_scripts_php.php"); ?>
<?php require_once("webassist/form_validations/wavt_validatedform_php.php"); ?>
<?php 
if (isset($_POST["ServicesRequesthayes_submit"]))  {
  $WAFV_Redirect = "".(htmlentities($_SERVER["PHP_SELF"], ENT_QUOTES))  ."?invalid=true";
  $_SESSION['WAVT_FinalFormTest_Errors'] = "";
  if ($WAFV_Redirect == "")  {
    $WAFV_Redirect = $_SERVER["PHP_SELF"];
  }
  $WAFV_Errors = "";
  $WAFV_Errors .= WAValidateRQ((isset($_POST["Business_Name"])?$_POST["Business_Name"]:"") . "",true,1);
  $WAFV_Errors .= WAValidateRQ((isset($_POST["Contact_Person"])?$_POST["Contact_Person"]:"") . "",true,2);
  $WAFV_Errors .= WAValidatePN((isset($_POST["Phone_Number"])?$_POST["Phone_Number"]:"") . "",false,true,true,3);
  $WAFV_Errors .= WAValidateEM((isset($_POST["email"])?$_POST["email"]:"") . "",true,4);
  $WAFV_Errors .= WAValidateDT((isset($_POST["Date_Needed"])?$_POST["Date_Needed"]:"") . "",true,"","","",false,"","","",true,5);
  $WAFV_Errors .= WAValidateRQ((isset($_POST["Start_Time"])?$_POST["Start_Time"]:"") . "",true,6);
  $WAFV_Errors .= WAValidateRQ((isset($_POST["Appointment_lenght"])?$_POST["Appointment_lenght"]:"") . "",true,7);
  $WAFV_Errors .= WAValidateRQ((isset($_POST["Service_type"])?$_POST["Service_type"]:"") . "",true,8);
  $WAFV_Errors .= WAValidateLE((strtolower(isset($_POST["Security_Code"])?$_POST["Security_Code"]:"")) . "",((isset($_SESSION["captcha_Security_Code"]))?strtolower($_SESSION["captcha_Security_Code"]):"") . "",true,9);
  $WAFV_Errors .= WAValidateRX((isset($_POST["Hidden_Field"])?$_POST["Hidden_Field"]:"") . "","/.*/",false,10);

  if ($WAFV_Errors != "")  {
    PostResult($WAFV_Redirect,$WAFV_Errors,"FinalFormTest");
  }
}
?>
<?php require_once("webassist/email/mail_php.php"); ?>
<?php require_once("webassist/email/mailformatting_php.php"); ?>
<?php
if (!isset($_SESSION))session_start();
if ((isset($_POST["ServicesRequesthayes_submit"])))     {
  //WA Universal Email object="mail"
  set_time_limit(0);
  $EmailRef = "waue_FinalFormTest_1";
  $BurstSize = 200;
  $BurstTime = 1;
  $WaitTime = 1;
  $GoToPage = "thankyou.html";
  $RecipArray = array();
  $StartBurst = time();
  $LoopCount = 0;
  $TotalEmails = 0;
  $RecipIndex = 0;
  // build up recipients array
  $CurIndex = sizeof($RecipArray);
  $RecipArray[$CurIndex] = array();
  $RecipArray[$CurIndex ][] = "ellen@hayesinterpretingservices.com ";
  $TotalEmails += sizeof($RecipArray[$CurIndex]);
  $RealWait = ($WaitTime<0.25)?0.25:($WaitTime+0.1);
  $TimeTracker = Array();
  $TotalBursts = floor($TotalEmails/$BurstSize);
  $AfterBursts = $TotalEmails % $BurstSize;
  $TimeRemaining = ($TotalBursts * $BurstTime) + ($AfterBursts*$RealWait);
  if ($TimeRemaining < ($TotalEmails*$RealWait) )  {
    $TimeRemaining = $TotalEmails*$RealWait;
  }
  $_SESSION[$EmailRef."_Total"] = $TotalEmails;
  $_SESSION[$EmailRef."_Index"] = 0;
  $_SESSION[$EmailRef."_Remaining"] = $TimeRemaining;
  while ($RecipIndex < sizeof($RecipArray))  {
    $EnteredValue = is_string($RecipArray[$RecipIndex][0]);
    $CurIndex = 0;
    while (($EnteredValue && $CurIndex < sizeof($RecipArray[$RecipIndex])) || (!$EnteredValue && $RecipArray[$RecipIndex][0])) {
      $starttime = microtime_float();
      if ($EnteredValue)  {
        $RecipientEmail = $RecipArray[$RecipIndex][$CurIndex];
      }  else  {
        $RecipientEmail = $RecipArray[$RecipIndex][0][$RecipArray[$RecipIndex][2]];
      }
      $EmailsRemaining = ($TotalEmails- $LoopCount);
      $BurstsRemaining = ceil(($EmailsRemaining-$AfterBursts)/$BurstSize);
      $IntoBurst = ($EmailsRemaining-$AfterBursts) % $BurstSize;
      if ($AfterBursts<$EmailsRemaining) $IntoBurst = 0;
      $TimeRemaining = ($BurstsRemaining * $BurstTime * 60) + ((($AfterBursts<$EmailsRemaining)?$AfterBursts:$EmailsRemaining)*$RealWait) - (($AfterBursts>$EmailsRemaining)?0:($IntoBurst*$RealWait));
      if ($TimeRemaining < ($EmailsRemaining*$RealWait) )  {
        $TimeRemaining = $EmailsRemaining*$RealWait;
      }
      $CurIndex ++;
      $LoopCount ++;
      session_commit();
      session_start();
      $_SESSION[$EmailRef."_Index"] = $LoopCount;
      $_SESSION[$EmailRef."_Remaining"] = round($TimeRemaining);
      session_commit();
      wa_sleep($WaitTime);
      include("webassist/email/waue_FinalFormTest_1.php");
      $endtime = microtime_float();
      $TimeTracker[] =$endtime - $starttime;
      $RealWait = array_sum($TimeTracker)/sizeof($TimeTracker);
      if ($LoopCount % $BurstSize == 0 && $CurIndex < sizeof($RecipArray[$RecipIndex]))  {
        $TimePassed = (time() - $StartBurst);
        if ($TimePassed < ($BurstTime*60))  {
          $WaitBurst = ($BurstTime*60) -$TimePassed;
          wa_sleep($WaitBurst);
        }
        else  {
          $TimeRemaining = ($TotalEmails- $LoopCount)*$RealWait;
        }
        $StartBurst = time();
      }
      if (!$EnteredValue)  {
        $RecipArray[$RecipIndex][0] =  mysql_fetch_assoc($RecipArray[$RecipIndex][1]);
      }
    }
    $RecipIndex ++;
  }
  $_SESSION[$EmailRef."_Total"] = 0;
  $_SESSION[$EmailRef."_Index"] = 0;
  $_SESSION[$EmailRef."_Remaining"] = 0;
  session_commit();
  session_start();
  if (function_exists("rel2abs")) $GoToPage = $GoToPage?rel2abs($GoToPage,dirname(__FILE__)):"";
  if ($GoToPage!="")     {
    header("Location: ".$GoToPage);
  }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta name="keywords" content=" deaf resouces, Des Moines, IA, contact, interpreting services  " />
<meta name="description" content="Web sites and email resouces for the deaf and hard of hearing. " />

<meta name="robots" content="index, follow" />
<meta name="GOOGLEBOT" content="index, follow" />

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Hayes Interpreting Services</title>
<link href="css/styles.css" rel="stylesheet" type="text/css"  />
<style>
<!--
#maincontent2 img {
	float:left;
	margin:5px;
	text-align:left
}
.title {
	width:100%;
	margin:o auto;
}
.clrFloat {
	clear:both;
}
p {
	text-align:left;
}
dt {
	font-weight:bold;
}
label{ padding-left:10px;}
-->
</style>



<script src="webassist/progress_bar/jquery-blockui-formprocessing.js" type="text/javascript"></script>
<link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script src="webassist/forms/wa_servervalidation.js" type="text/javascript"></script>
<script src="webassist/forms/wa_clientvalidation.js" type="text/javascript"></script>
<link type="text/css" href="webassist/forms/fd_checkboxlabelright_labelright/Datepicker/css/jquery-ui-1.7.1.custom.css" rel="stylesheet" />
<script type="text/javascript" src="webassist/forms/fd_checkboxlabelright_labelright/Datepicker/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="webassist/forms/fd_checkboxlabelright_labelright/Datepicker/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript">
$(function(){
	$('#Date_Needed').datepicker({
		changeMonth: true,
		changeYear: true,
		onClose: closeDatePicker_Date_Needed
	});
});
function closeDatePicker_Date_Needed() {
	var tElm = $('#Date_Needed');
	if (typeof Date_Needed_Spry != null && typeof Date_Needed_Spry != "undefined") {
		Date_Needed_Spry.validate();
	}
	var docElm = document.getElementById("Date_Needed");
	var tBlur = docElm.getAttribute("onBlur");
	if (!tBlur) tBlur = docElm.getAttribute("onblur");
	if (!tBlur) tBlur = docElm.getAttribute("ONBLUR");
	if (tBlur) {
		tBlur = tBlur.replace(/\bthis\b/g, "docElm");
		eval(tBlur);
	}
}
</script>
<link href="SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css" />
<script src="SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
<link href="SpryAssets/SpryValidationCheckbox.css" rel="stylesheet" type="text/css" />
<script src="SpryAssets/SpryValidationCheckbox.js" type="text/javascript"></script>
<link href="webassist/forms/fd_checkboxlabelright_labelright.css" rel="stylesheet" type="text/css" />


</head>
<body>
<div id="wrapper">
  <div id="header"> <img src="images/Header_Contact.gif" alt="Hayes Interpreting" width="980" height="322" border="0" usemap="#Map" />
    <map name="Map" id="Map">
      <area shape="rect" coords="577,184,701,226" href="FinalFormTest.php" alt="Contact" />
      <area shape="rect" coords="706,180,797,224" href="faq.html" alt="FAQ" />
      <area shape="rect" coords="808,175,952,222" href="resources.html" alt="Resources" />
      <area shape="rect" coords="335,200,448,243" href="index.html" alt="Home" />
      <area shape="rect" coords="456,190,570,229" href="about.html" alt="About" />
    </map>
  </div>
  <div id="maincontent2">
    <h1>CONTACT</h1>
    <div class="title">
      <table width="805px">
        <tr>
          <td width="157">&nbsp;</td>
          <td width="233"><p ><img src="images/DSC_0007a V2rs.jpg" width="200" height="133" alt="Sign for Contact" /></p></td>
          <td  align="left" width="295"><h4 >Hayes Interpreting Services, LLC<br />
              P.O. Box 244<br />
              Johnston, Iowa 50131 </h4></td>
          <td width="100">&nbsp;</td>
        </tr>
      </table>
    </div>
    <h1 class="clrFloat">Hayes Interpreting Services, LLC provides:</h1>
    <p style="text-align:center; font-stretch:expanded; font-size:24px"> Services at a Reasonable Rate<br />
      Travel outside the Des Moines Area<br />
      24 hours a day, 7 days a week</p>
    <p><strong>I have a 24 hour cancellation policy and do charge portal to   portal.  You don't need to worry about payment during the appointment, I will   invoice to your office at the end of the current month. I will include all   information necessary for you to match my service with your calendar. </strong></p>
   
    <table width="805px">
      <tr>
        <td width="94">&nbsp;</td>
        <td width="332"><dl >
            <dt>Business:</dt>
            <dd>515 - 669 - 7817 </dd>
            <br />
            <dt>Fax:</dt>
            <dd>515 - 253 - 9559 </dd>
            <br />
            <dt>Videophone:</dt>
            <dd>515 - 957 - 1104</dd>
            <br />
            <dt> Email:</dt>
            <dd><a href="mailto:hayesinterp@aol.com">Hayesinterp@aol.com</a></dd>
          </dl></td>
        <td width="265"><p ><img src="images/DSC_0042 V2rs.jpg"  alt="fun picture" /></p></td>
        <td width="94">&nbsp;</td>
      </tr>
    </table>
    <p>If you prefer to request services by email, please fill in the information below and I will confirm your request back to the address you provide.</p>
   
    <p>&nbsp;</p>


<div id="ServicesRequesthayes_checkboxlabelright_labelright_ProgressWrapper">
  <form class="checkboxlabelright_labelright" id="ServicesRequesthayes_checkboxlabelright_labelright" name="ServicesRequesthayes_checkboxlabelright_labelright" method="post" action="<?php echo(htmlentities($_SERVER["PHP_SELF"], ENT_QUOTES)); ?>">
    <!--
WebAssist CSS Form Builder - Form v1
CC: Contact
CP: Services Request hayes 6
TC: checkbox label right
TP: label right
-->
    <ul class="checkboxlabelright_labelright">
      <li>
        <fieldset class="checkboxlabelright_labelright" id="Appointment_request">
          <legend class="groupHeader">Appointment request</legend>
          <ul class="formList">
            <li class="formItem"> <span class="fieldsetDescription"> Required * </span> </li>
            <li class="formItem">
              <div class="formGroup">
                <div class="lineGroup">
                  <div class="fullColumnGroup">
                    <label for="Business_Name" class="sublabel" > Business Name<span class="requiredIndicator">&nbsp;*</span></label>
                    <div class="errorGroup">
                      <div class="fieldPair">
                        <div class="fieldGroup"> <span id="Business_Name_Spry">
                          <input id="Business_Name" name="Business_Name" type="text" value="<?php echo((isset($_GET["invalid"])?ValidatedField("FinalFormTest","Business_Name"):"")); ?>" class="formTextfield_Large" tabindex="1"  onblur="hideServerError('Business_Name_ServerError');"/>
                          <span class="textfieldRequiredMsg">Please enter your first name</span> </span>
                          <?php
if (ValidatedField('FinalFormTest','FinalFormTest'))  {
  if ((strpos((",".ValidatedField("FinalFormTest","FinalFormTest").","), "," . "1" . ",") !== false || "1" == ""))  {
    if (!(false))  {
?>
                            <span class="serverInvalidState" id="Business_Name_ServerError">Please enter your first name</span>
                            <?php //WAFV_Conditional FinalFormTest.php FinalFormTest(1:)
    }
  }
}?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="lineGroup">
                  <div class="fullColumnGroup">
                    <label for="Contact_Person" class="sublabel" > Contact Person<span class="requiredIndicator">&nbsp;*</span></label>
                    <div class="errorGroup">
                      <div class="fieldPair">
                        <div class="fieldGroup"> <span id="Contact_Person_Spry">
                          <input id="Contact_Person" name="Contact_Person" type="text" value="<?php echo((isset($_GET["invalid"])?ValidatedField("FinalFormTest","Contact_Person"):"")); ?>" class="formTextfield_Large" tabindex="2"  onblur="hideServerError('Contact_Person_ServerError');"/>
                          <span class="textfieldRequiredMsg">Please enter your last name</span> </span>
                          <?php
if (ValidatedField('FinalFormTest','FinalFormTest'))  {
  if ((strpos((",".ValidatedField("FinalFormTest","FinalFormTest").","), "," . "2" . ",") !== false || "2" == ""))  {
    if (!(false))  {
?>
                            <span class="serverInvalidState" id="Contact_Person_ServerError">Please enter your last name</span>
                            <?php //WAFV_Conditional FinalFormTest.php FinalFormTest(2:)
    }
  }
}?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="lineGroup">
                  <div class="fullColumnGroup">
                    <label for="Phone_Number" class="sublabel" > Phone<span class="requiredIndicator">&nbsp;*</span></label>
                    <div class="errorGroup">
                      <div class="fieldPair">
                        <div class="fieldGroup"> <span id="Phone_Number_Spry">
                          <input id="Phone_Number" name="Phone_Number" type="text" value="<?php echo((isset($_GET["invalid"])?ValidatedField("FinalFormTest","Phone_Number"):"")); ?>" class="formTextfield_Large" tabindex="3"  onblur="WAValidatePN(document.getElementById('ServicesRequesthayes_checkboxlabelright_labelright').Phone_Number,'',false,true,'x (xxx) xxx-xxxx',document.getElementById('ServicesRequesthayes_checkboxlabelright_labelright').Phone_Number,0,true);hideServerError('Phone_Number_ServerError');"/>
                          <span class="textfieldInvalidFormatMsg">Invalid format.</span><span class="textfieldRequiredMsg">Please enter a 10 digit phone number</span> </span>
                          <?php
if (ValidatedField('FinalFormTest','FinalFormTest'))  {
  if ((strpos((",".ValidatedField("FinalFormTest","FinalFormTest").","), "," . "3" . ",") !== false || "3" == ""))  {
    if (!(false))  {
?>
                            <span class="serverInvalidState" id="Phone_Number_ServerError">Please enter a 10 digit phone number</span>
                            <?php //WAFV_Conditional FinalFormTest.php FinalFormTest(3:)
    }
  }
}?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="lineGroup">
                  <div class="fullColumnGroup">
                    <label for="email" class="sublabel" > Email Address<span class="requiredIndicator">&nbsp;*</span></label>
                    <div class="errorGroup">
                      <div class="fieldPair">
                        <div class="fieldGroup"> <span id="email_Spry">
                          <input id="email" name="email" type="text" value="<?php echo((isset($_GET["invalid"])?ValidatedField("FinalFormTest","email"):"")); ?>" class="formTextfield_Large" tabindex="4"  onblur="hideServerError('email_ServerError');"/>
                          <span class="textfieldInvalidFormatMsg">Invalid format.</span><span class="textfieldRequiredMsg"> </span> </span>
                          <?php
if (ValidatedField('FinalFormTest','FinalFormTest'))  {
  if ((strpos((",".ValidatedField("FinalFormTest","FinalFormTest").","), "," . "4" . ",") !== false || "4" == ""))  {
    if (!(false))  {
?>
                            <span class="serverInvalidState" id="email_ServerError"> </span>
                            <?php //WAFV_Conditional FinalFormTest.php FinalFormTest(4:)
    }
  }
}?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="formItem">
              <div class="formGroup">
                <div class="lineGroup">
                  <div class="fullColumnGroup">
                    <label for="Date_Needed" class="sublabel" > Date Service Needed:<span class="requiredIndicator">&nbsp;*</span></label>
                    <div class="errorGroup">
                      <div class="fieldPair">
                        <div class="fieldGroup"> <span id="Date_Needed_Spry">
                          <input id="Date_Needed" name="Date_Needed" type="text" value="<?php echo((isset($_GET["invalid"])?ValidatedField("FinalFormTest","Date_Needed"):"")); ?>" class="formTextfield_Medium" tabindex="5"  onblur="hideServerError('Date_Needed_ServerError');"/>
                          <span class="textfieldInvalidFormatMsg">Invalid format.</span><span class="textfieldRequiredMsg">Please enter or select a date</span> </span>
                          <?php
if (ValidatedField('FinalFormTest','FinalFormTest'))  {
  if ((strpos((",".ValidatedField("FinalFormTest","FinalFormTest").","), "," . "5" . ",") !== false || "5" == ""))  {
    if (!(false))  {
?>
                            <span class="serverInvalidState" id="Date_Needed_ServerError">Please enter or select a date</span>
                            <?php //WAFV_Conditional FinalFormTest.php FinalFormTest(5:)
    }
  }
}?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="lineGroup">
                  <div class="columnGroup">
                    <label for="Start_Time" class="sublabel" > Start Time of Service:<span class="requiredIndicator">&nbsp;*</span></label>
                    <div class="errorGroup">
                      <div class="fieldPair">
                        <div class="fieldGroup"> <span id="Start_Time_Spry">
                          <select class="formListfield_Medium" name="Start_Time[]" id="Start_Time" tabindex="6" multiple="multiple" onchange="hideServerError('Start_Time_ServerError');">
                            <option value="No selection" <?php if (!(strcmp("No selection", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>Select</option>
                            <option value="8:00 AM" <?php if (!(strcmp("8:00 AM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>08:00 AM</option>
                            <option value="8:30 AM" <?php if (!(strcmp("8:30 AM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>08:30 AM</option>
                            <option value="9:00 AM" <?php if (!(strcmp("9:00 AM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>09:00 AM</option>
                            <option value="9:30 AM" <?php if (!(strcmp("9:30 AM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>09:30 AM</option>
                            <option value="10:00 AM" <?php if (!(strcmp("10:00 AM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>10:00 AM</option>
                            <option value="10:30 AM" <?php if (!(strcmp("10:30 AM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>10:30 AM</option>
                            <option value="11:00 AM" <?php if (!(strcmp("11:00 AM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>11:00 Am</option>
                            <option value="11:30 AM" <?php if (!(strcmp("11:30 AM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>11:30 AM</option>
                            <option value="12 Noon" <?php if (!(strcmp("12 Noon", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>12 Noon</option>
                            <option value="12:30 PM" <?php if (!(strcmp("12:30 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>12:30 PM</option>
                            <option value="1:00 PM" <?php if (!(strcmp("1:00 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>01:00 PM</option>
                            <option value="1:30 PM" <?php if (!(strcmp("1:30 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>01:30 PM</option>
                            <option value="2:00 PM" <?php if (!(strcmp("2:00 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>02:00 PM</option>
                            <option value="2:30 PM" <?php if (!(strcmp("2:30 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>02:30 PM</option>
                            <option value="3:00 PM" <?php if (!(strcmp("3:00 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>03:00 PM</option>
                            <option value="3:30 PM" <?php if (!(strcmp("3:30 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>03:30 PM</option>
                            <option value="4:00 PM" <?php if (!(strcmp("4:00 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>04:00 PM</option>
                            <option value="4:30 PM" <?php if (!(strcmp("4:30 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>04:30 PM</option>
                            <option value="5:00 PM" <?php if (!(strcmp("5:00 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>05:00 PM</option>
                            <option value="5:30 PM" <?php if (!(strcmp("5:30 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>05:30 PM</option>
                            <option value="6:00 PM" <?php if (!(strcmp("6:00 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>06:00 PM</option>
                            <option value="6:30 PM" <?php if (!(strcmp("6:30 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>06:30 PM</option>
                            <option value="7:00 PM" <?php if (!(strcmp("7:00 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>07:00 PM</option>
                            <option value="7:30 PM" <?php if (!(strcmp("7:30 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>07:30 PM</option>
                            <option value="8:00 PM" <?php if (!(strcmp("8:00 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>08:00 PM</option>
                            <option value="8:30 PM" <?php if (!(strcmp("8:30 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>08:30 PM</option>
                            <option value="9:00 PM" <?php if (!(strcmp("9:00 PM", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Start_Time"):"")))) {echo "selected=\"selected\"";} ?>>09:00 PM</option>
                          </select>
                          <span class="selectRequiredMsg">Please select a time</span> </span>
                          <?php
if (ValidatedField('FinalFormTest','FinalFormTest'))  {
  if ((strpos((",".ValidatedField("FinalFormTest","FinalFormTest").","), "," . "6" . ",") !== false || "6" == ""))  {
    if (!(false))  {
?>
                            <span class="serverInvalidState" id="Start_Time_ServerError">Please select a time</span>
                            <?php //WAFV_Conditional FinalFormTest.php FinalFormTest(6:)
    }
  }
}?>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="wideColumnGroup">
                    <label for="Appointment_lenght" class="sublabel" > Length of Appointment:<span class="requiredIndicator">&nbsp;*</span></label>
                    <div class="errorGroup">
                      <div class="fieldPair">
                        <div class="fieldGroup"> <span id="Appointment_lenght_Spry">
                          <select class="formListfield_Small" name="Appointment_lenght[]" id="Appointment_lenght" tabindex="7" multiple="multiple" onchange="hideServerError('Appointment_lenght_ServerError');">
                            <option value="No selection" <?php if (!(strcmp("No selection", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Appointment_lenght"):"")))) {echo "selected=\"selected\"";} ?>>Select</option>
                            <option value="1" <?php if (!(strcmp("1", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Appointment_lenght"):"")))) {echo "selected=\"selected\"";} ?>>1 hour</option>
                            <option value="2" <?php if (!(strcmp("2", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Appointment_lenght"):"")))) {echo "selected=\"selected\"";} ?>>2 hours</option>
                            <option value="3" <?php if (!(strcmp("3", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Appointment_lenght"):"")))) {echo "selected=\"selected\"";} ?>>3 hours</option>
                            <option value="4" <?php if (!(strcmp("4", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Appointment_lenght"):"")))) {echo "selected=\"selected\"";} ?>>4 hours</option>
                            <option value="5" <?php if (!(strcmp("5", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Appointment_lenght"):"")))) {echo "selected=\"selected\"";} ?>>5 hours</option>
                            <option value="6" <?php if (!(strcmp("6", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Appointment_lenght"):"")))) {echo "selected=\"selected\"";} ?>>6 hours</option>
                            <option value="7" <?php if (!(strcmp("7", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Appointment_lenght"):"")))) {echo "selected=\"selected\"";} ?>>7 hours</option>
                            <option value="8" <?php if (!(strcmp("8", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Appointment_lenght"):"")))) {echo "selected=\"selected\"";} ?>>8 hours</option>
                            <option value="9" <?php if (!(strcmp("9", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Appointment_lenght"):"")))) {echo "selected=\"selected\"";} ?>>9 hours</option>
                            <option value="" <?php if (!(strcmp("", (isset($_GET["invalid"])?ValidatedField("FinalFormTest","Appointment_lenght"):"")))) {echo "selected=\"selected\"";} ?>></option>
                          </select>
                          <span class="selectRequiredMsg">Please select an aproximate length of time.</span> </span>
                          <?php
if (ValidatedField('FinalFormTest','FinalFormTest'))  {
  if ((strpos((",".ValidatedField("FinalFormTest","FinalFormTest").","), "," . "7" . ",") !== false || "7" == ""))  {
    if (!(false))  {
?>
                            <span class="serverInvalidState" id="Appointment_lenght_ServerError">Please select an aproximate length of time.</span>
                            <?php //WAFV_Conditional FinalFormTest.php FinalFormTest(7:)
    }
  }
}?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="lineGroup">
                  <div class="fullColumnGroup">
                    <label for="Service_type__1" class="sublabel" > Type of Service:<span class="requiredIndicator">&nbsp;*</span></label>
                    <div class="errorGroup">
                      <div class="fieldPair">
                        <div class="fieldGroup"> <span id="Service_type__1_Spry"> <span class="checkFieldGroup_Wide"> <span class="checkGroup_Wide">
                          <label class="checkSublabel_Wide" for="Service_type__1" onblur="hideServerError('Service_type__1_ServerError');">
                            <input type="checkbox" name="Service_type[]" id="Service_type__1" value="Medical" class="formCheckboxField_Standard" <?php if (array_search("Medical", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Service_type"))?ValidatedField("FinalFormTest","Service_type"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="8" />
                            &nbsp;Medical</label>
                          <label class="checkSublabel_Wide" for="Service_type__2" onblur="hideServerError('Service_type__1_ServerError');">
                            <input type="checkbox" name="Service_type[]" id="Service_type__2" value="Business" class="formCheckboxField_Standard" <?php if (array_search("Business", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Service_type"))?ValidatedField("FinalFormTest","Service_type"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="9" />
                            &nbsp;Business</label>
                          <label class="checkSublabel_Wide" for="Service_type__3" onblur="hideServerError('Service_type__1_ServerError');">
                            <input type="checkbox" name="Service_type[]" id="Service_type__3" value="Education" class="formCheckboxField_Standard" <?php if (array_search("Education", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Service_type"))?ValidatedField("FinalFormTest","Service_type"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="10" />
                            &nbsp;Education</label>
                          <label class="checkSublabel_Wide" for="Service_type__4" onblur="hideServerError('Service_type__1_ServerError');">
                            <input type="checkbox" name="Service_type[]" id="Service_type__4" value="Funeral" class="formCheckboxField_Standard" <?php if (array_search("Funeral", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Service_type"))?ValidatedField("FinalFormTest","Service_type"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="11" />
                            &nbsp;Funeral</label>
                          <label class="checkSublabel_Wide" for="Service_type__5" onblur="hideServerError('Service_type__1_ServerError');">
                            <input type="checkbox" name="Service_type[]" id="Service_type__5" value="Wedding" class="formCheckboxField_Standard" <?php if (array_search("Wedding", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Service_type"))?ValidatedField("FinalFormTest","Service_type"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="12" />
                            &nbsp;Wedding</label>
                          <label class="checkSublabel_Wide" for="Service_type__6" onblur="hideServerError('Service_type__1_ServerError');">
                            <input type="checkbox" name="Service_type[]" id="Service_type__6" value="Private " class="formCheckboxField_Standard" <?php if (array_search("Private ", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Service_type"))?ValidatedField("FinalFormTest","Service_type"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="13" />
                            &nbsp;Private </label>
                          <label class="checkSublabel_Wide" for="Service_type__7" onblur="hideServerError('Service_type__1_ServerError');">
                            <input type="checkbox" name="Service_type[]" id="Service_type__7" value="Counseling" class="formCheckboxField_Standard" <?php if (array_search("Counseling", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Service_type"))?ValidatedField("FinalFormTest","Service_type"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="14" />
                            &nbsp;Counseling</label>
                          <label class="checkSublabel_Wide" for="Service_type__8" onblur="hideServerError('Service_type__1_ServerError');">
                            <input type="checkbox" name="Service_type[]" id="Service_type__8" value="Conferences" class="formCheckboxField_Standard" <?php if (array_search("Conferences", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Service_type"))?ValidatedField("FinalFormTest","Service_type"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="15" />
                            &nbsp;Conference</label>
                          <label class="checkSublabel_Wide" for="Service_type__9" onblur="hideServerError('Service_type__1_ServerError');">
                            <input type="checkbox" name="Service_type[]" id="Service_type__9" value="Religious Service" class="formCheckboxField_Standard" <?php if (array_search("Religious Service", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Service_type"))?ValidatedField("FinalFormTest","Service_type"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="16" />
                            &nbsp;Church</label>
                          <label class="checkSublabel_Wide" for="Service_type__10" onblur="hideServerError('Service_type__1_ServerError');">
                            <input type="checkbox" name="Service_type[]" id="Service_type__10" value="Other" class="formCheckboxField_Standard" <?php if (array_search("Other", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Service_type"))?ValidatedField("FinalFormTest","Service_type"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="17" />
                            &nbsp;All Other</label>
                          </span> </span> <span class="checkboxMinSelectionsMsg">Please select a valid value.</span><span class="checkboxRequiredMsg"> </span> </span>
                          <?php
if (ValidatedField('FinalFormTest','FinalFormTest'))  {
  if ((strpos((",".ValidatedField("FinalFormTest","FinalFormTest").","), "," . "8" . ",") !== false || "8" == ""))  {
    if (!(false))  {
?>
                            <span class="serverInvalidState" id="Service_type__1_ServerError"> </span>
                            <?php //WAFV_Conditional FinalFormTest.php FinalFormTest(8:)
    }
  }
}?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="formItem">
              <div class="formGroup">
                <div class="lineGroup">
                  <div class="fullColumnGroup">
                    <label for="Comments" class="sublabel" > Comments</label>
                    <div class="errorGroup">
                      <div class="fieldPair">
                        <div class="fieldGroup"> <span>
                          <textarea name="Comments" id="Comments" class="formTextarea_Medium" rows="1" cols="1" tabindex="18"><?php echo((isset($_GET["invalid"])?ValidatedField("FinalFormTest","Comments"):"")); ?></textarea>
                        </span> </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="formItem">
              <div class="formGroup">
                <div class="lineGroup">
                  <div class="fullColumnGroup">
                    <label for="Confirm_By__1" class="sublabel" > Confirm by</label>
                    <div class="errorGroup">
                      <div class="fieldPair">
                        <div class="fieldGroup"> <span> <span class="checkFieldGroup_Wide"> <span class="checkGroup_Wide">
                          <label class="checkSublabel_Wide" for="Confirm_By__1">
                            <input type="checkbox" name="Confirm_By[]" id="Confirm_By__1" value="Email" class="formCheckboxField_Standard" <?php if (array_search("Email", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Confirm_By"))?ValidatedField("FinalFormTest","Confirm_By"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="19" />
                            &nbsp;Email</label>
                          <label class="checkSublabel_Wide" for="Confirm_By__2">
                            <input type="checkbox" name="Confirm_By[]" id="Confirm_By__2" value="Phone" class="formCheckboxField_Standard" <?php if (array_search("Phone", ((isset($_GET["invalid"]) && ValidatedField("FinalFormTest","Confirm_By"))?ValidatedField("FinalFormTest","Confirm_By"):array())) !== false) {echo "checked=\"checked\"";} ?> tabindex="20" />
                            &nbsp;Phone</label>
                        </span> </span> </span> </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="formItem">
              <div class="formGroup">
                <div class="lineGroup">
                  <div class="fullColumnGroup">
                    <div class="fullColumnGroup">
                      <label for="Security_Code" class="sublabel" >&nbsp;</label>
                      <div class="errorGroup">
                        <div class="fieldPair">
                          <div class="fieldGroup"> <span>
                          
                           <img id="capt" src="webassist/captcha/wavt_captchasecurityimages.php?
                           gridfreq=5&
                           gridorder=behind&
                            noisefreq=20&
                           noiseorder=behind&                          
                           field=Security_Code&amp;font=fonts/MOM_T___.TTF" alt="Security Code" class="Captcha" /> 
                        
                        <img src="webassist/captcha/images/refresh.png" height="18" onclick="document.getElementById('capt').src+='&ref=1'" />
                        <br />
                        </span> </div>
                        
                        </div>
                      </div>
                    </div>
                    <div class="fullColumnGroup" style="clear:left;">
                      <label for="Security_Code" class="sublabel" > Security code<span class="requiredIndicator">&nbsp;*</span></label>
                      <div class="errorGroup">
                        <div class="fieldPair">
                          <div class="fieldGroup"> <span id="Security_Code_Spry">
                            <input id="Security_Code" name="Security_Code" type="text" value="" class="formTextfield_Large" tabindex="21"  onblur="hideServerError('Security_Code_ServerError');"/>
                            <span class="textfieldRequiredMsg">Entered text does not match; please try again</span> </span>
                            <?php
if (ValidatedField('FinalFormTest','FinalFormTest'))  {
  if ((strpos((",".ValidatedField("FinalFormTest","FinalFormTest").","), "," . "9" . ",") !== false || "9" == ""))  {
    if (!(false))  {
?>
                              <span class="serverInvalidState" id="Security_Code_ServerError">Entered text does not match; please try again</span>
                              <?php //WAFV_Conditional FinalFormTest.php FinalFormTest(9:)
    }
  }
}?>
				<div><br /></div><!--provides space -->
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="formItem"> <span class="buttonFieldGroup" >
              <input id="Hidden_Field" name="Hidden_Field" type="hidden" value="<?php echo((isset($_GET["invalid"])?ValidatedField("FinalFormTest","Hidden_Field"):"")); ?>" />
              <input class="formButton" name="ServicesRequesthayes_submit" type="submit" id="ServicesRequesthayes_submit" value="Send Request"  onclick="clearAllServerErrors('ServicesRequesthayes6_checkboxlabelright_labelright')" />
              
             <input class="formButton" name="ServicesRequesthayes_submit" type="reset" id="ServicesRequesthayes_submit" value="Clear Form"  onclick="clearAllServerErrors('ServicesRequesthayes6_checkboxlabelright_labelright')" /> 
              
            </span> </li>
          </ul>
        </fieldset>
      </li>
    </ul>
  </form>
</div>
<div id="ServicesRequesthayes_checkboxlabelright_labelright_ProgressMessageWrapper" class="blockUIOverlay" style="display:none;">
  <script type="text/javascript">
WADFP_SetProgressToForm('ServicesRequesthayes_checkboxlabelright_labelright', 'ServicesRequesthayes_checkboxlabelright_labelright_ProgressMessageWrapper', WADFP_Theme_Options['BigSpin:Slate']);
</script>
  <div id="ServicesRequesthayes_checkboxlabelright_labelright_ProgressMessage" >
    <p style="margin:10px; padding:5px;" ><img src="webassist/progress_bar/images/slate-largespin.gif" alt="" title="" style="vertical-align:middle;" />&nbsp;&nbsp;Please wait</p>
  </div>
</div>

<p><br />
    </p>
  </div>
  <div id="footer">
    <table width="870" align="center">
      <tr>
        <td width="50">&nbsp;</td>
        <td width="120"><a href="http://www.rid.org/"><img src="./images/rid logo.gif"  alt="RID Logo LINK" width="106" height="40" /></a></td>
        <td width="120"><a href="http://new.iowastaterid.org/"><img src="images/IA_rid.gif" width="106" height="40" alt="Iowa rid link" /></a></td>
        <td width="120"><a href="http://www.deafservices.iowa.gov/"><img src="images/DSCI.jpg" width="106" height="40" alt="DSCI Link" /></a></td>
        <td width="120"><a href="https://www.facebook.com/Hayesinterpretingservices"><img src="images/faceBook.jpg" width="106" height="40" alt="FaceBook Link" /></td>
        <td width="300">&copy; Web Designed and Hosted by<br />
          <a href="http://www.lehallco.com"> L E Hall and Company</a></td>
      </tr>
    </table>
 </div>
</div>
<script type="text/javascript">
<!--
var Business_Name_Spry = new Spry.Widget.ValidationTextField("Business_Name_Spry", "none",{validateOn:["blur"]});
var Contact_Person_Spry = new Spry.Widget.ValidationTextField("Contact_Person_Spry", "none",{validateOn:["blur"]});
var Phone_Number_Spry = new Spry.Widget.ValidationTextField("Phone_Number_Spry", "phone_number", { format:'phone_us' , validateOn:["blur"]});
var email_Spry = new Spry.Widget.ValidationTextField("email_Spry", "email",{validateOn:["blur"]});
var Date_Needed_Spry = new Spry.Widget.ValidationTextField("Date_Needed_Spry", "date", { format:'mm/dd/yyyy' , validateOn:["blur"]});
var Start_Time_Spry = new Spry.Widget.ValidationSelect("Start_Time_Spry",{validateOn:["change"]});
var Appointment_lenght_Spry = new Spry.Widget.ValidationSelect("Appointment_lenght_Spry",{validateOn:["change"]});
var Service_type__1_Spry = new Spry.Widget.ValidationCheckbox("Service_type__1_Spry", { minSelections:1 , validateOn:["click"]});
var Security_Code_Spry = new Spry.Widget.ValidationTextField("Security_Code_Spry", "none",{validateOn:["blur"]});
//-->
</script>

</body>
</html>