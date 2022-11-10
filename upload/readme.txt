##
##
##        Mod title:  Upload Mod
##
##      Mod version:  1.2.0
##  Works on FluxBB:  1.4.4, 1.4.5
##     Release date:  2011-04-07
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (visman@inbox.ru)
##                    On a basis "Uploadile" by bagu (http://fluxbb.org/resources/mods/uploadile/)
##
##      Description:  Users can upload files and pictures on a forum at directly in post.
##                    Юзеры могут загружать файлы и картинки на форум непосредственно при постинге сообщений.
##
##                    v 1.2.0
##                      French is added. Thanks to Bloody.
##                      Has added management of the uploaded files in a profile.
##                      В профиль добавил возможность загрузки/удаления файлов.
##
##   Repository URL:  http://fluxbb.org/resources/mods/?s=author&t=Visman&v=all&o=name
##
##   Affected files:  footer.php
##                    include/functions.php
##
##       Affects DB:  No (Yes in plugin)
##
##            Notes:  Russian/English
##                    Functions move_uploaded_file(), mkdir(), opendir() and others
##                    must be enabled in your Website and this one must accept
##                    resizing pictures with GD.
##
##       DISCLAIMER:  Please note that "mods" are not officially supported by
##                    FluxBB. Installation of this modification is done at 
##                    your own risk. Backup your forum database and any and
##                    all applicable files before proceeding.
##
##


#
#---------[ 1. UPLOAD ]-------------------------------------------------------
#

file upfiles.php; folders include, img, lang, plugins to /

#
#---------[ 2. OPEN ]---------------------------------------------------------
#

footer.php

#
#---------[ 3. FIND ]---------------------------------------------------------
#

ob_start();

#
#---------[ 4. AFTER, ADD ]---------------------------------------------------
#

require PUN_ROOT.'include/uploadf.php';

#
#---------[ 5. SAVE ]---------------------------------------------------------
#

footer.php

#
#---------[ 6. OPEN ]---------------------------------------------------------
#

include/functions.php

#
#---------[ 7. FIND ]---------------------------------------------------------
#

					<li<?php if ($page == 'privacy') echo ' class="isactive"'; ?>><a href="profile.php?section=privacy&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section privacy'] ?></a></li>

#
#---------[ 8. AFTER, ADD ]---------------------------------------------------
#

<?php require PUN_ROOT.'include/uploadp.php'; ?>

#
#---------[ 9. SAVE ]---------------------------------------------------------
#

include/functions.php
