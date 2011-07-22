<?php
function smarty_function_crop($params, &$smarty) {
    $params['img'] = str_replace(" ", "-", $params['img']);
    $info = @getimagesize($params['img']);

    if(!$info):
        $info = getimagesize(config('empty'));
        $params['alt'] = $params['img'];
        $params['img'] = config('empty');
    endif;

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
    $x_orig      = $info[0];
    $y_orig      = $info[1];
    $x_crop      = $params['width'];
    $y_crop      = $params['height'];
    $margin_x    = 0;
    $margin_y    = 0;
    $ratio_orig  = $y_orig/$x_orig;
    $ratio_crop  = $y_crop/$x_crop;
    $ratio_x     = $x_crop/$x_orig;
    $ratio_y     = $y_crop/$y_orig;

    // On calcul les dimmension en se basant sur la hauteur fixe
    $y_final  = $y_crop;
    $x_final  = $x_orig * $ratio_y;
    $margin_x = -($x_final - $x_crop)/2;

    if($x_final < $x_crop): // Si ça ne colle pas
        $y_final  = $y_orig * $ratio_x;
        $x_final  = $x_crop;
        $margin_y = -($y_final - $y_crop)/2;
        $margin_x = 0;
    endif;

    return '<span class="cropbox" style="width:'.$x_crop.'px; height:'.$y_crop.'px; '.$style.'">
                <img src="'.$img.'" alt="'.$alt.'" width="'.$x_final.'" height="'.$y_final.'" style="margin-left:'.$margin_x.'px; margin-top:'.$margin_y.'px;" />
            </span>';

}

?>
