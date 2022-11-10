##
##
##        Mod title:  Loginza
##
##      Mod version:  1.3.2
##  Works on FluxBB:  1.5.3
##     Release date:  2013-03-11
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (visman@inbox.ru)
##
##      Description:  Plugin for a quick registration and authorization of new users, using accounts from other sites through a service loginza.ru.
##                    Currently the plugin supports many popular portals (Yandex, Google, Rambler, Mail.Ru, etc). And social networks (Twitter, Vkontakte and Facebook), as well as OpenID identifiers.
##                    Плагин для быстрой регистрации и авторизации новых пользователей, используя учетные записи с других сайтов, через сервис loginza.ru.
##                    В данный момент плагин поддерживает много популярных порталов (Yandex, Google, Rambler, Mail.Ru и тп.) и социальные сети (Twitter, Вконтакте и Facebook), а так же OpenID идентификаторы.
##
##                    v 1.1.2
##                    French is added. Thanks to adaur.
##
##                    v 1.2.0
##                    Кнопка Loginza добавлена на каждую страницу форума.
##                    Button Loginza is added in each page of forum.
##
##                    v 1.2.1
##                     Update for FluxBB 1.4.6
##
##                    v 1.2.2
##                     Добавлен LinkedIn.
##
##                    v 1.3.0
##                     Добавлен LiveJournal.
##                     Update for FluxBB 1.5.0
##
##                    v 1.3.2
##                     Update for FluxBB 1.5.3
##
##   Repository URL:  http://fluxbb.org/resources/mods/?s=author&t=Visman&v=all&o=name
##                    http://fluxbb.org/forums/viewtopic.php?id=5218
##                    http://fluxbb.org.ru/forum/viewtopic.php?id=3393
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

file reglog.php; folders include, lang, plugins, img to /

#
#---------[ 2. DO THIS ]------------------------------------------------------
#

Administration -> Plugin "Loginza" -> Install