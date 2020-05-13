<?php

/* Author kevin Fraser
*
*	Take GnuCash csv export files and conver to MultiImport csv.
*
*/

/*
*
001/2018	Marcia	1	
002/2018	Kevin	1
003/2018	Marcia and Kevin	1	
004/2018	Condo	2
005/2018	Piping KSFP	2	
006/2018	Insurance And Investments KSFII	2
007/2018	Fraser Highland Shoppe	2
008/2018	Travel	2
009/2018	Trains	2
010/2018	Eating	2	
011/2018	Utilities	2
012/2018	Living Expenses	2
013/2018	House	2
014/2018	Truck	2
015/2018	MLFT	2
016/2018	Medical	2	
017/2018	Home and Yard Maintenance	2
018/2018	Scotland Trip 2018	2
019/2018	Transportation	2
001/2019	Springs Cres	2
002/2019	Family Reunion	2	
003/2019	Drum Lessons	2
004/2019	Clothing	2
005/2019	Gambling	2
006/2019	Furnace	2	
*/

$conf = array();
$conf["9281.3 Car Insurance"]["account"] = "9281.3";  
$conf["9281.3 Car Insurance"]["dim1"] = "003/2018";  
$conf["9281.3 Car Insurance"]["dim2"] = "014/2018";  
$conf["9152 Internet"]["account"] = "9152";  
$conf["9152 Internet"]["dim1"] = "003/2018";  
$conf["9152 Internet"]["dim2"] = "011/2018";  
$conf["5785.4 Lucy"]["account"] = "5785.4";  
$conf["5785.4 Lucy"]["dim1"] = "001/2018";  
$conf["5785.4 Lucy"]["dim2"] = "012/2018";  
$conf["8521.5 Advertising Expenses"]["account"] = "8521.5";  
$conf["8521.5 Advertising Expenses"]["dim1"] = "001/2018";  
$conf["8521.5 Advertising Expenses"]["dim2"] = "015/2018";  
$conf["8961.2 windridge"]["account"] = "8961.2";  
$conf["8961.2 windridge"]["dim1"] = "003/2018";  
$conf["8961.2 windridge"]["dim2"] = "013/2018";  
$conf["Lotto"]["account"] = "5785.6";  
$conf["Lotto"]["dim1"] = "003/2018";  
$conf["Lotto"]["dim2"] = "005/2019";  
$conf["5785.6 Lotto"]["account"] = "5785.6";  
$conf["5785.6 Lotto"]["dim1"] = "003/2018";  
$conf["5785.6 Lotto"]["dim2"] = "005/2019";  
$conf["Groceries"]["account"] = "8523.2";  
$conf["Groceries"]["dim1"] = "003/2018";  
$conf["Groceries"]["dim2"] = "010/2018";  
$conf["8523.2 Groceries"]["account"] = "8523.2";  
$conf["8523.2 Groceries"]["dim1"] = "003/2018";  
$conf["8523.2 Groceries"]["dim2"] = "010/2018";  
$conf["9180.2 Property Tax Springs Cres"]["account"] = "9180.2";  
$conf["9180.2 Property Tax Springs Cres"]["dim1"] = "003/2018";  
$conf["9180.2 Property Tax Springs Cres"]["dim2"] = "001/2019";  
$conf["Property Tax Windridge Road"]["account"] = "9180";  
$conf["Property Tax Windridge Road"]["dim1"] = "003/2018";  
$conf["Property Tax Windridge Road"]["dim2"] = "013/2018";  
$conf["9281.7 Repair and Maintenance"]["account"] = "9281.7";  
$conf["9281.7 Repair and Maintenance"]["dim1"] = "003/2018";  
$conf["9281.7 Repair and Maintenance"]["dim2"] = "014/2018";  
$conf["9281.1 Gas"]["account"] = "9281.1";  
$conf["9281.1 Gas"]["dim1"] = "003/2018";  
$conf["9281.1 Gas"]["dim2"] = "014/2018";  
$conf["8523.6 shared dining cost"]["account"] = "8523.6";  
$conf["8523.6 shared dining cost"]["dim1"] = "003/2018";  
$conf["8523.6 shared dining cost"]["dim2"] = "010/2018";  
$conf["8523.6 marcia"]["account"] = "8523.6";  
$conf["8523.6 marcia"]["dim1"] = "001/2018";  
$conf["8523.6 marcia"]["dim2"] = "010/2018";  
$conf["8523.6 Dining"]["account"] = "8523.6";  
$conf["8523.6 Dining"]["dim1"] = "001/2018";  
$conf["8523.6 Dining"]["dim2"] = "010/2018";  
$conf["Dining Out"]["account"] = "8523.6";  
$conf["Dining Out"]["dim1"] = "003/2018";  
$conf["Dining Out"]["dim2"] = "010/2018";  
$conf["FHS"]["account"] = "3400";  
$conf["FHS"]["dim1"] = "002/2018";  
$conf["FHS"]["dim2"] = "007/2018";  
$conf["fhs"]["account"] = "3400";  
$conf["fhs"]["dim1"] = "002/2018";  
$conf["fhs"]["dim2"] = "007/2018";  
$conf["1070 Walmart Mastercard 2251"]["account"] = "1070";  
$conf["1070 Walmart Mastercard 2251"]["dim1"] = "";  
$conf["1070 Walmart Mastercard 2251"]["dim2"] = "";  
$conf["1070.1 Walmart Points"]["account"] = "1070.1";  
$conf["1070.1 Walmart Points"]["dim1"] = "";  
$conf["1070.1 Walmart Points"]["dim2"] = "";  
$conf["MLFT"]["account"] = "8811";  
$conf["MLFT"]["dim1"] = "001/2018";  
$conf["MLFT"]["dim2"] = "015/2018";  
$conf["5786.2 marcia"]["account"] = "5786.2";  
$conf["5786.2 marcia"]["dim1"] = "001/2018";  
$conf["5786.2 marcia"]["dim2"] = "015/2018";  
$conf["1060 CIBC Checking Account"]["account"] = "1060";  
$conf["1060 CIBC Checking Account"]["dim1"] = "002/2018";  
$conf["1060 CIBC Checking Account"]["dim2"] = "";  
$conf["CIBC Savings Account"]["account"] = "1061";  
$conf["CIBC Savings Account"]["dim1"] = "002/2018";  
$conf["CIBC Savings Account"]["dim2"] = "";  
$conf["AR Matt"]["account"] = "3750.2";  
$conf["AR Matt"]["dim1"] = "001/2018";  
$conf["AR Matt"]["dim2"] = "012/2018";  
$conf["Mortgage"]["account"] = "3750.2";  
$conf["Mortgage"]["dim1"] = "001/2018";  
$conf["Mortgage"]["dim2"] = "004/2018";  
$conf["AR Marcia"]["account"] = "3750.2";  
$conf["AR Marcia"]["dim1"] = "001/2018";  
$conf["AR Marcia"]["dim2"] = "012/2018";  
$conf["Hearing Aides"]["account"] = "3750.2";  
$conf["Hearing Aides"]["dim1"] = "001/2018";  
$conf["Hearing Aides"]["dim2"] = "016/2018";  
$conf["3400 Fraser Highland Shoppe"]["account"] = "3400";  
$conf["3400 Fraser Highland Shoppe"]["dim1"] = "002/2018";  
$conf["3400 Fraser Highland Shoppe"]["dim2"] = "007/2018";  
$conf["Walmart Points"]["account"] = "1070.1";  
$conf["Walmart Points"]["dim1"] = "";  
$conf["Walmart Points"]["dim2"] = "";  
$conf["Imbalance-CAD"]["account"] = "1001";  
$conf["Imbalance-CAD"]["dim1"] = "";  
$conf["Imbalance-CAD"]["dim2"] = "";  
$conf["Car Insurance"]["account"] = "9281.3";  
$conf["Car Insurance"]["dim1"] = "003/2018";  
$conf["Car Insurance"]["dim2"] = "014/2018";  
$conf["5785.3 Music/Movies"]["account"] = "5785.3";  
$conf["5785.3 Music/Movies"]["dim1"] = "003/2018";  
$conf["5785.3 Music/Movies"]["dim2"] = "012/2018";  
$conf["Springs Cres Maintenance"]["account"] = "8961.1";  
$conf["Springs Cres Maintenance"]["dim1"] = "003/2018";  
$conf["Springs Cres Maintenance"]["dim2"] = "001/2019";  
$conf["Living Expenses"]["account"] = "5786";  
$conf["Living Expenses"]["dim1"] = "003/2018";  
$conf["Living Expenses"]["dim2"] = "012/2018";  
$conf["5786.2 Clothes"]["account"] = "5786.2";  
$conf["5786.2 Clothes"]["dim1"] = "003/2018";  
$conf["5786.2 Clothes"]["dim2"] = "012/2018";  
$conf["5786.1 Books"]["account"] = "5786.1";  
$conf["5786.1 Books"]["dim1"] = "002/2018";  
$conf["5786.1 Books"]["dim2"] = "012/2018";  
$conf["Books"]["account"] = "5786";  
$conf["Books"]["dim1"] = "002/2018";  
$conf["Books"]["dim2"] = "012/2018";  
$conf["Computer"]["account"] = "5786";  
$conf["Computer"]["dim1"] = "002/2018";  
$conf["Computer"]["dim2"] = "012/2018";  
$conf["dump"]["account"] = "8961.1";  
$conf["dump"]["dim1"] = "003/2018";  
$conf["dump"]["dim2"] = "001/2019";  
$conf["5785.2 Model Railroad"]["account"] = "5785.2";  
$conf["5785.2 Model Railroad"]["dim1"] = "002/2018";  
$conf["5785.2 Model Railroad"]["dim2"] = "009/2018";  
$conf["reno2018"]["account"] = "8961.1";  
$conf["reno2018"]["dim1"] = "003/2018";  
$conf["reno2018"]["dim2"] = "001/2019";  
$conf["kevin"]["account"] = "5786";  
$conf["kevin"]["dim1"] = "002/2018";  
$conf["kevin"]["dim2"] = "012/2018";  
$conf["honeymoon"]["account"] = "9834";  
$conf["honeymoon"]["dim1"] = "003/2018";  
$conf["honeymoon"]["dim2"] = "012/2018";  
$conf["Backyard"]["account"] = "8961.1";  
$conf["Backyard"]["dim1"] = "003/2018";  
$conf["Backyard"]["dim2"] = "001/2019";  
$conf["8223 Gifts"]["account"] = "8223";  
$conf["8223 Gifts"]["dim1"] = "003/2018";  
$conf["8223 Gifts"]["dim2"] = "012/2018";  
$conf["Gifts"]["account"] = "8223";  
$conf["Gifts"]["dim1"] = "003/2018";  
$conf["Gifts"]["dim2"] = "012/2018";  
$conf["Medical Expenses"]["account"] = "9000";  
$conf["Medical Expenses"]["dim1"] = "003/2018";  
$conf["Medical Expenses"]["dim2"] = "016/2018";  
$conf["Hobbies"]["account"] = "5785.2";  
$conf["Hobbies"]["dim1"] = "002/2018";  
$conf["Hobbies"]["dim2"] = "009/2018";  
$conf["Trailer"]["account"] = "9281.3";  
$conf["Trailer"]["dim1"] = "002/2018";  
$conf["Trailer"]["dim2"] = "007/2018";  
$conf["1067 PC Mastercard 7293 5863 2992"]["account"] = "1067";  
$conf["1067 PC Mastercard 7293 5863 2992"]["dim1"] = "";  
$conf["1067 PC Mastercard 7293 5863 2992"]["dim2"] = "";  
$conf["Walmart"]["account"] = "8223";  	//Walmart Gift Cards
$conf["Walmart"]["dim1"] = "002/2018";  
$conf["Walmart"]["dim2"] = "";  
$conf["Walmart GC"]["account"] = "8223";  	//Walmart Gift Cards
$conf["Walmart GC"]["dim1"] = "002/2018";  
$conf["Walmart GC"]["dim2"] = "";  
$conf["WFG"]["account"] = "8871.3";  	
$conf["WFG"]["dim1"] = "002/2018";  
$conf["WFG"]["dim2"] = "006/2018";  
$conf["wfg"]["account"] = "8871.3";  	
$conf["wfg"]["dim1"] = "002/2018";  
$conf["wfg"]["dim2"] = "006/2018";  
$conf["8523.3 wfg"]["account"] = "8523.3";  	
$conf["8523.3 wfg"]["dim1"] = "002/2018";  
$conf["8523.3 wfg"]["dim2"] = "006/2018";  
$conf["Springs Cres Insurance"]["account"] = "8690";  	
$conf["Springs Cres Insurance"]["dim1"] = "003/2018";  
$conf["Springs Cres Insurance"]["dim2"] = "013/2018";  
$conf["1061 CIBC Savings Account"]["account"] = "1061";  	
$conf["1061 CIBC Savings Account"]["dim1"] = "002/2018";  
$conf["1061 CIBC Savings Account"]["dim2"] = "";  
$conf["8810.1 Office Expenses KSFP"]["account"] = "8810.1";  	
$conf["8810.1 Office Expenses KSFP"]["dim1"] = "002/2018";  
$conf["8810.1 Office Expenses KSFP"]["dim2"] = "005/2018";  
$conf["Office Expenses Piping"]["account"] = "8810.1";  	
$conf["Office Expenses Piping"]["dim1"] = "002/2018";  
$conf["Office Expenses Piping"]["dim2"] = "005/2018";  
$conf["8810.3 ksfii"]["account"] = "8810.3";  	
$conf["8810.3 ksfii"]["dim1"] = "002/2018";  
$conf["8810.3 ksfii"]["dim2"] = "";  
$conf["AR AGS"]["account"] = "9270.1";  	
$conf["AR AGS"]["dim1"] = "002/2018";  
$conf["AR AGS"]["dim2"] = "005/2018";  
$conf["Airdrie Gaelic Society"]["account"] = "9270.1";  	
$conf["Airdrie Gaelic Society"]["dim1"] = "002/2018";  
$conf["Airdrie Gaelic Society"]["dim2"] = "005/2018";  
$conf["Visa 0307"]["account"] = "1069";  	
$conf["Visa 0307"]["dim1"] = "002/2018";  
$conf["Visa 0307"]["dim2"] = "";  
$conf["Windridge Insurance"]["account"] = "8690";  	
$conf["Windridge Insurance"]["dim1"] = "003/2018";  
$conf["Windridge Insurance"]["dim2"] = "013/2018";  
$conf["Scotland 2018"]["account"] = "9834.1";  	
$conf["Scotland 2018"]["dim1"] = "003/2018";  
$conf["Scotland 2018"]["dim2"] = "018/2018";  
$conf["food"]["account"] = "8523";  	
$conf["food"]["dim1"] = "003/2018";  
$conf["food"]["dim2"] = "018/2018";  
$conf["Other Income"]["account"] = "8223";  	
$conf["Other Income"]["dim1"] = "003/2018";  
$conf["Other Income"]["dim2"] = "012/2018";  
$conf["Creek Springs Insurance"]["account"] = "8690";  	
$conf["Creek Springs Insurance"]["dim1"] = "003/2018";  
$conf["Creek Springs Insurance"]["dim2"] = "013/2018";  
$conf["Education"]["account"] = "8871.3";  	
$conf["Education"]["dim1"] = "002/2018";  
$conf["Education"]["dim2"] = "006/2018";  
$conf["9223 9221 Gas Electricity"]["account"] = "9221";  	
$conf["9223 9221 Gas Electricity"]["dim1"] = "003/2018";  
$conf["9223 9221 Gas Electricity"]["dim2"] = "011/2018";  
$conf["PC Points"]["account"] = "1067.1";  
$conf["PC Points"]["dim1"] = "003/2018";  
$conf["PC Points"]["dim2"] = "010/2018";  
$conf["1074.1 Travelbrands"]["account"] = "1074.1";  
$conf["1074.1 Travelbrands"]["dim1"] = "001/2018";  
$conf["1074.1 Travelbrands"]["dim2"] = "015/2018";  
$conf["1067.1 PC Points"]["account"] = "1067.1";  
$conf["1067.1 PC Points"]["dim1"] = "003/2018";  
$conf["1067.1 PC Points"]["dim2"] = "010/2018";  
$conf["1067.1 Superbucks"]["account"] = "1067.1";  
$conf["1067.1 Superbucks"]["dim1"] = "003/2018";  
$conf["1067.1 Superbucks"]["dim2"] = "010/2018";  
$conf["Gifts Received"]["account"] = "8223";  
$conf["Gifts Received"]["dim1"] = "002/2018";  
$conf["Gifts Received"]["dim2"] = "012/2018";  
$conf["Meals 100%"]["account"] = "8523.1";  	
$conf["Meals 100%"]["dim1"] = "002/2018";  
$conf["Meals 100%"]["dim2"] = "005/2018";  
$conf["Recreation"]["account"] = "8523";  	
$conf["Recreation"]["dim1"] = "003/2018";  
$conf["Recreation"]["dim2"] = "012/2018";  

