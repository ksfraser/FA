#!/bin/sh

for x in includes lang sql _init images inquiry manage reports
do
	mkdir $x
	cd $x
	cat > index.php << FIN
<?php
header("Location: ../index.php");
FIN
	cd ..
done

cd includes
for x in db ui 
do
	mkdir $x
	cd $x
	cat > index.php << EOF
<?php
header("Location: ../index.php");
EOF
	cd ..
done

cd ..
