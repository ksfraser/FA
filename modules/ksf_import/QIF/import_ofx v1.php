<?php
// Session control 
session_start(); 
if(!session_is_registered(login)) { 
header("Location: ../login/login.htm");
exit;
}
else {

include ('../../lib/lib.php');

connectDB(); //connect to database

function paragraphe($code) {
    global $data, $paragraphe ;
    $debut = strpos($data, '<'.$code.'>') ;
    if ($debut>0) {
        $fin = strpos($data, '</'.$code.'>')+strlen($code)+3 ;
        $paragraphe = substr($data, $debut, $fin-$debut) ;
        $data = substr($data, $fin) ;
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

//function to get values stored between STMTTRN flags in OFX files
function get_stmttrn_value($tag){
    global $trn_str;
    return substr($trn_str,strpos($trn_str,'<'.$tag.'>') + strlen('<'.$tag.'>'),strpos($trn_str,'<',(strpos($trn_str,'<'.$tag.'>') + strlen('<'.$tag.'>'))) - (strpos($trn_str,'<'.$tag.'>') + strlen('<'.$tag.'>')));
}

//function to get values from QIF file
function get_qif_value($tag){
    global $trn_str;
    return substr($trn_str,strpos($trn_str,"\n" .$tag) + 2,(strpos($trn_str,"\n",strpos($trn_str,"\n" .$tag)+2) - (strpos($trn_str,"\n" .$tag) + 2)));
}

$created = date("Y-m-d");
$affected_rows = "";// initialize affected rows
$creator = $_SESSION[login];

$_file_ = $_FILES['bank_file'];

    if(is_uploaded_file($_file_['tmp_name']) && $_file_['error'] == 0){
        $ofx_msg = "";
        if($_file_['size'] > 3200000) $ofx_msg = "Erreur: le fichier est trop gros (max 3MO)";
        $file_name = $_file_['tmp_name'];
//test if file opens correctly
        if(file_exists($file_name)){
            $file = fopen($file_name, "r");
                $data = fread( $file, filesize($file_name) ); 
            fclose($file);
//test OFX format
            if(strpos($data,'</OFX>') - strpos($data,'<OFX>') > 0){
// OFX version checking, this script is able to read scripts up to version 211
                $max_version = 211;
                $ofxver = substr($data,strpos($data,'VERSION:') +8,strpos($data,"\n",strpos($data,'VERSION:')) - strpos($data,'VERSION:') -8);
                if($ofxver <= $max_version){
                    paragraphe('BANKACCTFROM');
                    $BANKID = valeur('BANKID') ;
                    $BRANCHID = valeur('BRANCHID') ;
                    $ACCTID = valeur('ACCTID') ;
                    $ACCTKEY = valeur('ACCTKEY');
    
                    $SQL = "SELECT *"
                        ." FROM account"
                        ." WHERE account_id=" .$_POST[account_id]
                        ." AND bank_id='" .$BANKID ."'"
                        ." AND branch_id='" .$BRANCHID ."'"
                        ." AND account_no='" .$ACCTID ."'"
                        ." AND account_key='" .$ACCTKEY ."'";
                    $req = mysql_query($SQL);
                    $match_account = mysql_numrows($req);

                    if($match_account == 1){
                        $i = 0;
                        $pos_start_data = strpos($data,'<STMTTRN>');
                        $len_data = strpos($data,"</BANKTRANLIST>") - $pos_start_data;
                        $data_str = substr($data,$pos_start_data,$len_data);
                        $start_trn_str = strpos($data_str,'<STMTTRN>');
                        $len_trn_str = strpos($data_str,'</STMTTRN>') + 10;
                        $nb_trn = substr_count($data_str,'<STMTTRN>');
    
                        while($i < $nb_trn){
                        $trn_str = substr($data_str,$start_trn_str,$len_trn_str);
                        $trntype = get_stmttrn_value('TRNTYPE');
                        $dtposted = get_stmttrn_value('DTPOSTED');
                        $trnamt = get_stmttrn_value('TRNAMT');
                        $fitid = get_stmttrn_value('FITID');
                        $checknum = "";
                        if(strpos($trn_str,'<CHECKNUM>') > 0){
                        $checknum = get_stmttrn_value('CHECKNUM');
                        }
                        $name = "";
                        if(strpos($trn_str,'<NAME>') > 0){
                        $name = str_replace("'","''",get_stmttrn_value('NAME'));
                        }
                        $memo = "";
                        if(strpos($trn_str,'<MEMO>') > 0){
                        $memo = str_replace("'","''",get_stmttrn_value('MEMO'));
                        }
        
                        $value .= "('" .$fitid ."_" .$_POST[account_id] ."',"
                            ."'" .$name ."',"
                            ."'" .$memo ."',"
                            ."'" .$trnamt ."',"



                            ."'" .$dtposted ."',"
                            ."'" .$trntype ."',"
                            ."'" .$_POST[account_id] ."',"
                            ."19,"
                            ."62,"
                            ."'" .$checknum ."',"
                            ."'no',"
                            ."'" .$created ."',"
                            ."'" .$creator ."')";
                        if($i + 1 < $nb_trn){$value .= ",";};
                        $start_trn_str = strpos($data_str,'<STMTTRN>',$start_trn_str + 1);
                        $len_trn_str = (strpos($data_str,'</STMTTRN>',$start_trn_str) - $start_trn_str) + 10;
                        $i++;
                        }
        
                        $SQL = "INSERT IGNORE INTO transaction"
                            ." (trans_id,title,memo,amount,date,mp_id,account_id,cat_id,u_cat_id,check_no,point,created,creator)"
                            ." VALUES ". $value;

                        mysql_query($SQL);
                        $affected_rows = mysql_affected_rows();
                        header("Location: index.php?account_id=" .$_POST[account_id] ."&affected_rows=" .$affected_rows);
                        exit;
                    }else header("Location: import.php?account_id=" .$_POST[account_id] ."&err_no=4");//wrong bank account in OFX file
                    exit;
                } else header("Location: import.php?account_id=" .$_POST[account_id] ."&err_no=5");//OFX version not supported
                exit;
            } else;// not an OFX file
            if(is_numeric($affected_rows) == 0){ //if no OFX file parsed

    //QIF file test
                if(substr_count($data,'!Type:Bank') == 1){
                    //QIF file import
                    $nb_trn = substr_count($data,"^");
                    $start_trn_str = strpos($data,"\nD");
                    $len_trn_str = strpos($data,"^",$start_trn_str) - $start_trn_str;
                    $i = 0;
                    while($i < $nb_trn){
                    $trn_str = substr($data,$start_trn_str,$len_trn_str);
    
                    $date = frdate2isodate(get_qif_value('D'));
                    $amount = get_qif_value('T');
                    $id = get_qif_value('N');
                    $title = get_qif_value('M');
                    $checknum = "";
                    $trntype = "";
                    // test if transaction is a check, only valid for "La Bred" bank
                    if(substr_count($trn_str,'CHEQUE PAIEMENT') == 1){
                    $checknum = get_qif_value('N');
                    $trntype = "CHECK";
                    }
                    $value .= "('" .$id ."_" .$_POST[account_id] ."',"
                        ."'" .$title ."',"
                        ."'" .$amount ."',"
                        ."'" .$date ."',"
                        ."'" .$trntype ."',"
                        ."'" .$_POST[account_id] ."',"
                        ."19,"
                        ."62,"
                        ."'" .$checknum ."',"
                        ."'no',"
                        ."'" .$created ."',"
                        ."'" .$creator ."')";
                    if($i + 1 < $nb_trn){$value .= ",";};

                    $start_trn_str = strpos($data,"\nD",$start_trn_str +2);
                    $len_trn_str = strpos($data,"^",$start_trn_str) - $start_trn_str;
                    $i++;
                    }
                    $SQL = "INSERT IGNORE INTO transaction"
                    ." (trans_id,title,amount,date,mp_id,account_id,cat_id,u_cat_id,check_no,point,created,creator)"
                    ." VALUES ". $value;
//                    echo $SQL;
                    mysql_query($SQL);
                    $affected_rows = mysql_affected_rows();
                    header("Location: index.php?account_id=" .$_POST[account_id] ."&affected_rows=" .$affected_rows);
                    exit;
                }else header("Location: import.php?account_id=" .$_POST[account_id] ."&err_no=3");//wrong file format, neither an OFX nor a QIF
                exit;
            }else header("Location: index.php?account_id=" .$_POST[account_id] ."&affected_rows=" .$affected_rows);//SQL query ran successfully
            exit;
        } else header("Location: import.php?account_id=" .$_POST[account_id] ."&err_no=2");//not possible to open file
        exit;
    } else header("Location: import.php?account_id=" .$_POST[account_id] ."&err_no=1");//no file uploaded
    exit;
}// session
?> 