$conf["9000.1 Kevin"]["account"] = "9000.1";  	
$conf["9000.1 Kevin"]["dim1"] = "002/2018";  
$conf["9000.1 Kevin"]["dim2"] = "016/2018";  
$conf["9000.1 Medical Expenses"]["account"] = "9000.1";  	
$conf["9000.1 Medical Expenses"]["dim1"] = "003/2018";  
$conf["9000.1 Medical Expenses"]["dim2"] = "016/2018";  

$conf["windridge"]["account"] = "8961.2";  	
$conf["windridge"]["dim1"] = "003/2018";  
$conf["windridge"]["dim2"] = "013/2018";  
$conf["9222 Water (City of Airdrie)"]["account"] = "9222";  	
$conf["9222 Water (City of Airdrie)"]["dim1"] = "003/2018";  
$conf["9222 Water (City of Airdrie)"]["dim2"] = "011/2018";  
$conf["9281 Auto"]["account"] = "9281";  
$conf["9281 Auto"]["dim1"] = "003/2018";  
$conf["9281 Auto"]["dim2"] = "014/2018";  
$conf["Auto Payment"]["account"] = "2620.1";  
$conf["Auto Payment"]["dim1"] = "003/2018";  
$conf["Auto Payment"]["dim2"] = "014/2018";  

