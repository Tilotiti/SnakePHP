<?php

class planetvo extends import  {
    
    public function load() {
        
        $ftp = ftp_connect('publicationvo.com');
        if(!$ftp):
            return("Impossible de se connecter au serveur FTP.");
        endif;

        if(!ftp_login($ftp, "digitaldealer", "Z7gljahQZW")):
            return("Impossible de se connecter au serveur FTP : Login incorrect.");
        endif;

        if(!ftp_get($ftp, FILE.'/import/planetvo/stockvo.xml', 'datas/stockvo.xml', FTP_BINARY)):
            return("Impossible de télécharger le fichier '/datas/stockvo.xml' depuis le serveur FTP.");
        endif;

        if(!ftp_get($ftp, FILE.'/import/planetvo/photos.txt.zip', 'datas/photos.txt.zip', FTP_BINARY)):
            return("Impossible de télécharger le fichier '/datas/photos.txt.zip' depuis le serveur FTP.");
        endif;

        $zip = new ZipArchive();
        $zip->open(FILE.'/import/planetvo/photos.txt.zip');
        $zip->extractTo(FILE.'/import/planetvo/');
        $zip->close();

        $file = file(FILE.'/import/planetvo/photos.txt');

        foreach($file as $line):
            $array = explode("\t", $line);
            if(file_exists(FILE.'/import/planetvo/photos/'.$array[0])):
                if(!md5_file(FILE.'/import/planetvo/photos/'.$array[0]) != $array[2]):
                    ftp_get($ftp, FILE.'/import/planetvo/photos/'.$array[0], $array[1], FTP_BINARY);
                endif;
            else:
                ftp_get($ftp, FILE.'/import/planetvo/photos/'.$array[0], $array[1], FTP_BINARY);
            endif;
        endforeach;
    }
    
    public function save() {
        $return['add']   = 0;
        $return['err']   = 0;
        $return['maj']   = 0;
        $return['del']   = 0;
        $return['total'] = 0;
        
        // On rétablie les VO
        $query = new query();
        $query->Update('auto')
              ->Set('save', 0)
              ->Where('flux', '=', 'planetvo')
              ->Where('end', '=', 0)
              ->exec();
        
        // On compte le nombre de VO actuel
        $query = new query();
        $query->Select('id')
              ->From('auto')
              ->Where('flux', '=', 'planetvo')
              ->Where('end', '=', 0)
              ->exec();
        
        $return['maj'] = $query->count;
        
        // On vérifie la présence et la validité du flux
        if(!$xml = @simplexml_load_file(FILE.'/import/planetvo/stockvo.xml')):
            return array('error' => lang::error('import/planetvo:xml'));
        endif;
        
        $site = array();
        
        // On enregistre les VO
        foreach($xml->Vehicule as $xml):
            if($xml->PvTTC == "0"):
                $return['err']++;
                continue;
            endif;
            
            // Enregistrement de l'auto
            $auto = new auto();
            $auto->set('id',       'planetvo_'.$xml->CodePVO.'_'.$xml->Numpoli);
            $auto->set('ref',      $xml->Numpoli);
            $auto->set('marque',   $xml->Marque);
            $auto->set('modele',   $xml->Famille);
            $auto->set('type',     $xml->Genre);
            $auto->set('version',  $xml->Version);
            $auto->set('annee',    $xml->Annee);
            $auto->set('mec',      $xml->Date1Mec);
            $auto->set('km',       $xml->Km);
            if(empty($xml->Energie)):
                $auto->set('energie',  "n/d");
            else:
                $auto->set('energie',  $xml->Energie);
            endif;
            $auto->set('place',    $xml->NbPlaces);
            $auto->set('porte',    $xml->NbPortes);
            $auto->set('couleur',  $xml->Couleur);
            $auto->set('vitesse',  $xml->Boite);
            $auto->set('rapport',  $xml->NbRapports);
            $auto->set('prix',     $xml->PvTTC);
            $auto->set('garantie', $xml->Garantie);
            $auto->set('option',   $xml->Equipements);
            $auto->set('cat',      $xml->Categorie);
            $auto->set('pf',       $xml->Puissance);
            $auto->set('pr',       $xml->PuissanceReelle);
            $auto->set('flux',     'planetvo');
            $auto->set('save',     1);
            $auto->set('site',     'planetvo_'.$xml->CodePVO);
            
            // Photos
            $photos = array();
            if(!empty($xml->Photos)):
                foreach(explode('|', $xml->Photos) as $photo):
                    $get = parent::getPhoto(FILE.'/import/planetvo/photos/'.$photo, WEBROOT.'/images/auto/', 'planetvo_'.$photo);
                    if($get):
                        $photos[] = $get;
                    endif;
                endforeach;
                $auto->set('photo', $photos);
            endif;
            
            // 1er Main
            if($xml->PremiereMain == "VRAI"):
                $auto->set('main', 1);
            else:    
                $auto->set('main', 0);
            endif;
            $auto->save();
            
            // Enregistrement de la concession
            if(!is_object($site[(string)$xml->CodePVO])):
                $site[(string)$xml->CodePVO] = new site((string)$xml->CodePVO, 'planetvo');
                $site[(string)$xml->CodePVO]->set('title', $xml->Nom);
                $site[(string)$xml->CodePVO]->set('marque', ucfirst(strtolower($xml->Societe_marque)));
                $site[(string)$xml->CodePVO]->set('mail', $xml->Email);
                $site[(string)$xml->CodePVO]->set('adresse', $xml->Adresse);
                $site[(string)$xml->CodePVO]->set('adresse2', $xml->AdresseSuite);
                $site[(string)$xml->CodePVO]->set('zipcode', $xml->Cpostal);
                $site[(string)$xml->CodePVO]->set('city', $xml->Ville);
                $site[(string)$xml->CodePVO]->set('phone', $xml->Telephone);
            endif;
            $return['total'] ++;
        endforeach;
        
        foreach($site as $concession):
            $concession->save();
        endforeach;
        
        // On compte les VO non mis à jour
        $query = new query();
        $query->Select('id')
              ->From('auto')
              ->Where('flux', '=', 'planetvo')
              ->Where('end', '=', 0)
              ->Where('save', '=', 0)
              ->exec();
        
        // On calcul les statistiques
        $return['maj'] = $return['maj'] - $query->count;
        $return['del'] = $query->count;
        $return['add'] = $return['del'] + $return['total'] - $return['maj'];
        
        // On marque les VO manquants comme vendus
        $query->Update('auto')
              ->Set('end', time())
              ->Where('flux', '=', 'planetvo')
              ->Where('end', '=', 0)
              ->Where('save', '=', 0)
              ->exec();
        
        // On met à jour l'import
        $update = new query();
        $update->Update('import')
               ->Set('time', time())
               ->Where("code", "=", "planetvo")
               ->exec();
        
        return $return;
    }
}
?>
