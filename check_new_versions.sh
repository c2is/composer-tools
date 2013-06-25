#!/bin/bash

# Check new versions of dependancies
#
# @author  Sylvain PLANCON <sylvain.plancon@c2is.fr>
# @package composer-tools by C2IS

BASEDIR=$(dirname $0)

if [ "$1" != "" ]; then
    PROJECT_DIR=$1
else
    echo -n "Veuillez indiquer le répertoire de votre fichier composer.json : "
    read PROJECT_DIR
fi

COMPOSER_FILE=$PROJECT_DIR'/composer.json'

if [ -f $COMPOSER_FILE ]; then
    VALIDATE=`composer validate $COMPOSER_FILE | grep ' is valid'`

    if [ "$VALIDATE" ]; then
        if [[ ! $PROJECT_DIR != ^/ && ! $PROJECT_DIR != ^~ ]]; then
            PROJECT_DIR=`pwd`'/'$PROJECT_DIR
        fi
        if [ "$PROJECT_DIR" != "*/" ]; then
            PROJECT_DIR=$PROJECT_DIR'/'
        fi

        if [ "$2" != "" ]; then
            php $BASEDIR/check_new_versions.php $PROJECT_DIR $2
        else
            php $BASEDIR/check_new_versions.php $PROJECT_DIR
        fi
    else
        echo "Votre fichier fichier composer.json comporte des erreurs. Veuillez les corriger avant vérifier les mises à jour possible."
        echo "Rapport de composer :"
        composer validate $COMPOSER_FILE
    fi
else
    echo "Le répertoire indiqué ne comporte pas de fichier composer.json."
fi