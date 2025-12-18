<?php
/**************************************************************************\
* eGroupWare - KnowledgeBase                                               *
* http://www.egroupware.org                                              *
* Written by Dave Hall [dave.hall at mbox.com.au]			               *
* ------------------------------------------------------------------------ *
* Started off as a port of phpBrain - http://vrotvrot.com/phpBrain/		   *
* but quickly became a full rewrite                                        *
* ------------------------------------------------------------------------ *
* This program is free software; you can redistribute it and/or modify it  *
* under the terms of the GNU General Public License as published by the    *
* Free Software Foundation; either version 2 of the License, or (at your   *
* option) any later version.                                               *
\**************************************************************************/

{
	$file = array
	(
		'Site Configuration'		=> egw::link('/index.php','menuaction=admin.admin_config.index&appname=' . $appname.'&ajax=true'),
		'Global Categories'			=> egw::link('/index.php','menuaction=admin.admin_categories.index&appname=phpbrain&ajax=true')
	);
	display_section($appname,$file);
}