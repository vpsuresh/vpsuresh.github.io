function WAAddError(formElement,errorMsg,focusIt,stopIt)  {
  if (document.WAFV_Error)  {
	  document.WAFV_Error += "\n" + errorMsg;
  }
  else  {
    document.WAFV_Error = errorMsg;
  }
  if (!document.WAFV_InvalidArray)  {
    document.WAFV_InvalidArray = new Array();
  }
  document.WAFV_InvalidArray[document.WAFV_InvalidArray.length] = formElement;
  if (focusIt && !document.WAFV_Focus)  {
	document.WAFV_Focus = focusIt;
  }

  if (stopIt == 1)  {
	document.WAFV_Stop = true;
  }
  else if (stopIt == 2)  {
	formElement.WAFV_Continue = true;
  }
  else if (stopIt == 3)  {
	formElement.WAFV_Stop = true;
	formElement.WAFV_Continue = false;
  }
}

function WAValidatePN(formElement,errorMsg,areaCode,international,reformat,focusIt,stopIt,required)  {
  var value = formElement.value;
  var isValid = true;
  var allowed = "*() -./_\n\r+";
  var newVal = "";
  if ((!document.WAFV_Stop && !formElement.WAFV_Stop) && !(!required && value==""))  {
    for (var x=0; x<value.length; x++)  {
      var z = value.charAt(x);
      if ((z >= "0") && (z <= "9")) {
	    newVal += z;
	  }
	  else  {
		if (allowed.indexOf(z) < 0)  {
		  isValid = false;
		}
	  }
    }	
	if (international)  {
	  if  (newVal.length < 5)  {
	    isValid = false;
	  }
	}
	else if (newVal.length == 11)  {
	  if (newVal.charAt(0) != "1")	{
		isValid = false;
	  }
	}
	else if ((newVal.length != 10 && newVal.length != 7) || (newVal.length==7 && areaCode)) {
	  isValid = false;
	}
  }
  if (!isValid)  {
    WAAddError(formElement,errorMsg,focusIt,stopIt);
  }
  else  {
    formElement.WAValid = true;
    if (reformat != "" && newVal != "")  {
      for (var x=0; x<newVal.length; x++)  {
	    reformat = reformat.substring(0,reformat.lastIndexOf("x")) + newVal.charAt(newVal.length-(x+1)) + reformat.substring(reformat.lastIndexOf("x")+1);
	  }
	  if (reformat.indexOf("x")>=0)  {
	    reformat = reformat.substring(reformat.lastIndexOf("x")+1);
        z = reformat.charAt(0);
	    while (((z < "0") || (z > "9")) && z != "(")  {
	      reformat = reformat.substring(1);
		  z = reformat.charAt(0);
		}
	  }
      formElement.value = reformat;
	}
  }
}

function WAGetDateFormat(value, dateFormat) {
  var isUSServ = (new Date("1/2/2006").getMonth() == 0);
  var tValue = value;
  var isEuroDate = ((dateFormat && String(dateFormat).indexOf("[12]\\d|3[0-1]") < String(dateFormat).indexOf("1[0-2]|") && String(dateFormat).indexOf("\\w*") < 0 && (String(dateFormat).indexOf("\\d{4}") < 0 || (String(dateFormat).indexOf("\\d{4}") >= 0 && String(dateFormat).indexOf("\\d{4}") > String(dateFormat).indexOf("[12]\\d|3[0-1]")))) || (!isUSServ));
  if ((isEuroDate && isUSServ) || (!isEuroDate && !isUSServ)) {
    var datePattn = /(\d*)[-\.\/](\d*)[-\.\/](\d*)/;
    var tMatch = tValue.match(datePattn);
    if (tMatch && String(tMatch[1]).length != 4) {
      if (isEuroDate) {
        value = tMatch[2] + "/" + tMatch[1] + "/" + tMatch[3];
      }
      else {
        value = tMatch[1] + "/" + tMatch[2] + "/" + tMatch[3];
      }
      if (tValue.indexOf(" ") > 0) {
        value += tValue.substring(tValue.indexOf(" "));
      }
    }
  }
  return new Date(value.replace(/[-\.]/g,"/"));
}

