<?php


class DataPager {
	var $id; 	// unique id for pager (defaults to 'adodb')
	//var $db; 	// ADODB connection object
	var $sql; 	// sql used
	var $rs;	// recordset generated
	var $curr_page;	// current page number before Render() called, calculated in constructor
	var $rows;		// number of rows per page
    var $linksPerPage=10; // number of links per page in navigation bar
    var $showPageLinks; 

	//var $gridAttributes = 'width=100% border=1 bgcolor=white';
	
	// Localize text strings here
	var $first = '<code>|&lt;</code>';
	var $prev = '<code>&lt;&lt;</code>';
	var $next = '<code>>></code>';
	var $last = '<code>>|</code>';
	var $moreLinks = '...';
	var $startLinks = '...';
	var $gridHeader = false;
	var $htmlSpecialChars = true;
	var $page = 'Page';
	var $linkSelectedColor = 'red';
	var $cache = 0;  #secs to cache with CachePageExecute()
	
	var $last_page = "";
	var $num_result = "";
	
	function DataPager($sql,$id = 'adodb', $showPageLinks = false)
	{
	global $HTTP_SERVER_VARS,$PHP_SELF,$HTTP_SESSION_VARS,$HTTP_GET_VARS, $AppUI;
	
		$curr_page = $id.'_curr_page';
		if (empty($PHP_SELF)) $PHP_SELF = $HTTP_SERVER_VARS['PHP_SELF'];
		
		$this->sql = $sql;
		$this->id = $id;
		$this->page = $AppUI->_('Page');
		$this->showPageLinks = $showPageLinks;
		$this->rows = $AppUI->getPref("RECORDSxPAGE") ? $AppUI->getPref("RECORDSxPAGE") :  $AppUI->getConfig('records_per_page');
		
		if (!is_numeric($this->rows) || $this->rows <= 0)
			$this->rows = 20;
		$next_page = $id.'_next_page';	
		
		if (isset($HTTP_GET_VARS[$next_page])) {
			$HTTP_SESSION_VARS[$curr_page] = $HTTP_GET_VARS[$next_page];
		}
		if (empty($HTTP_SESSION_VARS[$curr_page])) $HTTP_SESSION_VARS[$curr_page] = 1; ## at first page
		
		$this->curr_page = $HTTP_SESSION_VARS[$curr_page];
	}
	
	function getResults($rows=NULL){
		if (is_int($rows) && $rows > 0)
			$this->rows = $rows;	

		$rows = $this->rows;
		$result = db_loadList($this->sql);
		if (empty($result)) return $result;
		$this->num_result = count($result);
		$this->last_page = ceil( count($result) / $rows) ;
		$this->curr_page = $this->curr_page > $this->last_page ? $this->last_page : $this->curr_page;
		$page_results = array();
		$ini = $rows * ($this->curr_page - 1);
		$fin = $rows * ($this->curr_page);
		$fin = $fin > count($result) ? count($result) : $fin;
		for($i=$ini; $i < $fin; $i++){
			$page_results[] = $result[$i];
		}
		
		return $page_results;
	}	

	//---------------------------
	// Display link to first page
	function Render_First($anchor=true)
	{
	global $PHP_SELF;
		if ($anchor) {
			$query_string = filterQueryString($this->id."_next_page");
	?>
		<a href="<?php echo $PHP_SELF,'?',$query_string,"&",$this->id;?>_next_page=1"><?php echo $this->first;?></a> &nbsp; 
	<?php
		} else {
			print "$this->first &nbsp; ";
		}
	}

	//--------------------------
	// Display link to next page
	function render_next($anchor=true)
	{
	global $PHP_SELF;
	
		if ($anchor) {
			$query_string = filterQueryString($this->id."_next_page");
		?>
		<a href="<?php echo $PHP_SELF,'?',$query_string,"&",$this->id,'_next_page=',$this->curr_page + 1 ?>"><?php echo $this->next;?></a> &nbsp; 
		<?php
		} else {
			print "$this->next &nbsp; ";
		}
	}
	
