<?php

class cargest extends import  {
    
    public function load() {
        
        $zip = new ZipArchive();
        $zip->open(FILE.'/import/cargest/cargesthgcma.zip');
        $zip->extractTo(FILE.'/import/cargest/');
        $zip->close();
        
        @chmod(FILE.'/import/cargest/cargesthgcma.csv', 0777);

    }
    
    public function save() {
        $return['add']   = 0;
        $return['maj']   = 0;
        $return['del']   = 0;
        $return['err']   = 0;
        $return['total'] = 0;
        
        // On rétablie les VO
        $query = new query();
        $query->Update('auto')
              ->Set('save', 0)
              ->Where('flux', '=', 'cargest')
              ->exec();
        
        // On compte le nombre de VO actuel
        $query = new query();
        $query->Select('id')
              ->From('auto')
              ->Where('flux', '=', 'cargest')
              ->Where('end', '=', 0)
              ->exec();
        
        $return['maj'] = $query->count;
        
        $file = file(FILE.'/import/cargest/cargesthgcma.csv');
        $file = array_splice($file, 1, count($file)-1);
        
        foreach($file as $line):

            $array = explode('";"', utf8_encode($line), 29);
        
            if($array[13] == "0"):
                $return['err']++;
                continue;
            endif;

            // Enregistrement de l'auto
            $auto = new auto();
            $auto->set('id',       'cargest_hgcma_'.str_replace('"', "", $array[0]));
            $auto->set('ref',      str_replace('"', "", $array[0]));
            $auto->set('marque',   $array[1]);
            $auto->set('modele',   $array[2]);
            $auto->set('version',  str_replace(strtolower($array[2]), "", strtolower($array[3])));
            $auto->set('annee',    $array[7]);
            $auto->set('km',       $array[8]);
            $auto->set('energie',  lang::flux('energie:'.slug($array[5])));
            $auto->set('place',    "");
            $auto->set('porte',    $array[11]);
            $auto->set('couleur',  $array[9]);
            $auto->set('vitesse',  lang::flux('vitesse:'.slug($array[6])));
            $auto->set('rapport',  0);
            $auto->set('prix',     $array[13]);
            $auto->set('garantie', $array[14]);
            $auto->set('option',   explode(',', $array[12]));
            $auto->set('cat',      lang::flux('cat:'.slug($array[4])));
            $auto->set('pf',       $array[15]);
            $auto->set('pr',       $array[16]);
            $auto->set('flux',     'cargest');
            $auto->set('save',     1);
            $auto->set('site',     'cargest_hgcma');
            
            // Photos
            $photos = array();
            if(!empty($array[28])):
                $i = 0;
                foreach(explode(';', $array[28]) as $photo):
                    $i++;
                    $get = parent::getPhoto(FILE.'/import/cargest/'.str_replace('"', '', $photo), WEBROOT.'/images/auto/', 'cargest_'.str_replace('"', "", $array[0]).'_'.$i);
                    if($get):
                        $photos[] = $get;
                    endif;
                endforeach;
                $auto->set('photo', $photos);
            endif;
            $auto->save();
            
            $return['total'] ++;
        endforeach;
        
        // On compte les VO non mis à jour
        $query->Select('id')
              ->From('auto')
              ->Where('flux', '=', 'cargest')
              ->Where('end', '=', 0)
              ->Where('save', '=', 0)
              ->exec();
        
        // On calcul les statistiques
        $return['maj'] = $return['maj'] - $query->count;
        $return['del'] += $query->count;
        $return['add'] = $return['del'] + $return['total'] - $return['maj'];
        
        // On supprime les VO manquants
        $query = new query();
        $query->Update('auto')
              ->Set('end', time())
              ->Where('flux', '=', 'cargest')
              ->Where('end', '=', 0)
              ->Where('save', '=', 0)
              ->exec();
        
        // On met à jour l'import
        $update = new query();
        $update->Update('import')
               ->Set('time', time())
               ->Where('code', '=', 'cargest')
               ->exec();
        
        // On met à jour la concession
        $update = new query();
        $update->Update('site')
               ->Set('time', time())
               ->Where('code', '=', 'hgcma')
               ->exec();
        
        return $return;
    }
}
?>
