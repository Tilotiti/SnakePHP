<?php

class v12 extends import  {
    
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
              ->Where('flux', '=', 'v12')
              ->Where('end', '=', 0)
              ->exec();
        
        // On compte le nombre de VO actuel
        $query = new query();
        $query->Select('id')
              ->From('auto')
              ->Where('flux', '=', 'v12')
              ->Where('end', '=', 0)
              ->exec();
        
        $return['maj'] = $query->count;
        
        // Implantation de toutes les class  Flux
        $site = array();
        $dir = opendir(FILE.'/import/v12');
        while ($file = readdir($dir)):
            if(preg_match('#([\w]+).xml$#', $file)):
                // On vérifie la présence et la validité du flux
                $xml = @simplexml_load_file(FILE.'/import/v12/'.$file);
                foreach($xml->VEHICULE as $vehicule):

                    if($vehicule->attributes()->parc != 1):
                        continue;
                    endif;
                    
                    if(str_replace('.00', '', $vehicule->PRIX) == "0"):
                        $return['err']++;
                        continue;
                    endif;
                    
                    $auto = new auto();
                    $auto->set('id',       'v12_'.str_replace('.xml', '', $file).'_'.$vehicule->attributes()->id);
                    $auto->set('ref',      $vehicule->attributes()->id);
                    $auto->set('marque',   $vehicule->MARQUE);
                    $auto->set('modele',   trim($vehicule->MODELE_2));
                    $auto->set('version',  trim(str_replace(strtolower($vehicule->MODELE_2), '', strtolower($vehicule->VERSION))));
                    $auto->set('annee',    $vehicule->ANNEE_MODELE);
                    $auto->set('km',       $vehicule->KILOMETRAGE);
                    $auto->set('energie',  lang::flux('energie:'.slug($vehicule->ENERGIE)));
                    $auto->set('place',    0);
                    $auto->set('porte',    $vehicule->NB_PORTE);
                    $auto->set('couleur',  $vehicule->COULEUR);
                    $auto->set('vitesse',  lang::flux('vitesse:'.slug($vehicule->TRANSMISSION)));
                    $auto->set('rapport',  $vehicule->NB_VITESSE);
                    $auto->set('prix',     str_replace('.00', '', $vehicule->PRIX));
                    $auto->set('garantie', $vehicule->GARANTIE.' ('.$vehicule->GARANTIE_DUREE.')');
                    $auto->set('cat',      lang::flux('cat:'.slug($vehicule->CARROSSERIE)));
                    $auto->set('pf',       $vehicule->CV_F);
                    $auto->set('pr',       $vehicule->CV_DIN);
                    $auto->set('flux',     'v12');
                    $auto->set('save',     1);
                    $auto->set('site',     'v12_'.str_replace('.xml', '', $file));

                    // Photos                    
                    $i = 1;
                    $photos = array();
                    foreach ($vehicule->PHOTOS->PHOTO as $photo):
                        if(!file_exists(WEBROOT.'/images/auto/'.$auto->get('id').'_'.$i.'.jpg')):
                            $get = parent::getPhoto($photo, WEBROOT.'/images/auto/', $auto->get('id').'_'.$i, 'jpg');
                            if($get):
                                $photos[] = $get;
                            endif;
                        else:
                            $photos[] = $auto->get('id').'_'.$i.'.jpg';
                        endif;
                        $i++;
                    endforeach;
                    $auto->set('photo', $photos);
                    
                    // Options
                    $options = array();
                    foreach ($vehicule->EQUIPEMENTS->EQUIPEMENT as $option):
                        $options[]    = mysql_real_escape_string($option);
                    endforeach;
                    $auto->set('option',   $options);
                    $auto->save();
                    
                    // Enregistrement de la concession
                    if(!is_object($site[str_replace('.xml', '', $file)])):
                        $site[str_replace('.xml', '', $file)] = new site(str_replace('.xml', '', $file), 'v12');
                        $site[str_replace('.xml', '', $file)]->set('title',     $vehicule->SITE_NOM);
                        $site[str_replace('.xml', '', $file)]->set('mail',      $vehicule->CONTACT_MAIL);
                        $site[str_replace('.xml', '', $file)]->set('adresse',   $vehicule->SITE_ADRESSE);
                        $site[str_replace('.xml', '', $file)]->set('zipcode',   $vehicule->SITE_CP);
                        $site[str_replace('.xml', '', $file)]->set('city',      $vehicule->SITE_VILLE);
                        $site[str_replace('.xml', '', $file)]->set('phone',     $vehicule->SITE_TELEPHONE);
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
              ->Where('flux', '=', 'v12')
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
              ->Where('flux', '=', 'v12')
              ->Where('end', '=', 0)
              ->Where('save', '=', 0)
              ->exec();
        
        // On met à jour l'import
        $update = new query();
        $update->Update('import')
               ->Set('time', time())
               ->Where("code", "=", "v12")
               ->exec();
        
        return $return;
    }
}
?>