$conf["Appliances"]["account"] = "8961.1";
$conf["Appliances"]["dim1"] = "003/2018";  
$conf["Appliances"]["dim2"] = "001/2019";  
$conf["Costco Mastercard 7888 6887"]["account"] = "2100.2";  	
$conf["Costco Mastercard 7888 6887"]["dim1"] = "001/2018";  
$conf["Costco Mastercard 7888 6887"]["dim2"] = "";  
$conf["fitness"]["account"] = "5786";  
$conf["fitness"]["dim1"] = "001/2018";  
$conf["fitness"]["dim2"] = "012/2018";  
$conf["wedding"]["account"] = "5786";  
$conf["wedding"]["dim1"] = "003/2018";  
$conf["wedding"]["dim2"] = "012/2018";  
$conf["Home Depot"]["account"] = "1001.1";  	
$conf["Home Depot"]["dim1"] = "003/2018";  
$conf["Home Depot"]["dim2"] = "";  
$conf["8961 Furnace"]["account"] = "8961";  	
$conf["8961 Furnace"]["dim1"] = "003/2018";  
$conf["8961 Furnace"]["dim2"] = "001/2019";  

$conf["9200.5a Travel"]["account"] = "9200.5a";  
$conf["9200.5a Travel"]["dim1"] = "001/2018";  
$conf["9200.5a Travel"]["dim2"] = "008/2018";  
$conf["8811.5 Supplies"]["account"] = "8811.5";  
$conf["8811.5 Supplies"]["dim1"] = "001/2018";  
$conf["8811.5 Supplies"]["dim2"] = "008/2018";  
$conf["8810.5 Office Expenses"]["account"] = "8810.5";  
$conf["8810.5 Office Expenses"]["dim1"] = "001/2018";  
$conf["8810.5 Office Expenses"]["dim2"] = "008/2018";  
$conf["8523.5 Meals and Entertainment"]["account"] = "8523.5";  
$conf["8523.5 Meals and Entertainment"]["dim1"] = "001/2018";  
$conf["8523.5 Meals and Entertainment"]["dim2"] = "008/2018";  
$conf["9200.2019.7 Family Reunion"]["account"] = "9200.2019.7";  
$conf["9200.2019.7 Family Reunion"]["dim1"] = "001/2018";  
$conf["9200.2019.7 Family Reunion"]["dim2"] = "008/2018";  