	//------------------
	// Link to last page
	// 
	// for better performance with large recordsets, you can set
	// $this->db->pageExecuteCountRows = false, which disables
	// last page counting.
	function render_last($anchor=true)
	{
	global $PHP_SELF;
	
		
		if ($anchor) {
			$query_string = filterQueryString($this->id."_next_page");
		?>
			<a href="<?php echo $PHP_SELF,'?',$query_string,"&",$this->id,'_next_page=',$this->last_page ?>"><?php echo $this->last;?></a> &nbsp; 
		<?php
		} else {
			print "$this->last &nbsp; ";
		}
	}
	
	//---------------------------------------------------
	// original code by "Pablo Costa" <pablo@cbsp.com.br> 
        function render_pagelinks()
        {
        global $PHP_SELF;
            $pages        = $this->last_page;
            $linksperpage = $this->linksPerPage ? $this->linksPerPage : $pages;
            for($i=1; $i <= $pages; $i+=$linksperpage)
            {
                if($this->curr_page >= $i)
                {
                    $start = $i;
                }
            }
            $query_string = filterQueryString($this->id."_next_page");
			$numbers = '';
            $end = $start+$linksperpage-1;
			$link = $query_string."&".$this->id . "_next_page";
            if($end > $pages) $end = $pages;
			
			
			if ($this->startLinks && $start > 1) {
				$pos = $start - 1;
				$numbers .= "<a href=$PHP_SELF?$link=$pos>$this->startLinks</a>  ";
            } 
			
			for($i=$start; $i <= $end; $i++) {
                if ($this->curr_page == $i)
                    $numbers .= "<font color=$this->linkSelectedColor><b>$i</b></font>  ";
                else 
                     $numbers .= "<a href=$PHP_SELF?$link=$i>$i</a>  ";
            
            }
			if ($this->moreLinks && $end < $pages) 
				$numbers .= "<a href=$PHP_SELF?$link=$i>$this->moreLinks</a>  ";
            print $numbers . ' &nbsp; ';
        }
	// Link to previous page
	function render_prev($anchor=true)
	{
	global $PHP_SELF;
		if ($anchor) {
			$query_string = filterQueryString($this->id."_next_page");
	?>
		<a href="<?php echo $PHP_SELF,'?',$query_string,"&",$this->id,'_next_page=',$this->curr_page - 1 ?>"><?php echo $this->prev;?></a> &nbsp; 
	<?php 
		} else {
			print "$this->prev &nbsp; ";
		}
	}	
	//-------------------------------------------------------
	// Navigation bar
	//
	// we use output buffering to keep the code easy to read.
	function RenderNav()
	{
		if ($this->last_page == "") return '';
		
		ob_start();
		if ($this->curr_page != 1) {
			$this->Render_First();
			$this->Render_Prev();
		} else {
			$this->Render_First(false);
			$this->Render_Prev(false);
		}
        if ($this->showPageLinks){
            $this->Render_PageLinks();
        }
		if ($this->curr_page != $this->last_page) {
			$this->Render_Next();
			$this->Render_Last();
		} else {
			$this->Render_Next(false);
			$this->Render_Last(false);
		}
		$s = ob_get_contents();
		ob_end_clean();
		return $s;
	}
	
	//-------------------
	// This is the footer
	function RenderPageCount()
	{
		if ($this->last_page == "") return '';
		$lastPage = $this->last_page;
		if ($lastPage == -1) $lastPage = 1; // check for empty rs.
		return "$this->page ".$this->curr_page."/".$lastPage."";
	}
	
}

/**
 * Clase que genera paginacion , permite mandar los datos por POST usando una funcion js que envia el form.
 * 
 */

