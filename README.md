COMPOSER TOOLS
==============

This script verify if new versions of your dependencies are availables to help you to maintain up to date your composer.json.
It shows the latest version of dependencies if it will not be taken into account by your composer.json but doesn't modify your composer.json.

The verification is based on your minimum level of stability required. Actually only dev and stable stability are supported.

REQUIREMENTS
============

Composer must be install globally. See [https://github.com/sanplomb/composer](https://github.com/composer/composer#global-installation-of-composer-manual "Instruction for global install") for instruction

USAGE
=====

Just run the `check_new_version.sh` script.
You could pass the directory of your project as argument otherwise the script ask you where find your compose.json.

```
./check_new_version.sh path/to/your/project/
```
