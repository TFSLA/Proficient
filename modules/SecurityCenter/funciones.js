<script language="javascript">
function popUp2(URL) 
{
	day = new Date();
	id = day.getTime();
	eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0 ,scrollbars=1, location=0, statusbar=0, menubar=0, resizable=1, width=910, height=600');");
}

function deshabilitar_companies()
{
	var allcompanies = document.getElementById('allcompanies');
	var companies_r = document.getElementById('companies_r');
	var companies_rw = document.getElementById('companies_rw');
	
	allcompanies.disabled=true;
	companies_r.disabled=true;
	companies_rw.disabled=true;
}

function habilitar_companies()
{
	var allcompanies = document.getElementById('allcompanies');
	var companies_r = document.getElementById('companies_r');
	var companies_rw = document.getElementById('companies_rw');
	
	allcompanies.disabled=false;
	companies_r.disabled=false;
	companies_rw.disabled=false;
}

function deshabilitar_calendar()
{
	var allcalendar = document.getElementById('allcalendar');
	var calendar_r = document.getElementById('calendar_r');
	var calendar_rw = document.getElementById('calendar_rw');
	
	allcalendar.disabled=true;
	calendar_r.disabled=true;
	calendar_rw.disabled=true;
}

function habilitar_calendar()
{
	var allcalendar = document.getElementById('allcalendar');
	var calendar_r = document.getElementById('calendar_r');
	var calendar_rw = document.getElementById('calendar_rw');
	
	allcalendar.disabled=false;
	calendar_r.disabled=false;
	calendar_rw.disabled=false;
}

function submitFrm() 
{
	var allcompanies = document.getElementById('allcompanies');
	var companies_r = document.getElementById('companies_r');
	var companies_rw = document.getElementById('companies_rw');
	var companies_r_txt = document.getElementById('companies_r_txt');
	var companies_rw_txt = document.getElementById('companies_rw_txt');
	
	var allcalendar = document.getElementById('allcalendar');
	var calendar_r = document.getElementById('calendar_r');
	var calendar_rw = document.getElementById('calendar_rw');
	var calendar_r_txt = document.getElementById('calendar_r_txt');
	var calendar_rw_txt = document.getElementById('calendar_rw_txt');
	
	var fl = companies_r.length -1;
	var f2 = companies_rw.length -1;
	var msg = '';

	companies_r_txt.value = "";
	for (fl; fl > -1; fl--){
		companies_r_txt.value = companies_r_txt.value +","+ companies_r.options[fl].value
	}
	
	companies_rw_txt.value = "";
	for (f2; f2 > -1; f2--){
		companies_rw_txt.value = companies_rw_txt.value +","+ companies_rw.options[f2].value
	}

	if (msg.length < 1) {
		document.frm.submit();
	} else {
		alert(msg);
	}
}

function cambiarDeSelect(origen1, destino) {
	var fl = origen1.length -1;
	var au = destino.length -1;
	var users = "x";

	//Pull selected resources and add them to list
	for (fl; fl > -1; fl--) {
		if (origen1.options[fl].selected) {
			t = destino.length;
			opt = new Option( origen1.options[fl].text, origen1.options[fl].value );
			destino.options[t] = opt;
			origen1.options[fl] = null;
		}
	}
	
	//ordeno el select de destino, poniendo la opcion ALL primera
	sortSelect(destino, '<?php echo $AppUI->_('All Companies'); ?>' );
}

/* Ordena un select alfabeticamente que se pase como parametro, y si se pasa el
 * parametro primero, pondra siempre ese elemento primero, esta pensado x ejemplo 
 * para poner la opcion ALL que este siempre primera.
 */
function sortSelect(obj,primero){
    var o = new Array();
    
    for (var i=0; i<obj.options.length; i++){
        o[o.length] = new Option(obj.options[i].text, obj.options[i].value, obj.options[i].defaultSelected, obj.options[i].selected);
    }
    
    o = o.sort(
        function(a,b){
        	 if (a.text == primero) {return -1}
        	 if (b.text == primero) {return 1}
        	
            if ((a.text.toLowerCase()+"") < (b.text.toLowerCase()+"")) { return -1; }
            if ((a.text.toLowerCase()+"") > (b.text.toLowerCase()+"")) {  return 1; }
            return 0;
        } 
    ); 
    
    for (var i=0; i<o.length; i++){
        obj.options[i] = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
    }
}
</script>