function isEmailField(email){
	var field = email;
<?php	/* patron para eliminar las descripciones de los mails*/ ?>
	var descpat = /(\x22.*?\x22|\x27.*?\x27)/g;
	field = field.replace(descpat, ""); 
<?php	/* patron para detectar mails entre < > */ ?>	
	var mailpat = /\x3c(.*?)\x3e/g;
	
	var ad = field.toLowerCase().split(",");
	var rta = true;
	for(var i = 0 ; i < ad.length && rta ; i++){
		var address = trim(ad[i]).replace(mailpat, "$1");
		var rta = rta && isEmail(address);
	}
	return rta;
}

function isEmail(email){
	var emailPatterns = [
		<?php /* /.+@.+\..+$/i, */ ?>
		/.+@.+[\..+]*$/i,
		/^\w.+@\w.+\.[a-z]+$/i,
		/^\w[-_a-z~.]+@\w[-_a-z~.]+\.[a-z]{2}[a-z]*$/i,
		/^\w[\w\d]+(\.[\w\d]+)*@\w[\w\d]+(\.[\w\d]+)*\.[a-z]{2,7}$/i
		];
		
	var rta = emailPatterns[0].test(email.toLowerCase());
	//alert(email+" + "+rta);
	return rta;
}

function isURL (url) {
	var urlPattern = /^(?:(?:ftp|https?):\/\/)?(?:[a-z0-9](?:[-a-z0-9]*[a-z0-9])?\.)+(?:com|edu|biz|org|gov|int|info|mil|net|name|musem|coop|aero|[a-z][a-z])\b(?:\d+)?(?:\/[^;"'<>()\[\]{}\s\x7f-\xff]*(?:[.,?]+[^;"'<>()\[\]{}\s\x7f-\xff]+)*)?/;
	return urlPattern.test(url.toLowerCase());
}


function trim(inputString) {
   // Removes leading and trailing spaces from the passed string. Also removes
   // consecutive spaces and replaces it with one space. If something besides
   // a string is passed in (null, custom object, etc.) then return the input.
   if (typeof inputString != "string") { return inputString; }
   var retValue = inputString;
   var ch = retValue.substring(0, 1);
   while (ch == " ") { // Check for spaces at the beginning of the string
	  retValue = retValue.substring(1, retValue.length);
	  ch = retValue.substring(0, 1);
   }
   ch = retValue.substring(retValue.length-1, retValue.length);
   while (ch == " ") { // Check for spaces at the end of the string
	  retValue = retValue.substring(0, retValue.length-1);
	  ch = retValue.substring(retValue.length-1, retValue.length);
   }
   while (retValue.indexOf("  ") != -1) { // Note that there are two spaces in the string - look for multiple spaces within the string
	  retValue = retValue.substring(0, retValue.indexOf("  ")) + retValue.substring(retValue.indexOf("  ")+1, retValue.length); // Again, there are two spaces in each of the strings
   }
   return retValue; // Return the trimmed string back to the user
} // Ends the "trim" function



function findPosX(obj)
{
	var curleft = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft;
}

function findPosY(obj)
{
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
		curtop += obj.y;
	return curtop;
}
