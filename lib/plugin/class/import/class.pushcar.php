<?php

class pushcar extends import  {
    
    public function load() {
        return true;
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
              ->Where('flux', '=', 'pushcar')
              ->Where('end', '=', 0)
              ->exec();
        
        // On compte le nombre de VO actuel
        $query = new query();
        $query->Select('id')
              ->From('auto')
              ->Where('flux', '=', 'pushcar')
              ->Where('end', '=', 0)
              ->exec();
        
        $return['maj'] = $query->count;
        
        // Implantation de toutes les class  Flux
        $site = array();
        $dir = opendir(FILE.'/import/pushcar');
        while ($file = readdir($dir)):
            if(preg_match('#([\w]+).csv$#', $file)):
                $ligne = file(FILE.'/import/pushcar/'.$file);
                foreach($ligne as $line):
                    
                    // PushCar
                    // CSV
                    // 0  - ID_VO
                    // 1  - Marque
                    // 2  - Modele
                    // 3  - Version
                    // 4  - Immatriculation
                    // 5  - transmission
                    // 6  - Carrosserie
                    // 7  - Annee
                    // 8  - Mise en circulation
                    // 9  - Carburant
                    // 10 - Kms
                    // 11 - Couleur Ext
                    // 12 - Nb Portes
                    // 13 - PuisFisc
                    // 14 - PuisDin
                    // 15 - ConsoUrb
                    // 16 - ConsoExtra
                    // 17 - ConsoMixte
                    // 18 - Pollution
                    // 19 - Equipements : Reprends les équipements venant séparés par des « , »
                    // 20 - Garanties
                    // 21 - PxVenteTTC
                    // 22 - Site : Description du site de stockage du véhicule
                    // 23 - Adresse : Adresse du site de stockage du véhicule
                    // 24 - telephone
                    // 25 - Mail
                    // 26 - Amarchand :
                    // 27 - Commentaire
                    // 28 - Photo1 : URL des photos sur notre serveur
                    // 29 - Photo2 : URL des photos sur notre serveur
                    // 30 - Photo3 : URL des photos sur notre serveur
                    // 31 - Photo4 : URL des photos sur notre serveur
                    // 32 - Photo5 : URL des photos sur notre serveur
                    // 33 - Photo6 : URL des photos sur notre serveur
                    // 34 - PxMarchand : prix marchand du véhicule
                    
                    $array = explode('|', $line);
                    
                    if(empty($array[21])):
                        $return['err']++;
                        continue;
                    endif;
                    
                    $auto = new auto();
                    $auto->set('id',       'pushcar_'.str_replace('.csv', '', $file).'_'.$array[0]);
                    $auto->set('ref',      $array[0]);
                    $auto->set('type',     "VP");
                    $auto->set('marque',   $array[1]);
                    $auto->set('mec',      $array[8]);
                    $auto->set('modele',   $array[2]);
                    $auto->set('version',  $array[3]);
                    $auto->set('annee',    $array[7]);
                    $auto->set('km',       $array[10]);
                    $auto->set('energie',  lang::flux('energie:'.slug($array[9])));
                    $auto->set('place',    0);
                    $auto->set('porte',    $array[12]);
                    $auto->set('couleur',  $array[11]);
                    $auto->set('vitesse',  lang::flux('vitesse:'.slug($array[5])));
                    $auto->set('rapport',  lang::flux('rapport:'.slug($array[5])));
                    $auto->set('prix',     $array[21]);
                    $auto->set('garantie', $array[20]);
                    $auto->set('cat',      lang::flux('cat:'.slug($array[6])));
                    $auto->set('pf',       $array[13]);
                    $auto->set('pr',       $array[14]);
                    $auto->set('option',   str_replace(',', '|', $array[19]));
                    $auto->set('flux',     'pushcar');
                    $auto->set('save',     1);
                    $auto->set('site',     'pushcar_'.str_replace('.csv', '', $file));
                    
                    // Photos
                    $photos = array();
                    $image = array(28, 29, 30, 31, 32, 33);
                    foreach($image as $i):
                        if(!empty($array[$i])):
                            $get = parent::getPhoto($array[$i], WEBROOT.'/images/auto/', 'pushcar_'.str_replace('.csv', '', $file).'_'.$i);
                            if($get):
                                $photos[] = $get;
                            endif;
                        endif;
                    endforeach;
                    $auto->set('photo', $photos);

                    $auto->save();
                    
                    // Enregistrement de la concession
                    if(!is_object($site[str_replace('.csv', '', $file)])):
                        $site[str_replace('.csv', '', $file)] = new site(str_replace('.csv', '', $file), 'pushcar');
                        $site[str_replace('.csv', '', $file)]->set('title',     $array[22]);
                        $site[str_replace('.csv', '', $file)]->set('mail',      $array[25]);
                        $site[str_replace('.csv', '', $file)]->set('adresse',   $array[23]);
                        $site[str_replace('.csv', '', $file)]->set('phone',     $array[24]);
                    endif;
                    $return['total'] ++;
                endforeach;
            endif;
        endwhile;
        closedir($dir);
        
        foreach($site as $concession):
            $concession->save();
        endforeach;
        
        // On compte les VO non mis à jour
        $query->Select('id')
              ->From('auto')
              ->Where('flux', '=', 'pushcar')
              ->Where('end', '=', 0)
              ->Where('save', '=', 0)
              ->exec();
        
        // On calcul les statistiques
        $return['maj'] = $return['maj'] - $query->count;
        $return['del'] = $query->count;
        $return['add'] = $return['del'] + $return['total'] - $return['maj'];
        
        // On supprime les VO manquants
        $query->Update('auto')
              ->Set('end', time())
              ->Where('flux', '=', 'pushcar')
              ->Where('end', '=', 0)
              ->Where('save', '=', 0)
              ->exec();
        
        // On met à jour l'import
        $update = new query();
        $update->Update('import')
               ->Set('time', time())
               ->Where("code", "=", "pushcar")
               ->exec();
        
        return $return;
    }
}
?>