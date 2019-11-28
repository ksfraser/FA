#!/bin/sh


NEWENV=$1
MOD=$2

if [ "" == $MOD ]
then

	for x in ksf_qoh EXPORT_WOO ksf_data_dictionary ksf_generate_catalogue Inventory  ksf_dashboard ksf_cron ksf_missing_image ksf_sale_price ksf_import_dream ksf_price_suggest ksf_purchase_orders_suggest
	do
		cp $x ../../../$NEWENV/fhs/frontaccounting/modules
		cd ../../../$NEWENV/fhs/frontaccounting/modules
		tar xzvf $x.tgz
	done
else
	echo cp $MOD to $NEWENV
	x=$MOD
	cp $x.tgz ../../../../$NEWENV/fhs/frontaccounting/modules
	cd ../../../../$NEWENV/fhs/frontaccounting/modules
	tar xzvf $x.tgz
fi



