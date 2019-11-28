<?php
function paragraphe($code) {
    global $texte, $paragraphe ;
    $debut = strpos($texte, '<'.$code.'>') ;
    if ($debut>0) {
        $fin = strpos($texte, '</'.$code.'>')+strlen($code)+3 ;
        $paragraphe = substr($texte, $debut, $fin-$debut) ;
        $texte = substr($texte, $fin) ;
    } else $paragraphe = '' ;
}

function valeur($parametre) {
    global $paragraphe ;
    $debut = strpos($paragraphe, '<'.$parametre.'>') ;
    if ($debut>0) {
        $debut = $debut+strlen($parametre)+2 ;
        $fin = strpos($paragraphe, '<',$debut+1) ;
        return trim(substr($paragraphe, $debut, $fin-$debut)) ;
    } else return '' ;
}


$_file_ = $_FILES['fichier_ofx'];
if(is_uploaded_file($_file_['tmp_name']) && $_file_['error'] == 0){
    $ofx_msg = "";
    if($_size_ > 3200000) $ofx_msg = "Erreur: le fichier est trop lourd (max 3M)";
    if(empty($errStr)){
        $fichier = $_file_['tmp_name'] ;
        if (file_exists($fichier)) {
            $file = fopen($fichier, "r") ;
            $texte = '';
            while (!feof($file)) $texte .= fread($file, 8192);
            fclose($file);    
            paragraphe('BANKACCTFROM') ;
            if (strlen($paragraphe)>0) {
                $code_banque = valeur('BANKID') ;
                $code_guichet = valeur('BRANCHID') ;
                $no_compte = valeur('ACCTID') ;
                mysql_select_db($database_locations, $locations);
                $query_rs_compte = "SELECT id, libelle FROM tbl_compte WHERE code_banque='$code_banque' AND code_guichet='$code_guichet' AND no_compte='$no_compte'";
                $rs_compte = mysql_query($query_rs_compte, $locations) or die(mysql_error());
                $row_rs_compte = mysql_fetch_assoc($rs_compte);
                $compte_id = $row_rs_compte['id'] ;
                $totalRows_rs_compte = mysql_num_rows($rs_compte);    
                if ($totalRows_rs_compte == 1) {
                    $values = '' ;
                    $a_supprimer = array("'", ".") ;
                    paragraphe('STMTTRN') ;
                    $i = 0 ;
                    while (strlen($paragraphe)>0) {
                        $i += 1 ;
                        $type = valeur('TRNTYPE') ;
                        $date = valeur('DTPOSTED') ;
                        $montant = valeur ('TRNAMT') ;
                        $reste = $montant ;
                        $banque_mouvement_id = $compte_id.' - '.valeur('FITID') ;
                        $libelle = ucwords(strtolower(str_replace($a_supprimer, ' ', valeur('NAME')))) ;
                        $info = ucwords(strtolower(str_replace($a_supprimer, ' ', valeur ('MEMO')))) ;
                        $values .= "(".$compte_id.",'".$type."',".$date.",".$montant.",".$reste.
                            ",'".$banque_mouvement_id."','".$libelle."','".$info."'), " ;
                        paragraphe('STMTTRN') ;
                    }
                    $values = substr($values, 0, strlen($values)-2) ;
                    mysql_select_db($database_locations, $locations);
                    $query_insert = "INSERT IGNORE INTO tbl_mouvement (compte_id, type, `date`, montant, reste, banque_mouvement_id, libelle, info) VALUES $values";
                    if (mysql_query($query_insert, $locations) == 1)
                        $ofx_msg = "Importation réussie de $i mouvements dans le compte ".$row_rs_compte['libelle'].'<br />'.mysql_info($locations) ;
                    else $ofx_msg = "Erreur dans l'insertion des mouvements" ;
                } else $ofx_msg = "Erreur: le compte bancaire $code_banque / $code_guichet / $no_compte n'existe pas" ;
            } else $ofx_msg = "Erreur: le fichier ne semble pas être un fichier OFX valide" ;
        } else $ofx_msg = "Erreur: échec lors de l'ouverture du fichier $fichier" ;
    } else $ofx_msg = "Erreur: le fichier n'a pas été téléchargé" ;
} else $ofx_msg = "Erreur: vous n'avez pas choisi de fichier" ;
?> 

