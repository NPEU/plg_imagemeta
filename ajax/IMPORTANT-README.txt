IMPORTANT
=========


Below is no longer relavent as I worked around the issue, but if something similar is found in future it may be useful to have these note, so keep for now.

Note it's a problem with Canon Maker Notes not being parsed. There are a couple of similar issues online but no fix.
Summary:
New images that didn't have copyright were looking for one and crashing when encountering maker notes.
What I've done is to wrap the relevant code in a try/catch to prevent it crashing, the code then adds copyright and it all seems to work ok.

---

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


UPDATE Jan 24
Debug shows:
"Found Canon Camera Settings sub IFD at offset 974"

vendor/fileeye/pel/src/PelCanonMakerNotes.php line 232 then crashes.
throw new PelMakerNotesMalformedException('Size of Canon Camera Settings does not match the number of entries.');

This is being thrown.

