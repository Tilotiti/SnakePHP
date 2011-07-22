<?php
function icone($src = false, $class = false, $alt = false, $id = false, $onclick = false) {
    if(!$src):
        return false;
    endif;

    if(!$alt):
        $params['alt'] = ' alt="icone"';
    else:
        $params['alt'] = ' alt="'.$alt.'"';
    endif;

    if(!$class):
        $params['class'] = 'icone';
    else:
        $params['class'] = 'icone '.$class;
    endif;

    if(!$id):
        $params['id'] = '';
    else:
        $params['id'] = ' id="'.$id.'"';
    endif;

    if(!$onclick):
        $params['onclick'] = '';
    else:
        $params['onclick'] = ' onclick="'.$onclick.'"';
    endif;

    return '<img src="/images/icones/'.$src.'.png" '.$params['alt'].$params['id'].$params['onclick'].' class="'.$params['class'].'" />';
}

?>
