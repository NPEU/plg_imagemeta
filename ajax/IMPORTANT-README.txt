IMPORTANT
=========

I've "x"d out the composer file to avoid any automatic updates here.

Explanation:

"lsolesen/pel": "^0.9.6"

This works on PHP 7.4 in general.
Upgrading to PHP8 causes it to crash because of compat issues.

Upgrading to 0.9.12 causes crash on some images:
e.g. https://www.npeu.ox.ac.uk/assets/images/sites/Brhc.jpg
- some kind of Canon data problem which I can't fix.

SO, I've stuck with 0.9.6 and used the following commands to fix compat issues:

phpcbf [..]\vendor\lsolesen
phpcbf -p --standard=C:/Users/akirk/AppData/Roaming/Composer/vendor/phpcompatibility/php-compatibility/PHPCompatibility --runtime-set testVersion 8.2 [..]\vendor\lsolesen