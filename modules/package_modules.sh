#!/bin/sh

#When packaging up a module to move environments we need to make sure the requrie* and include* dependencies are met!

#echo "ensure dependencies are met! But don't include files that aren't tested"
#grep require *
#grep include *


for x in ksf_qoh EXPORT_WOO ksf_data_dictionary ksf_generate_catalogue Inventory ksf_dashboard ksf_cron ksf_missing_image ksf_sale_price ksf_import_dream ksf_price_suggest ksf_purchase_orders_suggest
do
	cd $x
	doxygen documentation.doxyfile
	cd -
	tar czvf $x.tgz $x ksf_modules_common/
done

tar czvf $1.tgz $1 ksf_modules_common/


