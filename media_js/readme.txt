##
##
##        Mod title:  Media.js
##
##      Mod version:  2.4.0
##  Works on FluxBB:  1.5.11
##     Release date:  2021-10-15
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (mio.visman@yandex.ru)
##
##      Description:  Мод на стороне пользователя заменяет ссылки на медиа-контент.
##                    Modification on the party of the user replaces links to a media content.
##
##                    Поддерживает / It is supported
##                    видео / video: YouTube, Vimeo, Rutube, Dailymotion, Sibnet, Facebook, Coub, Vine, VK, IGN, Gamespot, OK.ru, www.aparat.com, izlesene.com, vlipsy.com и прямые ссылки на / and direct urls for mp4, m4v, ogv, webm, webmv;
##                    аудио / audio: SoundCloud, ZippyShare, PromoDJ, Mixcloud, Hulkshare, audiomack.com, hearthis.at и прямые ссылки на / and direct urls for mp3, m4a, ogg, oga, webma, wav, flac;
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

folders /js/ to /

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
