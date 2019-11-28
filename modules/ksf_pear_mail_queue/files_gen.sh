#!/bin/sh

mkdir _init
find ./ -type f -print0 | xargs -0 sha1sum| sed -e 's#\s*\.*\.# #'| awk -F'[ ]' '{ print substr($2,2), ":", tolower($1) }'| sed -e 's# :#:#g'| sort -r > _init/files

