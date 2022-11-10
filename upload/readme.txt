##
##
##        Mod title:  Upload Mod
##
##      Mod version:  3.0.0 beta
##  Works on FluxBB:  1.5.11
##     Release date:  2019-11-07
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (mio.visman@yandex.ru)
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
##                    v 1.3.0
##                      German is added. Thanks to cyberman.
##                      For FluxBB v.1.5.0
##                      Settings for each group of users.
##                      Administration of files is changed.
##
##                    v 1.3.1
##                      Fix bug in create thumbnails for .jpg and .jpe files. Thanks to Ian Stanistreet.
##
##                    v 1.3.2
##                      For FluxBB v.1.5.1
##                      Fix AP_Upload.php
##
##                    v 2.0 beta
##                      For FluxBB v.1.5.4
##                      Новый вид списка загруженных файлов.
##                      New type of the list of the uploaded files.
##
##                    v 2.0.1
##                      Fix AP_Upload.php. Thanks to Quy.
##
##                    v 2.2.0 beta
##                      New uploader for browsers with FormData support (https://caniuse.com/#search=FormData).
##                      Extended blacklist of file types for upload.
##                      New .htaccess for img/members/ folder.
##                      Automatically add bb-code to the message when uploading a file.
##
##                    v 2.2.1 beta
##                      Fix AP_Upload.php for SQLite.
##
##                    v 2.2.2 beta
##                      Updated .htaccess for img/members/ folder.
##                      A group of administrators can set limits for themselves.
##
##                    v 2.3.0 beta
##                      Maximum "Space allocated to members" increased from 2 GiB to 20 TiB. For 32 and 64-bit systems.
##                      Maximum "Max size members can upload" remained unchanged and depends on the server/PHP settings and OS bit depth.
##
##                    v 3.0.0 beta
##                      Added support for ImageMagick graphics library.
##                      The file mentioned in the forum posts cannot be deleted.
##                        The search for the mention of the file goes through the search index and further LIKE in the message.
##                        Admin can delete files without checks in the admin plugin.
##
##
##   Repository URL:  https://fluxbb.org/resources/mods/?s=author&t=Visman&v=all&o=name
##                    https://fluxbb.qb7.ru/forum/viewtopic.php?id=3380
##
##   Affected files:  footer.php
##                    include/functions.php
##
##       Affects DB:  No (Yes in plugin)
##
##            Notes:  Russian/English/French/German
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

upfiles.php to /
img/ to /img/
include/ to /include/
lang/ to /lang/
plugins/ to /plugins/
style/ to /style/

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

#
# Adjust this plugin in Administration - Plugins menu - Upload
#