class DataPager_post {
	var $id; 	// unique id for pager (defaults to 'adodb')
	//var $db; 	// ADODB connection object
	var $sql; 	// sql used
	var $rs;	// recordset generated
	var $curr_page;	// current page number before Render() called, calculated in constructor
	var $rows;		// number of rows per page
    var $linksPerPage=10; // number of links per page in navigation bar
    var $showPageLinks; 

	//var $gridAttributes = 'width=100% border=1 bgcolor=white';
	
	// Localize text strings here
	var $first = '<code>|&lt;</code>';
	var $prev = '<code>&lt;&lt;</code>';
	var $next = '<code>>></code>';
	var $last = '<code>>|</code>';
	var $moreLinks = '...';
	var $startLinks = '...';
	var $gridHeader = false;
	var $htmlSpecialChars = true;
	var $page = 'Page';
	var $linkSelectedColor = 'red';
	var $cache = 0;  #secs to cache with CachePageExecute()
	
	var $last_page = "";
	var $num_result = "";
	
	
	function getResults($rows=NULL){
		if (is_int($rows) && $rows > 0)
			$this->rows = $rows;	

		$rows = $this->rows;
		$result = db_loadList($this->sql);
		if (empty($result)) return $result;
		$this->num_result = count($result);
		$this->last_page = ceil( count($result) / $rows) ;
		$this->curr_page = $this->curr_page > $this->last_page ? $this->last_page : $this->curr_page;
		$page_results = array();
		$ini = $rows * ($this->curr_page - 1);
		$fin = $rows * ($this->curr_page);
		$fin = $fin > count($result) ? count($result) : $fin;
		for($i=$ini; $i < $fin; $i++){
			$page_results[] = $result[$i];
		}
		
		return $page_results;
	}	
	
	//-------------------
	// This is the footer
	function RenderPageCount()
	{
		if ($this->last_page == "") return '';
		$lastPage = $this->last_page;
		if ($lastPage == -1) $lastPage = 1; // check for empty rs.
		return "$this->page ".$this->curr_page."/".$lastPage."";
	}

	
	function DataPager_post($sql,$id = 'adodb', $showPageLinks = false)
	{
	global $HTTP_SERVER_VARS,$PHP_SELF,$HTTP_SESSION_VARS,$HTTP_POST_VARS, $AppUI;
	
		$curr_page = $id.'_curr_page';
		if (empty($PHP_SELF)) $PHP_SELF = $HTTP_SERVER_VARS['PHP_SELF'];
		
		$this->sql = $sql;
		$this->id = $id;
		$this->page = $AppUI->_('Page');
		$this->showPageLinks = $showPageLinks;
		$this->rows = $AppUI->getPref("RECORDSxPAGE") ? $AppUI->getPref("RECORDSxPAGE") :  $AppUI->getConfig('records_per_page');
		
		if (!is_numeric($this->rows) || $this->rows <= 0)
			$this->rows = 20;
		$next_page = $id.'_next_page';	
		
		if (isset($HTTP_POST_VARS[$next_page])) {
			$HTTP_SESSION_VARS[$curr_page] = $HTTP_POST_VARS[$next_page];
		}
		if (empty($HTTP_SESSION_VARS[$curr_page])) $HTTP_SESSION_VARS[$curr_page] = 1; ## at first page
		
		$this->curr_page = $HTTP_SESSION_VARS[$curr_page];
	}
	
	
	/**
	 * Arma los links con los numeros de pagina y las flechas, genera funcion js que enviara el formulario por POST
	 * 
	 * @param  $name_form : Nombre del formulario 
	 *         $extra1, $extra2 : permite el ingreso de variables extras
	 *                            Ej: "changepage.value = '1'"
	 */
	function RenderNav_post($name_form, $extra1=null, $extra2=null )
	{
		if ($this->last_page == "") return '';
		
		ob_start();
		
		$campo = $this->id."_next_page";
		
		$js = "
		<SCRIPT LANGUAGE=\"JavaScript\">
		 
		        function pager(p){
		         f = document.forms['".$name_form."'];
		         f.$campo.value = p;
		         ";
		
		if ($extra1 != null){
			$js .= "f.".$extra1.";";
		}
		
		if ($extra2 != null){
			$js .= "f.".$extra2.";";
		}
		         
	    $js .= "f.submit();
		}
		</script>
		";
	    
	    echo $js;
		
		if ($this->curr_page != 1) {
			$this->Render_First_post();
			$this->Render_Prev_post();
		} else {
			$this->Render_First_post(false);
			$this->Render_Prev_post(false);
		}
        if ($this->showPageLinks){
            $this->Render_PageLinks_post();
        }
		if ($this->curr_page != $this->last_page) {
			$this->Render_Next_post();
			$this->Render_Last_post();
		} else {
			$this->Render_Next_post(false);
			$this->Render_Last_post(false);
		}
		$s = ob_get_contents();
		ob_end_clean();
		return $s;
	}
	
