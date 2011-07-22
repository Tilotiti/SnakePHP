<?php

class planetvo_csv {
    
    public function export($stock, $export) {
        
        $stats['add'] = 0;
        $stats['maj'] = 0;
        $stats['del'] = 0;
        
        $smarty = new smarty();
        $smarty->template_dir = TEMPLATE.'/flux/';
        $smarty->compile_dir  = CACHE.'/flux/';
        
        $md5 = "NOM_PHOTO   CURRENT_MD5 NOUVELLE\n";
        $autos = array();
        while($stock->next()):
            $auto = new auto($stock);
            $auto->getSite();
            if(is_array($auto->get('photo'))):
                foreach($auto->get('photo') as $photo):
                    if(file_exists(WEBROOT.'/images/auto/'.$photo)):
                        @chmod(WEBROOT.'/file/export/auto/'.$photo, 0777);
                        @chmod(WEBROOT.'/file/export/tmp/'.$photo, 0777);
                        copy(WEBROOT.'/images/auto/'.$photo, WEBROOT.'/file/export/tmp/'.$photo);
                        $md5 .= $photo."    ".md5_file(WEBROOT.'/file/export/tmp/'.$photo)."    N\n";
                    endif;
                endforeach;
            elseif($auto->get('photo')):
                if(file_exists(WEBROOT.'/images/auto/'.$auto->get('photo'))):
                    @chmod(WEBROOT.'/file/export/auto/'.$auto->get('photo'), 0777);
                    @chmod(WEBROOT.'/file/export/tmp/'.$auto->get('photo'), 0777);
                    copy(WEBROOT.'/images/auto/'.$auto->get('photo'), WEBROOT.'/file/export/tmp/'.$auto->get('photo'));
                    $md5 .= $auto->get('photo')."    ".md5_file(WEBROOT.'/file/export/tmp/'.$auto->get('photo'))."    N\n";
                endif;
            endif;
            // Choose MAJ or ADD
            if($auto->get('time') > $export->get('time')):
                $stats['add']++;
            else:
                $stats['maj']++;
            endif;

            array_push($autos, $auto->get());
        endwhile;

        $smarty->assign('autos', $autos);
        $return = $smarty->fetch('planetvo_csv.tpl');

        $fp = fopen(WEBROOT.'/file/export/tmp/stockvo.csv', "w");
        fputs($fp, $return);
        fclose($fp);

        $fp = fopen(WEBROOT.'/file/export/tmp/listing_photo.txt', "w");
        fputs($fp, $md5);
        fclose($fp);

        @chmod(WEBROOT.'/file/export/tmp/stockvo.csv', 0777);
        @chmod(WEBROOT.'/file/export/tmp/listing_photo.txt', 0777);
        
        return $stats;
    }
}

?>
