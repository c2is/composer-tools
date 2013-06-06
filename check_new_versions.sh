#!/bin/sh

if [ "$1" != "" ]; then
    PROJECT_DIR=$1
else
    printf "Veuillez indiquer le répertoire de votre fichier composer.json:"
    read PROJECT_DIR
fi

COMPOSER_FILE=$PROJECT_DIR'/composer.json'

if [ -f $COMPOSER_FILE ]; then
    VALIDATE=`composer validate $COMPOSER_FILE | grep ' is valid'`

    if [ "$VALIDATE" ]; then
        php check_new_versions.php $PROJECT_DIR
    else
        printf "Votre fichier fichier composer.json comporte des erreurs. Veuillez les corriger avant vérifier les mises à jour possible."
        printf "Rapport de composer :"
        composer validate $COMPOSER_FILE
    fi
else
    printf "Le répertoire indiqué ne comporte pas de fichier composer.json"
fi