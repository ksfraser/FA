#!/bin/sh

#When packaging up a module to move environments we need to make sure the requrie* and include* dependencies are met!

echo "ensure dependencies are met! But don't include files that aren't tested"
grep require *php
grep include *php

cd ..
tar czvf ksf_drop_ship.tgz ksf_drop_ship ksf_modules_common/*php
