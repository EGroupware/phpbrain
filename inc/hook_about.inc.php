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

	function about_app($tpl,$handle)
	{
		$s = '<b>' . lang('phpBrain') . '</b><p>' . lang('written by:') . '&nbsp;Dave Hall - dave.hall at mbox.com.au';
		return $s;
	}
?>
