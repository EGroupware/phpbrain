<?php
	/**************************************************************************\
	* phpGroupWare - KnowledgeBase                                             *
	* http://www.phpgroupware.org                                              *
	* Written by Dave Hall [dave.hall at mbox.com.au]			                 *
	* ------------------------------------------------------------------------ *
	* Started off as a port of phpBrain - http://vrotvrot.com/phpBrain/		 *
	*  but quickly became a full rewrite										 *
	* ------------------------------------------------------------------------ *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	{
		$values = array
		(
			'Site Configuration' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=' . $appname),
			'Global Categories' => $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicategories.index&appname=' . $appname),
			'Maintain Answers' => $GLOBALS['phpgw']->link('/index.php','menuaction=phpbrain.uikb.maint_answer'),
//			'Maintain Questions' => $GLOBALS['phpgw']->link('/index.php','menuaction=phpbrain.uikb.maint_question'),
			'Reports - yet to be implemented' => $GLOBALS['phpgw']->link('/index.php','menuaction=phpbrain.uikb.index')
		);

		display_section($appname,$appname,$values);
	}
?>