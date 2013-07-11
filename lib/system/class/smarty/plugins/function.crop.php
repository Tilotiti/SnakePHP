<?php
function smarty_function_crop($params, &$smarty) {
    $style = '';

    // Définition des styles de la cropbox
    if(isset($params['align'])):
        $style .= "float: ".$params['align'].";";
    endif;

    if(isset($params['border'])):
        $style .= "border: ".$params['border'].";";
    endif;

    if(isset($params['margin'])):
        $style .= "margin: ".$params['margin'].";";
    endif;
    
    $img         = $params['img'];
    $alt         = $params['alt'];
    $x           = $params['width'];
    $y           = $params['height'];
    
    return '<span class="cropbox" style="width:'.$x.'px; height:'.$y.'px; '.$style.'">
                <img src="'.$img.'" alt="'.$alt.'" width="'.$x.'" />
            </span>';

}
?>