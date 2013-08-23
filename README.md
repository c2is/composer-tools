COMPOSER TOOLS
==============

This script verify if new versions of your dependencies are availables to help you to maintain up to date your composer.json.
It shows the latest version of dependencies if it will not be taken into account by your composer.json but doesn't modify your composer.json.

The verification is based on your minimum level of stability required. Actually only dev and stable stabilities are supported.

REQUIREMENTS
============

Composer must be install globally. See [https://github.com/composer/composer](https://github.com/composer/composer#global-installation-of-composer-manual "Instruction for global install") for instruction

USAGE
=====

Just run the `check_new_version.sh` script.
You could pass the directory of your project as argument otherwise the script ask you where find your compose.json.

```
./check_new_version.sh path/to/your/project/
```

LANGUAGE SUPPORT
================

Script's output support actually english and french
To specify which language you want to use, just add language code as second argument (en = english, fr = french).

```
./check_new_version.sh path/to/your/project/ fr
```

If you don't specify language code, the script use your system language or use english if your system language isn't supported.
