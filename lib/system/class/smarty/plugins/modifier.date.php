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
    
    setlocale(LC_TIME, 'fr_FR.UTF8', 'fr.UTF8', 'fr_FR.UTF-8', 'fr.UTF-8');
    
    $mois = strftime('%B', $timestamp);
    $jour = strftime('%A', $timestamp);

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
