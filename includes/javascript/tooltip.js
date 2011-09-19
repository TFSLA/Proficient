//tooltip

//uso onmouseover="tooltipLink('<pre>Mensaje</pre>', 'Llamar a Claudia');"
//onmouseout="tooltipClose();"

var isIE = document.all ? true : false;
var activeTimeout;

if (!isIE) {
    document.captureEvents(Event.MOUSEMOVE);
    document.onmousemove = mousePos;
    var netX, netY;
}

function posX()
{
    tempX = document.body.scrollLeft + event.clientX;
    if (tempX < 0) {
        tempX = 0;
    }
    return tempX;
}

function posY()
{
    tempY = document.body.scrollTop + event.clientY;
    if (tempY < 0) {
        tempY = 0;
    }
    return tempY;
}

function mousePos(e)
{
    netX = e.pageX;
    netY = e.pageY;
}

function tooltipShow(pX, pY, src)
{
    if (pX < 1) {
        pX = 1;
    }
    if (pY < 1) {
        pY = 1;
    }
    if (isIE) {
        document.all.tooltip.style.visibility = 'visible';
        document.all.tooltip.innerHTML = src;
        document.all.tooltip.style.left = pX + 'px';
        document.all.tooltip.style.top = pY + 'px';
    } else {
        document.getElementById('tooltip').style.visibility = 'visible';
        document.getElementById('tooltip').style.left = pX + 'px';
        document.getElementById('tooltip').style.top = pY + 'px';
        document.getElementById('tooltip').innerHTML = src;
    }
}

function tooltipClose()
{
    if (isIE) {
        document.all.tooltip.innerHTML = '';
        document.all.tooltip.style.visibility = 'hidden';
    } else {
        document.getElementById('tooltip').style.visibility = 'hidden';
        document.getElementById('tooltip').innerHTML = '';
    }
    clearTimeout(activeTimeout);
    window.status = '';
}

function tooltipLink(tooltext, statusline, classtooltip)
{
    text = '<div class="' + (classtooltip ? classtooltip : 'tooltip') + '">' + tooltext + '</div>';
    if (isIE) {
        xpos = posX();
        ypos = posY();
    } else {
        xpos = netX;
        ypos = netY;
    }
//    activeTimeout = setTimeout('tooltipShow(xpos, ypos + 10, text);', 300);
    activeTimeout = setTimeout('tooltipShow(xpos - 80, ypos + 10, text);', 300);
    window.status = statusline;
}

function tooltipLinkXY(tooltext, statusline, classtooltip, x, y)
{
    text = '<div class="' + (classtooltip ? classtooltip : 'tooltip') + '">' + tooltext + '</div>';

	xpos = x;
	ypos = y;
	
    activeTimeout = setTimeout('tooltipShow(xpos - 80, ypos + 10, text);', 300);
    window.status = statusline;
}

document.write('<div id="tooltip" style="position: absolute; visibility: hidden; z-index: 1;"></div>');

function showGenericMessage(message)
{
	if (document.getElementById('divGenericMessage').style.display == 'none')
	{
		document.getElementById('divGenericMessage').style.top = (document.body.scrollTop <= 0 ? '40%' : (document.body.scrollTop + 200));
		document.getElementById('divGenericMessage').innerHTML = htmlgenericMessage.replace('{0}', message);
		document.getElementById('divGenericMessage').style.display = '';
		setTimeout("showGenericMessage()", 3000);
	}
	else
	{
		document.getElementById('divGenericMessage').style.display = 'none';
	}
}

document.write('<div id="divGenericMessage" name="divGenericMessage" style="z-index:1;display:none;position:absolute;padding:0px;width:350px;height:70px;background-color: #E9E9E9; left: 40%; top: 40%; border:1px solid;">');
var htmlgenericMessage = '<br><center><b>{0}</b></center>';
document.write('</div>');