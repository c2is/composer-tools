#!/bin/sh

if [ "$1" != "" ]; then
    PROJECT_DIR=$1
else
    echo "Veuillez indiquer le répertoire de votre fichier composer.json:"
    read PROJECT_DIR
fi

php check-updates.php $PROJECT_DIR