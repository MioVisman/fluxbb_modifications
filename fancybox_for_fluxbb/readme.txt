##
##
##        Mod title:  Fancybox for FluxBB
##
##      Mod version:  1.1.0
##  Works on FluxBB:  1.4.2
##     Release date:  2011-01-14
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (visman@inbox.ru)
##
##      Description:  FancyBox is a tool for displaying images, html content and multi-media in a Mac-style "lightbox" that floats overtop of web page.
##                    This mod displays full images from a preview of sites funkyimg.com, fastpic.ru, radikal.ru, imageshack.us, savepic.(ru|org|net), jpegshare.net, ompldr.org; from references to images *.(jpg|jpeg|png|gif|bmp).
##
##                    1.1.0 
##                     Has added processing photobucket.com.
##                     Has added video processing www.youtube.com.
##                     The signature in post isn't processed.
##
##   Repository URL:  http://fluxbb.org/resources/mods/?s=author&t=Visman&v=all&o=name
##
##   Affected files:  header.php
##
##       Affects DB:  No
##
##            Notes:  All work is carried out on the party of the client (javascript + jQuery).
##                    Work examples http://forum.alltes.ru/fluxbb14test/viewtopic.php?id=24
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

all folders to /

#
#---------[ 2. OPEN ]---------------------------------------------------------
#

header.php

#
#---------[ 3. FIND ]---------------------------------------------------------
#

if (isset($page_head))
	echo implode("\n", $page_head)."\n";

#
#---------[ 4. BEFORE, ADD ]--------------------------------------------------
#

// Fancybox
if (!$pun_user['is_guest'] && in_array(basename($_SERVER['PHP_SELF']), array('viewtopic.php', 'search.php')))
{
	$page_head['jquery'] = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>';
	$page_head['fancyboxcss'] = '<link rel="stylesheet" type="text/css" href="style/imports/fancybox.css" />';
	$page_head['fancybox'] = '<script type="text/javascript" src="js/fancybox.js"></script>';
}

#
#---------[ 5. SAVE ]---------------------------------------------------------
#

header.php

