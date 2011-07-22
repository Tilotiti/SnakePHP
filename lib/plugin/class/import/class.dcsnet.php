<?php

class dcsnet extends import  {
    
    public
        $flux = false;
    
    public function __construct() {
        $this->flux = array('PC3189');
    }
    
    public function load() {
        
        $ftp = ftp_connect('ftp.auto-site.fr');
        if(!$ftp):
            return array('error' => lang::error('import/dcsnet:ftpConnect'));
        endif;
        
        if(!ftp_login($ftp, "dcsnet@auto-site.fr", "dcsnet2011")):
            return array('error' => lang::error('import/dcsnet:ftpLogin'));
        endif;
        
        foreach($this->flux as $file):

            if(!ftp_get($ftp, FILE.'/import/dcsnet/'.$file.'.tar.Z', 'VOW'.$file.'.tar.Z', FTP_BINARY)):
                return array('error' => lang::error('import/dcsnet:ftpGet'));
            endif;

            shell_exec('cd '.FILE.'/import/dcsnet/ 
tar -zxvf '.$file.'.tar.Z');

        endforeach;

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
              ->Where('flux', '=', 'dcsnet')
              ->Where('end', '=', 0)
              ->exec();
        
        // On compte le nombre de VO actuel
        $query = new query();
        $query->Select('id')
              ->From('auto')
              ->Where('flux', '=', 'dcsnet')
              ->Where('end', '=', 0)
              ->exec();
        
        $return['maj'] = $query->count;
        
        foreach($this->flux as $code):
            $ligne = file(FILE.'/import/dcsnet/VOW'.$code.'.txt');
            foreach($ligne as $line):
                $array = explode('|', $line);
            
                if($array[18] != 0):
                    $auto = new auto();
                    $auto->set('id',       'dcsnet_'.$code.'_'.$array[0]);
                    $auto->set('ref',      $array[0]);
                    $auto->set('marque',   $array[5]);
                    $auto->set('modele',   $array[46]);
                    $auto->set('version',  $array[47]);
                    $auto->set('annee',    $array[2]);
                    $auto->set('km',       $array[13]);
                    $auto->set('type',     $array[4]);
                    $auto->set('mec',      $array[3]);
                    $auto->set('energie',  lang::flux('energie:'.slug($array[8])));
                    $auto->set('place',    "");
                    $auto->set('porte',    $array[52]);
                    $auto->set('couleur',  ucfirst(strtolower($array[16])));
                    $auto->set('vitesse',  lang::flux('vitesse:'.slug($array[53])));
                    $auto->set('rapport',  0);
                    $auto->set('prix',     $array[18]);
                    $auto->set('garantie', ucfirst(strtolower($array[21])));

                    $option = array();
                    for($i = 81; $i < 111; $i++):
                        if(!empty($array[$i])):
                            $option[] = ucfirst(strtolower($array[$i]));
                        endif;
                    endfor;

                    $auto->set('option',   $option);
                    $auto->set('cat',      lang::flux('cat:'.slug($array[7])));
                    $auto->set('pf',       $array[9]);
                    $auto->set('flux',     'dcsnet');
                    $auto->set('save',     1);
                    $auto->set('end',      0);
                    $auto->set('site',     'dcsnet_'.$code);

                    $photos = array();
                    $image = array(116, 117, 118, 119, 120, 121, 122, 123, 126, 127);
                    foreach($image as $i):
                        if(!empty($array[$i])):
                            $get = parent::getPhoto(FILE.'/import/dcsnet/'.$array[$i], WEBROOT.'/images/auto/', 'dcsnet_'.$array[$i]);
                            if($get):
                                $photos[] = $get;
                            endif;
                        endif;
                    endforeach;
                    $auto->set('photo', $photos);
                    $auto->save();

                    // Enregistrement de la concession
                    if(!is_object($site[$code])):
                        $site[$code] = new site($code, 'dcsnet');
                        $site[$code]->set('title', ucfirst(strtolower($array[37])));
                        $site[$code]->set('phone', $array[39]);
                    endif;
                    $return['total'] ++;
                else:
                    $return['err']++;
                endif;
                
            endforeach;
            
        endforeach;
        
        foreach($site as $concession):
            $concession->save();
        endforeach;
        
        // On compte les VO non mis à jour
        $query->Select('id')
              ->From('auto')
              ->Where('flux', '=', 'dcsnet')
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
              ->Where('flux', '=', 'dcsnet')
              ->Where('end', '=', 0)
              ->Where('save', '=', 0)
              ->exec();
        
        // On met à jour l'import
        $update = new query();
        $update->Update('import')
               ->Set('time', time())
               ->Where('code', '=', 'dcsnet')
               ->exec();
        
        return $return;
    }
}
?>
