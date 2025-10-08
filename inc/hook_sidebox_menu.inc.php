<?php
/**************************************************************************\
* eGroupWare - Knowledge Base                                              *
* http://www.egroupware.org                                                *
* -----------------------------------------------                          *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */
{
	$file=Array(
		'Main View'					=> $GLOBALS['egw']->link('/index.php','menuaction=phpbrain.uikb.index'),
		'New Article'				=> $GLOBALS['egw']->link('/index.php','menuaction=phpbrain.uikb.edit_article'),
		'Add Question'				=> $GLOBALS['egw']->link('/index.php','menuaction=phpbrain.uikb.add_question'),
		'Maintain Articles'			=> $GLOBALS['egw']->link('/index.php','menuaction=phpbrain.uikb.maintain_articles&ajax=true'),
		'Maintain Questions'		=> $GLOBALS['egw']->link('/index.php','menuaction=phpbrain.uikb.maintain_questions&ajax=true')
	);
	foreach($file as $text => $link)
	{
		$GLOBALS['egw']->framework->sidebox($appname, lang($text), [['link' => $link]]);
	}

	if($GLOBALS['egw_info']['user']['apps']['admin'] && $args['location'] == "admin")
	{
		$menu_title = 'Administration';
		$file = Array(
			'Site Configuration'		=> egw::link('/index.php','menuaction=admin.admin_config.index&appname=' . $appname.'&ajax=true'),
			'Global Categories'			=> egw::link('/index.php','menuaction=admin.admin_categories.index&appname=phpbrain')
		);
		$GLOBALS['egw']->framework->sidebox($appname,$menu_title,$file);
	}
}