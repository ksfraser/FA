#!/bin/sh

HOST='fhs-laptop1'
USER='root'
PASS='m1l1ce'
DATABASE='fhs'

DESC=$1
AMT=$2
TAX_B=$3
GL=$4
DIM1=$5
DIM2=$6

ACCOUNT="INSERT ignore into 1_chart_master(account_code, account_code2, account_name, account_type, inactive) values ('$GL', '$GL', '$DESC', '16', '0')"
echo "$ACCOUNT" | mysql -h $HOST -u $USER -p$PASS $DATABASE 

QUERY="INSERT into 1_quick_entries(type, description, base_amount, base_desc, bal_type) values ('3', '$DESC', '$AMT', '$DESC', '0')"
echo "$QUERY" | mysql -h $HOST -u $USER -p$PASS $DATABASE 

CARD="INSERT into 1_quick_entry_lines( qid, amount, memo, action, dest_id, dimension_id, dimension2_id ) SELECT id, '-100', '$DESC', '%', '1069', '$DIM1', '$DIM2' from 1_quick_entries where description='$DESC' order by id desc limit 1"  
echo "$CARD" | mysql -h $HOST -u $USER -p$PASS $DATABASE 

if [ "1" -eq $TAX_B ]
then
	LINE="INSERT into 1_quick_entry_lines( qid, amount, memo, action, dest_id, dimension_id, dimension2_id ) SELECT id, '95.2381', '$DESC', '%-', '$GL', '$DIM1', '$DIM2' from 1_quick_entries where description='$DESC' order by id desc limit 1"
	echo "$LINE" | mysql -h $HOST -u $USER -p$PASS $DATABASE 
	TAX="INSERT into 1_quick_entry_lines( qid, amount, memo, action, dest_id, dimension_id, dimension2_id ) SELECT id, '0', '$DESC GST', '=', '$GL', '$DIM1', '$DIM2' from 1_quick_entries where description='$DESC' order by id desc limit 1"
	echo "$TAX" | mysql -h $HOST -u $USER -p$PASS $DATABASE 
else
	LINE="INSERT into 1_quick_entry_lines( qid, amount, memo, action, dest_id, dimension_id, dimension2_id ) SELECT id, '100', '$DESC', '%', '$GL', '$DIM1', '$DIM2' from 1_quick_entries where description='$DESC' order by id desc limit 1"
	echo "$LINE" | mysql -h $HOST -u $USER -p$PASS $DATABASE 
fi