$conf["5786.3 Grooming"]["account"] = "5786.3";  
$conf["5786.3 Grooming"]["dim1"] = "001/2018";  
$conf["5786.3 Grooming"]["dim2"] = "008/2018";  
$conf["8690.5 Insurance"]["account"] = "8690.5";  
$conf["8690.5 Insurance"]["dim1"] = "001/2018";  
$conf["8690.5 Insurance"]["dim2"] = "008/2018";  
$conf["5786 Marcia Living"]["account"] = "5786";  
$conf["5786 Marcia Living"]["dim1"] = "001/2018";  
$conf["5786 Marcia Living"]["dim2"] = "012/2018";  
$conf["9281.13 Parking"]["account"] = "9281.13";  
$conf["9281.13 Parking"]["dim1"] = "003/2018";  
$conf["9281.13 Parking"]["dim2"] = "014/2018";  
$conf["1072 CIBC HISA 01729 53-77897"]["account"] = "1072";  
$conf["1072 CIBC HISA 01729 53-77897"]["dim1"] = "002/2018";  
$conf["1072 CIBC HISA 01729 53-77897"]["dim2"] = "";  
$conf["5690 Bank Service Charge"]["account"] = "5690";  
$conf["5690 Bank Service Charge"]["dim1"] = "003/2018";  
$conf["5690 Bank Service Charge"]["dim2"] = "006/2018";  
$conf["1066 Cheques in Wallet"]["account"] = "1066";  
$conf["1066 Cheques in Wallet"]["dim1"] = "002/2018";  
$conf["1066 Cheques in Wallet"]["dim2"] = "";  
$conf["1066 Cash in Wallet"]["account"] = "1066";  
$conf["1066 Cash in Wallet"]["dim1"] = "002/2018";  
$conf["1066 Cash in Wallet"]["dim2"] = "";  
$conf["GST Personal Credit"]["account"] = "8230.1";  
$conf["GST Personal Credit"]["dim1"] = "003/2018";  
$conf["GST Personal Credit"]["dim2"] = "";  
$conf["8230 GOVT"]["account"] = "8230.1";  
$conf["8230 GOVT"]["dim1"] = "003/2018";  
$conf["8230 GOVT"]["dim2"] = "";  
$conf["CIBC"]["account"] = "1186.1";  
$conf["CIBC"]["dim1"] = "002/2018";  
$conf["CIBC"]["dim2"] = "";  
$conf["1168.1 TFSA"]["account"] = "1186.1";  
$conf["1168.1 TFSA"]["dim1"] = "002/2018";  
$conf["1168.1 TFSA"]["dim2"] = "";  
$conf["1072.3 Simplii Low Cost Borrowing Account 379"]["account"] = "1072.3";  
$conf["1072.3 Simplii Low Cost Borrowing Account 379"]["dim1"] = "002/2018";  
$conf["1072.3 Simplii Low Cost Borrowing Account 379"]["dim2"] = "";  
$conf["1069 Visa 0307"]["account"] = "1069";  
$conf["1069 Visa 0307"]["dim1"] = "002/2018";  
$conf["1069 Visa 0307"]["dim2"] = "";  
$conf["9281.4 Registration"]["account"] = "9281.4";  
$conf["9281.4 Registration"]["dim1"] = "003/2018";  
$conf["9281.4 Registration"]["dim2"] = "014/2018";  
$conf["1072.4 Manu Advantage"]["account"] = "1072.4";  
$conf["1072.4 Manu Advantage"]["dim1"] = "003/2018";  
$conf["1072.4 Manu Advantage"]["dim2"] = "";  
$conf["1072.1 PC Interest Plus 998"]["account"] = "1072.1";  
$conf["1072.1 PC Interest Plus 998"]["dim1"] = "002/2018";  
$conf["1072.1 PC Interest Plus 998"]["dim2"] = "";  


