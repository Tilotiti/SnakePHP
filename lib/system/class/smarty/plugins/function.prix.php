<?php
function smarty_function_prix($params, &$smarty) {
    // start
    // remise
    // type
    
    if($params['type'] == "%"):
        $total = floor($params['start'] - (($params['start']/100) * $params['remise']));
    else:
        $total = $params['start'] - $params['remise'];
    endif;
    
    $cent  = substr($total, -3);
    $mille = substr($total, 0, (strlen($total)-3));
    
    $cent  = capsule($cent,  $params['type']);
    $mille = capsule($mille, $params['type']);
    
    return '<div class="autoPrix">€'.$mille.'.'.$cent.',-</div>';
}

function capsule($string, $type) {
    $tab = str_split($string, 1);
    $return = '';
    
    foreach($tab as $c):
        $return .= '<span class="capsule_'.$type.'">'.$c.'</span>';
    endforeach;

    return $return;
}
?>