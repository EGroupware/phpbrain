<?php
	/**************************************************************************\
	* phpGroupWare - KnowledgeBase                                             *
	* http://www.phpgroupware.org                                              *
	* Written by Dave Hall [skwashd AT phpgroupware.org]		           *
	* ------------------------------------------------------------------------ *
	* Started off as a port of phpBrain - http://vrotvrot.com/phpBrain/	   *
	*  but quickly became a full rewrite			                   *
	* ------------------------------------------------------------------------ *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

  /* $Id$ */

	function anon_user($config)
	{
		$sbox = createObject('phpgwapi.sbox2');
		return $sbox->getAccount('newsettings[anon_user]',$config['anon_user'], true);
	}
?>
