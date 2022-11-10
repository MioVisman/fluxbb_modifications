##
##
##        Mod title:  uLogin
##
##      Mod version:  1.2.0
##  Works on FluxBB:  1.5.11
##     Release date:  2021-01-16
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (mio.visman@yandex.ru)
##
##      Description:  Plugin for a quick registration and authorization of new users, using accounts from other sites through a service ulogin.ru.
##                    Currently the plugin supports many popular portals (Yandex, Google, Mail.Ru, etc). And social networks (Twitter, Vkontakte and Facebook), as well as OpenID identifiers.
##                    Плагин для быстрой регистрации и авторизации новых пользователей, используя учетные записи с других сайтов, через сервис ulogin.ru.
##                    В данный момент плагин поддерживает много популярных порталов (Yandex, Google, Mail.Ru и тп.) и социальные сети (Twitter, Вконтакте и Facebook), а так же OpenID идентификаторы.
##
##   Repository URL:  https://fluxbb.org/resources/mods/?s=author&t=Visman&v=all&o=name
##
##   Affected files:  The plugin itself makes changes to forum files.
##
##       Affects DB:  Yes
##
##            Notes:  Russian/English/French
##
##                    REQUIRES: CURL or allow_url_fopen = ON, ONLY PHP5.
##
##                    Should work at an original forum and on my version /
##                    / Должен работать на оригинальном форуме и на моей версии
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

Upload all the files from the folder /files to your FTP.

# file ulogin.php; folders include, lang, plugins, img to /

#
#---------[ 2. DO THIS ]------------------------------------------------------
#

Administration -> Plugin "uLogin" -> Install