	function Render_First_post($anchor=true)
	{
	global $PHP_SELF;
		if ($anchor) {
			$query_string = filterQueryString($this->id."_next_page");
	?>  
		<a href="javascript: pager('1')"><?php echo $this->first;?></a> &nbsp; 
	<?php
		} else {
			print "$this->first &nbsp; ";
		}
	}


function Render_Prev_post($anchor=true)
	{
	global $PHP_SELF;
		if ($anchor) {
			$query_string = filterQueryString($this->id."_next_page");
			$pag = $this->curr_page - 1;
	?>
		<a href="javascript: pager('<?=$pag?>')"><?php echo $this->prev;?></a> &nbsp; 
	<?php 
		} else {
			print "$this->prev &nbsp; ";
		}
	}	


function render_next_post($anchor=true)
	{
	global $PHP_SELF;
	
		if ($anchor) {
			$query_string = filterQueryString($this->id."_next_page");
			$pag = $this->curr_page + 1;
		?>
		<a href="javascript: pager('<?=$pag?>')"><?php echo $this->next;?></a> &nbsp; 
		<?php
		} else {
			print "$this->next &nbsp; ";
		}
	}
	

function render_last_post($anchor=true)
	{
	global $PHP_SELF;
	
		
		if ($anchor) {
			$query_string = filterQueryString($this->id."_next_page");
		?>
			<a href="javascript: pager('<?=$this->last_page?>')"><?php echo $this->last;?></a> &nbsp; 
		<?php
		} else {
			print "$this->last &nbsp; ";
		}
	}
	

function render_pagelinks_post()
        {
        global $PHP_SELF;
            $pages        = $this->last_page;
            $linksperpage = $this->linksPerPage ? $this->linksPerPage : $pages;
            for($i=1; $i <= $pages; $i+=$linksperpage)
            {
                if($this->curr_page >= $i)
                {
                    $start = $i;
                }
            }
            $query_string = filterQueryString($this->id."_next_page");
			$numbers = '';
            $end = $start+$linksperpage-1;
			$link = $query_string."&".$this->id . "_next_page";
            if($end > $pages) $end = $pages;
			
			
			if ($this->startLinks && $start > 1) {
				$pos = $start - 1;
				
				$numbers .= "<a href=\"javascript: pager('$pos')\">$this->startLinks</a>  ";
            } 
			
			for($i=$start; $i <= $end; $i++) {
                if ($this->curr_page == $i)
                    $numbers .= "<font color=$this->linkSelectedColor><b>$i</b></font>  ";
                else 
                     $numbers .= "<a href=\"javascript: pager('$i')\">$i</a>  ";
            
            }
			if ($this->moreLinks && $end < $pages) 
				$numbers .= "<a href=\"javascript: pager('$i')\">$this->moreLinks</a>  ";
            print $numbers . ' &nbsp; ';
        }
        
        
	
}

?>