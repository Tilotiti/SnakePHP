<?php

class sage extends import  {
    private
        $code = array();
    
    public function load() {
        $dir = opendir(FILE.'/import/sage');
        while ($file = readdir($dir)):
            if(preg_match('#([\w]+).zip$#', $file)):
                $zip = new ZipArchive();
                $zip->open(FILE.'/import/sage/'.$file);
                $zip->extractTo(FILE.'/import/sage/');
                $zip->close();
                
                $code = str_replace('.zip', '', $file);
                
                $this->code[] = $code;

                @chmod(FILE.'/import/sage/'.$code.'.xml', 0777);
            endif;
        endwhile;
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
              ->Where('flux', '=', 'sage')
              ->exec();
        
        // On compte le nombre de VO actuel
        $query = new query();
        $query->Select('id')
              ->From('auto')
              ->Where('flux', '=', 'sage')
              ->Where('end', '=', 0)
              ->exec();
        
        $return['maj'] = $query->count;
        
        foreach($this->code as $code):

            $xml = @simplexml_load_file(FILE.'/import/sage/'.$code.'.xml');
        
            $site = new site($code, 'sage');
            $site->set('title', $xml->coordonnees->raison_sociale);
            $site->set('adresse', $xml->coordonnees->adresse);
            $site->set('zipcode', $xml->coordonnees->code_postal);
            $site->set('city', $xml->coordonnees->ville);
            $site->set('mail', $xml->coordonnees->email);
            $site->set('phone', $xml->coordonnees->telephone);
            $site->save();
        
            foreach($xml->annonce as $annonce):

                if(!isset($annonce->offre->prix) || $annonce->offre->prix == "0"):
                    $return['err']++;
                    continue;
                endif;

                // Enregistrement de l'auto
                $auto = new auto();
                $auto->set('id',       'sage_'.$code.'_'.$annonce['id']);
                $auto->set('ref',      $annonce->reference);
                $auto->set('marque',   $annonce->vehicule->marque);
                $auto->set('modele',   $annonce->vehicule->modele);
                $auto->set('version',  trim(str_replace('NOUVELLE', '', str_replace(strtoupper($annonce->vehicule->marque), '', str_replace(strtoupper($annonce->vehicule->modele), '', strtoupper($annonce->vehicule->version))))));
                $auto->set('annee',    $annonce->vehicule->millesime);
                $auto->set('km',       $annonce->vehicule->kilometrage);
                $auto->set('energie',  lang::flux('energie:'.slug($annonce->vehicule->energie)));
                $auto->set('place',    $annonce->vehicule->nb_places);
                $auto->set('porte',    $annonce->vehicule->nb_portes);
                $auto->set('couleur',  $annonce->vehicule->couleur);
                $auto->set('vitesse',  lang::flux('vitesse:'));
                $auto->set('rapport',  0);
                $auto->set('mec',      $annonce->vehicule->mise_en_circulation);
                $auto->set('type',     'VP');
                $auto->set('prix',     $annonce->offre->prix);
                $auto->set('garantie', '');
                $auto->set('cat',      lang::flux('cat:'.slug($annonce->vehicule->carrosserie)));
                $auto->set('pf',       $annonce->vehicule->puissance_fiscale);
                $auto->set('pr',       $annonce->vehicule->puissance_reelle);
                $auto->set('flux',     'sage');
                $auto->set('save',     1);
                $auto->set('site',     'sage_'.$code);
                $auto->set('end',      0);

                // Photos
                $photos = array();
                if(isset($annonce->photos)):
                    $i = 0;
                    foreach($annonce->photos->photo as $photo):
                        $i++;
                        $get = parent::getPhoto($photo, WEBROOT.'/images/auto/', 'sage_'.$annonce['id'].'_'.$i);
                        if($get):
                            $photos[] = $get;
                        endif;
                    endforeach;
                    $auto->set('photo', $photos);
                endif;
                
                //echo $return['total']." : ".$code."_".$annonce['id']."<br />";
                
                $auto->save();

                $return['total'] ++;
            endforeach;
        endforeach;
        
        // On compte les VO non mis à jour
        $query->Select('id')
              ->From('auto')
              ->Where('flux', '=', 'sage')
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
              ->Where('flux', '=', 'sage')
              ->Where('end', '=', 0)
              ->Where('save', '=', 0)
              ->exec();
        
        // On met à jour l'import
        $update = new query();
        $update->Update('import')
               ->Set('time', time())
               ->Where('code', '=', 'sage')
               ->exec();
        
        return $return;
    }
}
?>
