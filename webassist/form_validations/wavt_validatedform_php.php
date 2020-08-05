<?php
if (!session_id())  {
  session_start();
}
function ValidatedField($page,$field)  {
  $theFields= "";
  $retVal = "";
  if (isset($_SESSION["WAVT_".$page."_Errors"]))  {
    $theFields = "&".$_SESSION["WAVT_".$page."_Errors"];
  }
  if (strpos($theFields,"&WAVT_".$field."=") !== false)  {
    $retVal = substr($theFields,strpos($theFields,"&WAVT_".$field."=")+strlen("&WAVT_".$field."="));
  } else if (strpos($theFields,"&WAVT_".$field."[0]=") !== false) {
		$retVal = array();
		$arrIndex = 0;
		while (strpos($theFields,"&WAVT_".$field."[".$arrIndex."]=") !== false)  {
			$arrVal = substr($theFields,strpos($theFields,"&WAVT_".$field."[".$arrIndex."]=")+strlen("&WAVT_".$field."[".$arrIndex."]="));
			if (strpos($arrVal,"&WAVT_") !== false)  $arrVal = substr($arrVal,0,strpos($arrVal,"&WAVT_"));
      $retVal[] = htmlspecialchars($arrVal);
			$arrIndex++;
		}
		return $retVal;
	}
  if (strpos($retVal,"&WAVT_") !== false)  {
    $retVal = substr($retVal,0,strpos($retVal,"&WAVT_"));
  }
  if ($retVal == "" && $page == $field) {
    $retVal = ValidatedField($page,$field."_Errors");
  }
   return htmlspecialchars($retVal);
}
?>