$conf["Piping"]["account"] = "4010.1";  
$conf["Piping"]["dim1"] = "002/2018";  
$conf["Piping"]["dim2"] = "005/2018";  
$conf["AR Piping Performances"]["account"] = "4010.1";  
$conf["AR Piping Performances"]["dim1"] = "002/2018";  
$conf["AR Piping Performances"]["dim2"] = "005/2018";  
$conf["AR Piping Performances One-off"]["account"] = "4010.1";  
$conf["AR Piping Performances One-off"]["dim1"] = "002/2018";  
$conf["AR Piping Performances One-off"]["dim2"] = "005/2018";  
$conf["AR Tim Ford"]["account"] = "4010.1";  
$conf["AR Tim Ford"]["dim1"] = "002/2018";  
$conf["AR Tim Ford"]["dim2"] = "005/2018";  
$conf["AR Duncan Hawkins"]["account"] = "4010.1";  
$conf["AR Duncan Hawkins"]["dim1"] = "002/2018";  
$conf["AR Duncan Hawkins"]["dim2"] = "005/2018";  
$conf["AR BJ Fontana"]["account"] = "4010.1";  
$conf["AR BJ Fontana"]["dim1"] = "002/2018";  
$conf["AR BJ Fontana"]["dim2"] = "005/2018";  
$conf["AR Janet Shivas"]["account"] = "4010.1";  
$conf["AR Janet Shivas"]["dim1"] = "002/2018";  
$conf["AR Janet Shivas"]["dim2"] = "005/2018";  
$conf["AR David Poffenroth"]["account"] = "4010.1";  
$conf["AR David Poffenroth"]["dim1"] = "002/2018";  
$conf["AR David Poffenroth"]["dim2"] = "005/2018";  
$conf["AR Diane MacKay"]["account"] = "4010.1";  
$conf["AR Diane MacKay"]["dim1"] = "002/2018";  
$conf["AR Diane MacKay"]["dim2"] = "005/2018";  
$conf["Dane Bruce"]["account"] = "4010.1";  
$conf["Dane Bruce"]["dim1"] = "002/2018";  
$conf["Dane Bruce"]["dim2"] = "005/2018";  
$conf["AR Elisa Blanchard"]["account"] = "4010.1";  
$conf["AR Elisa Blanchard"]["dim1"] = "002/2018";  
$conf["AR Elisa Blanchard"]["dim2"] = "005/2018";  

