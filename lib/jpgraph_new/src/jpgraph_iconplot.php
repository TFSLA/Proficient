<?php
//=======================================================================
// File:	JPGRAPH_ICONPLOT.PHP
// Description:	PHP4 Graph Plotting library. Extension module.
// Created: 	2004-02-18
// Author:	Johan Persson (johanp@aditus.nu)
// Ver:		$Id: jpgraph_iconplot.php,v 1.1 2009-05-19 21:15:40 pkerestezachi Exp $
//
// License:	This code is released under QPL 1.0
// Copyright (C) 2001,2002,2003,2004 Johan Persson
//========================================================================


//===================================================
// CLASS IconPlot
// Description: Make it possible to add a (small) image
// to the graph
//===================================================
class IconPlot {
    var $iHorAnchor='left',$iVertAnchor='top';
    var $iX=0,$iY=0;
    var $iFile='';
    var $iScale=1.0,$iMix=100;
    var $iAnchors = array('left','right','top','bottom','center');
    var $iCountryFlag='',$iCountryStdSize=3;

    function IconPlot($aFile="",$aX=0,$aY=0,$aScale=1.0,$aMix=100) {
	$this->iFile = $aFile;
	$this->iX=$aX;
	$this->iY=$aY;
	$this->iScale= $aScale;
	if( $aMix < 0 || $aMix > 100 ) {
	    JpGraphError::Raise('Mix value for icon must be between 0 and 100.');
	}
	$this->iMix = $aMix ;
    }

    function SetCountryFlag($aFlag,$aX=0,$aY=0,$aScale=1.0,$aMix=100,$aStdSize=3) {
	$this->iCountryFlag = $aFlag;
	$this->iX=$aX;
	$this->iY=$aY;
	$this->iScale= $aScale;
	if( $aMix < 0 || $aMix > 100 ) {
	    JpGraphError::Raise('Mix value for icon must be between 0 and 100.');
	}
	$this->iMix = $aMix;
	$this->iCountryStdSize = $aStdSize;
    }

    function SetPos($aX,$aY) {
	$this->iX=$aX;
	$this->iY=$aY;
    }

    function SetScale($aScale) {
	$this->iScale = $aScale;
    }

    function SetMix($aMix) {
	if( $aMix < 0 || $aMix > 100 ) {
	    JpGraphError::Raise('Mix value for icon must be between 0 and 100.');
	}
	$this->iMix = $aMix ;
    }

    function SetAnchor($aXAnchor='left',$aYAnchor='center') {
	if( !in_array($aXAnchor,$this->iAnchors) ||
	    !in_array($aYAnchor,$this->iAnchors) ) {
	    JpGraphError::Raise("Anchor position for icons must be one of 'top', 'bottom', 'left', 'right' or 'center'");
	}
	$this->iHorAnchor=$aXAnchor;
	$this->iVertAnchor=$aYAnchor;
    }
    
    function PreStrokeAdjust($aGraph) {
	// Nothing to do ...
    }

    function DoLegend($aGraph) {
	// Nothing to do ...
    }

    function Max() {
	return array(false,false);
    }


    // The next four function are framework function tht gets called
    // from Gantt and is not menaiungfull in the context of Icons but
    // they must be implemented to avoid errors.
    function GetMaxDate() { return false;   }
    function GetMinDate() { return false;   }
    function GetLineNbr() { return 0;   }
    function GetAbsHeight() {return 0;  }


    function Min() {
	return array(false,false);
    }

    function StrokeMargin(&$aImg) {
	return true;
    }

    function Stroke($aImg) {
	if( $this->iFile != '' && $this->iCountryFlag != '' ) {
	    JpGraphError::Raise('It is not possible to specify both an image file and a country flag for the same icon.');	
	}
	if( $this->iFile != '' ) {
	    $gdimg = Graph::LoadBkgImage('',$this->iFile);
	}
	else {
	    if( ! class_exists('FlagImages') ) {
		JpGraphError::Raise('In order to use Country flags as icons you must include the "jpgraph_flags.php" file.');
	    }
	    $fobj = new FlagImages($this->iCountryStdSize);
	    $dummy='';
	    $gdimg = $fobj->GetImgByName($this->iCountryFlag,$dummy);
	}
	if( $this->iX >= 0  && $this->iX <= 1.0 ) {
	    $w = imagesx($aImg->img);
	    $this->iX = round($w*$this->iX);
	}
	if( $this->iY >= 0  && $this->iY <= 1.0 ) {
	    $h = imagesy($aImg->img);
	    $this->iY = round($h*$this->iY);
	}
	$iconw = imagesx($gdimg);
	$iconh = imagesy($gdimg);
	
	if( $this->iHorAnchor == 'center' ) 
	    $this->iX -= round($iconw*$this->iScale/2);
	if( $this->iHorAnchor == 'right' ) 
	    $this->iX -= round($iconw*$this->iScale);
	if( $this->iVertAnchor == 'center' ) 
	    $this->iY -= round($iconh*$this->iScale/2);
	if( $this->iVertAnchor == 'bottom' ) 
	    $this->iY -= round($iconh*$this->iScale);

	$aImg->CopyMerge($gdimg,$this->iX,$this->iY,0,0,
			 round($iconw*$this->iScale),round($iconh*$this->iScale),
			 $iconw,$iconh,
			 $this->iMix);
    }
}

?>
