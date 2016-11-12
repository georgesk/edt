#!/bin/sh

# l'utilisateur $specialuser est autorisé à bricoler
specialuser=$(id -un)

sudo chmod 2775 .
sudo chown $specialuser:www-data .

sudo chown www-data:www-data edt.db
sudo setfacl -m u:$specialuser:rw edt.db
