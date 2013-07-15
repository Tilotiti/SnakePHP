<?php
/**
 * Smarty date_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     date<br>
 * Purpose:  Retourne une date<br>
 * Input:<br>
 *         - timestamp: timestamp de la date
 *         - type: format de la date
 * @link http://www.tiloweb.com
 * @author   Tilotiti <contact at tiloweb dot com>
 * @param string
 * @return string|void
 */
function smarty_modifier_date($timestamp, $type = false) {
    
    $mois = lang::text("date/month:".date("m", $timestamp));
    $jour = lang::text("date/day:".date("D", $timestamp));

    switch($type):
        case 'long':
            $string = $jour.' '.date("j", $timestamp).' '.$mois.' '.date("Y", $timestamp).' - '.date("G", $timestamp).'h'.date("i", $timestamp);
        break;
        case 'short':
            $string = date("j", $timestamp).'/'.date("m", $timestamp).'/'.date("Y", $timestamp);
        break;
        case 'medium':
            $string = $jour.' '.date("j", $timestamp).' '.$mois.' '.date("Y", $timestamp);
        break;
        default:
            $string = date("j", $timestamp).'/'.date("m", $timestamp).'/'.date("Y", $timestamp).' - '.date("G", $timestamp).'h'.date("i", $timestamp);
        break;
    endswitch;
	
    return $string; 
}


?>
