##
##
##        Mod title:  Media.js
##
##      Mod version:  1.5.0
##  Works on FluxBB:  1.5.10, 1.5.9, 1.5.8, 1.5.7, 1.5.6, 1.5.5, 1.5.4, 1.5.3
##     Release date:  2018-04-08
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (mio.visman@yandex.ru)
##
##      Description:  Мод на стороне пользователя заменяет ссылки на медиа-контент.
##                    Modification on the party of the user replaces links to a media content.
##
##                    Поддерживает / It is supported
##                    видео / video: YouTube, Vimeo, Rutube, Mail.ru, Smotri, Dailymotion, Metacafe, Sibnet, Facebook, Coub, Vine, VK, IGN, Twitch, g4tv, Gamespot, OK.ru, LiveLeak.com, video.online.ua, www.aparat.com и прямые ссылки на mp4, m4v, ogv, webm, webmv / and direct urls for mp4, m4v, ogv, webm, webmv;
##                    аудио / audio: SoundCloud, ZippyShare, PromoDJ, Mixcloud, Hulkshare и прямые ссылки на mp3, m4a, ogg, oga, webma, wav / and direct urls for mp3, m4a, ogg, oga, webma, wav;
##                    карты / maps Google Maps, Yandex карты.
##
##   Repository URL:  https://fluxbb.org/resources/mods/?s=author&t=Visman&v=all&o=name
##                    https://fluxbb.qb7.ru/forum/viewtopic.php?id=3801
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


#
#---------[ 1. UPLOAD ]---------------------------------------------------------
#

folders js and swf to /

#
#---------[ 2. OPEN ]-----------------------------------------------------------
#

/include/parser.php

#
#---------[ 3. FIND ]-----------------------------------------------------------
#

		return '<a href="'.$full_url.'" rel="nofollow">'.$link.'</a>';
	}
}

#
#---------[ 4. BEFORE, ADD ]----------------------------------------------------
#

		global $mediajs;
		$mediajs = true;

#
#---------[ 5. SAVE ]-----------------------------------------------------------
#

/include/parser.php

#
#---------[ 6. OPEN ]-----------------------------------------------------------
#

footer.php

#
#---------[ 7. FIND ]-----------------------------------------------------------
#

// Display debug info (if enabled/defined)
if (defined('PUN_DEBUG'))

#
#---------[ 8. BEFORE, ADD ]----------------------------------------------------
#

if (!empty($mediajs))
	echo "\n".'<script type="text/javascript" src="js/media.min.js"></script>'."\n";

#
#---------[ 9. SAVE ]-----------------------------------------------------------
#

footer.php
