#!/bin/sh

#When packaging up a module to move environments we need to make sure the requrie* and include* dependencies are met!

echo "ensure dependencies are met! But don't include files that aren't tested"
grep require *php
grep include *php

cd ..
tar czvf ksf_suitecrm.tgz ksf_suitecrm ksf_modules_common/*php ../includes/types.inc ../sales/includes/db/customers_db.inc ../sales/includes/db/branches_db.inc ../includes/db/crm_contacts_db.inc
