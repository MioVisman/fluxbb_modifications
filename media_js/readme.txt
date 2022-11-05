##
##
##        Mod title:  Media.js
##
##      Mod version:  0.8.3
##  Works on FluxBB:  1.5.4, 1.5.3
##     Release date:  2013-11-15
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (visman@inbox.ru)
##
##      Description:  Мод на стороне пользователя заменяет ссылки на медиа-контент.
##                    Modification on the party of the user replaces links to a media content.
##
##                    v 0.8.3 ОБНОВЛЕНИЕ / UPDATE
##                    Улучшена совместимость при работе форума по протоколу HTTPS.
##                    Улучшена совместимость с моей модификацией "Highlight of search requests".
##                    Compatibility with HTTPS is improved.
##                    Compatibility with my mod "Highlight of search requests" is improved.
##
##                    v 0.7.13 ОБНОВЛЕНИЕ / UPDATE
##                    Проверяет наличие установленного Adobe Flash Player для отображения контента через <object>.
##                    Добавлена поддержка аудио файлов mp3, m4a, ogg, oga, webma, wav через тэг <audio> HTML5.
##                    Добавлена поддержка видео файлов mp4, m4v, ogv, webm, webmv через тэг <video> HTML5.
##                    Checks existence of installed Adobe Flash Player for content display through <object>.
##                    Adds support of audio files mp3, m4a, ogg, oga, webma, wav through of tag <audio> HTML5.
##                    Adds support of video files mp4, m4v, ogv, webm, webmv through of tag <video> HTML5.
##
##                    v 0.6.0 ОБНОВЛЕНИЕ / UPDATE
##                    Для IE изменен метод создания элементов <object>.
##                    Для всех браузеров (и IE 9+) использовано событие DOMContentLoaded для старта скрипта.
##                    Обработанные ссылки получают класс mediajslink. Можно через CSS изменять отображение таких ссылок.
##                    For IE the method of creation of the <object> elements is changed.
##                    For all browsers (and IE 9+) is used DOMContentLoaded event for script start.
##                    The processed links receive the class mediajslink. It is possible to change display of such links through CSS.
##
##                    v 0.5.12 ОБНОВЛЕНИЕ / UPDATE
##                    Добавлена поддержка ign.com (http://www.ign.com/videos/[год]/[месяц]/[день]/[название видео])
##                    Добавлена поддержка twitch.tv
##                    Добавлена поддержка g4tv.com
##                    Добавлена поддержка gamespot.com (http://www.gamespot.com/[название игры]/videos/[название ролика]-[номер ролика]/)
##                    Adds support ign.com (_http://www.ign.com/videos/[year]/[month]/[day]/[name of video])
##                    Adds support twitch.tv
##                    Adds support g4tv.com
##                    Adds support gamespot.com (_http://www.gamespot.com/[name of game]/videos/[name of video]-[number of video]/)
##
##                    v 0.5.10 ОБНОВЛЕНИЕ / UPDATE
##                    Добавлена поддержка плейлистов для Pleer.com
##                    Adds support of playlists for Pleer.com
##
##                    v 0.5.9 ОБНОВЛЕНИЕ / UPDATE
##                    Введены менее жесткие правила для обрабатываемых ссылок.
##                    It contains less strict rules for processed links.
##
##                    v 0.5.7 ПОЛНАЯ ВЕРСИЯ / FULL VERSION
##                    Поддерживает / It is supported
##                    видео / video from: YouTube, Vimeo, Rutube, Yandex, Mail.ru, Smotri, Dailymotion, Metacafe, Sibnet;
##                    аудио / audio from: SoundCloud, ZippyShare, PromoDJ, Pleer.com и прямые ссылки на mp3 / and direct urls on mp3;
##                    и / and Google Maps.
##
##   Repository URL:  http://fluxbb.org/resources/mods/?s=author&t=Visman&v=all&o=name
##                    http://fluxbb.org.ru/forum/viewforum.php?id=34
##
##   Affected files:  /include/parser.php
##                    footer.php
##
##       Affects DB:  No
##
##            Notes:  Russian/English
##
##       DISCLAIMER:  Please note that "mods" are not officially supported by
##                    FluxBB. Installation of this modification is done at 
##                    your own risk. Backup your forum database and any and
##                    all applicable files before proceeding.
##
##

1. install version 0.5.7 http://fluxbb.org/resources/mods/mediajs/releases/0.5.7/

2. upload file media.min.js to folder js/ in your forum.