$conf["AR Marcia"]["account"] = "3750.2";  
$conf["AR Marcia"]["dim1"] = "001/2018";  
$conf["AR Marcia"]["dim2"] = "";  
$conf["RRSP Cash"]["account"] = "2300.1";  
$conf["RRSP Cash"]["dim1"] = "002/2018";  
$conf["RRSP Cash"]["dim2"] = "";  
$conf["cash"]["account"] = "2300.5";  
$conf["cash"]["dim1"] = "002/2018";  
$conf["cash"]["dim2"] = "";  
$conf["Brokerage Account"]["account"] = "2300.5";  
$conf["Brokerage Account"]["dim1"] = "002/2018";  
$conf["Brokerage Account"]["dim2"] = "";  
$conf["Honorarium"]["account"] = "8223";  
$conf["Honorarium"]["dim1"] = "002/2018";  
$conf["Honorarium"]["dim2"] = "";  
$conf["Federal Income Tax Payable"]["account"] = "2110";  
$conf["Federal Income Tax Payable"]["dim1"] = "002/2018";  
$conf["Federal Income Tax Payable"]["dim2"] = "";  
$conf["Taxes"]["account"] = "2110";  
$conf["Taxes"]["dim1"] = "002/2018";  
$conf["Taxes"]["dim2"] = "";  
$conf["8710.5 Windridge"]["account"] = "8710.5";  
$conf["8710.5 Windridge"]["dim1"] = "003/2018";  
$conf["8710.5 Windridge"]["dim2"] = "";  
$conf["Home Depot"]["account"] = "8961";  
$conf["Home Depot"]["dim1"] = "003/2018";  
$conf["Home Depot"]["dim2"] = "013/2018";  
$conf["HELOC"]["account"] = "2101";  
$conf["HELOC"]["dim1"] = "003/2018";  
$conf["HELOC"]["dim2"] = "001/2019";  


$conf["Savings Interest"]["account"] = "4440";  
$conf["Savings Interest"]["dim1"] = "002/2018";  
$conf["Savings Interest"]["dim2"] = "008/2019";  

?>