function WADateFormat(format,dateVar)  { 
  var fullYear = dateVar.getYear();
  if (fullYear <= 10) fullYear += 2000;
  if (fullYear <= 200) fullYear += 1900;
    dateVar.setYear(fullYear);
  var newDate = format;
  var ampm = "A";
  var ampmReplace = "p";
  var month = dateVar.getMonth() +1;
  var monthName = "January";
  if (month == 2) monthName="February";
  if (month == 3) monthName="March";
  if (month == 4) monthName="April";
  if (month == 5) monthName="May";
  if (month == 6) monthName="June";
  if (month == 7) monthName="July";
  if (month == 8) monthName="August";
  if (month == 9) monthName="September";
  if (month == 10) monthName="October";
  if (month == 11) monthName="November";
  if (month == 12) monthName="December";
  var monthNameReplace = "Month";
  var monthReplace = "m";
  var day = dateVar.getDate();
  var dayReplace = "d";
  var year = dateVar.getYear();
  if (String(year).length > 2)
    year = String(year).substring(year.length-2,year.length);
  var yearReplace = "yy";
  var hour = dateVar.getHours();
  var hourReplace = "h";
  var minute = dateVar.getMinutes();
  if (String(minute).length == 1)
    minute = "0" + minute;
  var minuteReplace = "nn";
  var second = dateVar.getSeconds();
  if (String(second).length == 1)
    second = "0" + second;
  var secondReplace = "ss";
  var timeFormat = "";
  if (format.indexOf(":")>=0)  {
    timeFormat = format.substring(format.indexOf(":"));
	newDate = format.substring(0,format.indexOf(":"));
	timeFormat = newDate.substring(newDate.lastIndexOf(" "))+ timeFormat;
	newDate = newDate.substring(0,newDate.lastIndexOf(" "));
  }
  if (timeFormat.indexOf("h:n")>=0)  {
    if (timeFormat.indexOf("p")>=0)  {
	  if (hour >= 12)  {
	    ampm = "P"
	    if (hour>12)
		  hour = hour -12;
	  }
	  if (timeFormat.indexOf("pm")>=0)  {
	    ampm += "M"
		ampmReplace = "pm"
	  }
	}
	if (timeFormat.indexOf("hh")>=0)  {
	  if (String(hour).length == 1)
        hour = "0" + hour;
	  hourReplace = "hh";
	}
    timeFormat = timeFormat.replace(hourReplace,hour).replace(minuteReplace,minute).replace(secondReplace,second).replace(ampmReplace,ampm);
  }
  if (newDate.indexOf("yy")>=0)  {
	if (newDate.indexOf("yyyy")>=0)  {
	  year = fullYear;
	  yearReplace = "yyyy";
	}
	if (newDate.indexOf("mm")>=0)  {
	  if (String(month).length == 1)
        month = "0" + month;
	  monthReplace = "mm";
	}
	if (newDate.indexOf("dd")>=0)  {
	  if (String(day).length == 1)
        day = "0" + day;
	  dayReplace = "dd";
	}
	newDate = newDate.replace(yearReplace,year).replace(monthReplace,month).replace(dayReplace,day).replace(monthNameReplace,monthName);
  }
  return newDate + timeFormat;
}

