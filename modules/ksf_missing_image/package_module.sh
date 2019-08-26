#!/bin/sh

#When packaging up a module to move environments we need to make sure the requrie* and include* dependencies are met!

echo "ensure dependencies are met! But don't include files that aren't tested"
grep require *
grep include *

cd ..
tar czvf ksf_missing_image.tgz ksf_missing_image ksf_modules_common/
