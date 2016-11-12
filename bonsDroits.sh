#!/bin/sh

if [ "$(id -u)" != 0 ]; then
    echo "il faut être super-utilisateur"
    exit 1
fi

# l'utilisateur $specialuser est autorisé à bricoler
specialuser=georgesk

chmod 2775 .
chown $specialuser:www-data .

chown www-data:www-data edt.db
setfacl -m u:$specialuser:rw edt.db