function WAValidateTheTime(doTime, timeFormat, value, isValid, timeMin, timeMax) {
	if (doTime)  {
      if (timeFormat)  {
	    if (value.search(timeFormat)<0)  {
		  isValid = false;
		}
	  }
	  if (value.indexOf(":")<0)  {
	    isValid = false;
	  }
	  if (isValid)  {
	    var dateVar = new Date(value.replace(/-/g,"/"));
		var fullYear = dateVar.getYear();
		if (isNaN(dateVar.valueOf()) || (dateVar.valueOf() == 0))
          dateVar = new Date("1/1/1 "+value);
        if (isNaN(dateVar.valueOf()) || (dateVar.valueOf() == 0))
          isValid = false;
        if (timeMin != "")  {
	      var Today = new Date(timeMin);
          if (isNaN(Today.valueOf()) || Today.valueOf() == 0)  {
		    Today =  new Date("1/1/1 "+timeMin);
		  }
		  if (isNaN(Today.valueOf()) || Today.valueOf() == 0)  {
		    Today = eval(timeMin);
		  }
	      if (dateVar < Today)
	        isValid = false;
        }
        if (timeMax != "")  {
	      var Today = new Date(timeMax);
		  if (isNaN(Today.valueOf()) || Today.valueOf() == 0)  {
		    Today = new Date("1/1/1 "+timeMax);
		  }
          if (isNaN(Today.valueOf()) || Today.valueOf() == 0)  {
		    Today = eval(timeMax);
		  }
	      if (dateVar > Today)
	        isValid = false;
        }
	  }
	}
  return isValid;
}

function WAValidateDT(formElement,errorMsg,doDate,dateFormat,dateReformat,dateMin,dateMax,doTime,timeFormat,timeReformat,timeMin,timeMax,focusIt,stopIt,required)  {
  var isValid = true;
  var value = formElement.value;
  var Now = new Date();
  var Today = Now;
  Today.setHours(0);
  Today.setMinutes(0);
  Today.setSeconds(0);
  if ((!document.WAFV_Stop && !formElement.WAFV_Stop) && !(!required && value==""))  {
    if (doDate)  {
      if (dateFormat)  {
	    if (value.search(dateFormat)<0)  {
		  isValid = false;
		}
	  }
	  if (isValid)  {
      var dateVar = WAGetDateFormat(value, dateFormat);
		if (isNaN(dateVar.valueOf()) || (dateVar.valueOf() == 0))
          isValid = false;
        if (dateMin != "")  {
	      var compareDay = WAGetDateFormat(dateMin, dateFormat);
		  if (isNaN(compareDay.valueOf()) || compareDay.valueOf() == 0)  {
		    compareDay = eval(dateMin);
		  }
	      if (dateVar < compareDay)
	        isValid = false;
        }
        if (dateMax != "")  {
	      var compareDay = WAGetDateFormat(dateMax, dateFormat);
		  if (isNaN(compareDay.valueOf()) || compareDay.valueOf() == 0)  {
		    compareDay = eval(dateMax);
		  }
	      if (dateVar > compareDay)
	      isValid = false;
        }
	  }
	}
  if (doTime) {
    isValid = WAValidateTheTime(doTime, timeFormat, value, isValid, timeMin, timeMax);
  }
    if (!isValid)  {
      WAAddError(formElement,errorMsg,focusIt,stopIt);
    }
    else  {
      var newVal = "";
      if (doDate)  {
	    if (dateReformat!="")  {
	      var newVal = dateReformat;
        }
	    else  {
	      newVal = value;
		  if (newVal.search(/\s*\d*:/)>0)
		    newVal = newVal.substring(0,newVal.search(/\s*\d*:/));
	    }
        if (doTime && timeReformat == "" && value.search(/\s*\d*:/)>0) newVal += value.substring(value.search(/\s*\d*:/));
	  }
	  else  {
		dateVar = new Date("1/1/1 " +value);
	  }
      if (doTime)  {
	    if (newVal != "")
	      newVal += " ";
	    if (timeReformat!="")  {
	      newVal += timeReformat;
        }
	    else if (!doDate) {
	      newVal = value;
	    }
	  }
	  formElement.value = WADateFormat(newVal,dateVar);
    }
  }
}
