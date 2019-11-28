#!/bin/sh

#When packaging up a module to move environments we need to make sure the requrie* and include* dependencies are met!

echo "ensure dependencies are met! But don't include files that aren't tested"
grep require *php
grep include *php

doxygen.exe documentation.doxyfile

cd ..
tar czvf ksf_purchase_orders_suggest.tgz ksf_purchase_orders_suggest ksf_modules_common/*php
