<?php
	/**************************************************************************\
	* phpGroupWare - Addressbook                                               *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Basic information about this app */
	$setup_info['phpbrain']['name']      = 'phpbrain';
	$setup_info['phpbrain']['title']     = 'Knowledge Base';
	$setup_info['phpbrain']['version']   = '0.9.14.001';
	$setup_info['phpbrain']['app_order'] = 25;
	$setup_info['phpbrain']['enable']    = 1;

	$setup_info['phpbrain']['author'] = 'Dave Hall';
	$setup_info['phpbrain']['note']   = 'A knowledge base for storing and searching for FAQs and Tutorials';
	$setup_info['phpbrain']['license']  = 'GPL';
	$setup_info['phpbrain']['description'] =
		'Searchable Knowledge Base.';
	$setup_info['phpbrain']['maintainer'] = 'Dave Hall';
	$setup_info['phpbrain']['maintainer_email'] = 'dave.hall at mbox.com.au';

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['phpbrain']['hooks'][] = 'about';
	$setup_info['phpbrain']['hooks'][] = 'admin';
	//$setup_info['phpbrain']['hooks'][] = 'add_def_pref';
	$setup_info['phpbrain']['hooks'][] = 'config';
	//$setup_info['phpbrain']['hooks'][] = 'config_validate';
	//$setup_info['phpbrain']['hooks'][] = 'home';
	$setup_info['phpbrain']['hooks'][] = 'manual';
	//$setup_info['phpbrain']['hooks'][] = 'addaccount';
	//$setup_info['phpbrain']['hooks'][] = 'editaccount';
	//$setup_info['phpbrain']['hooks'][] = 'deleteaccount';
	//$setup_info['phpbrain']['hooks'][] = 'notifywindow';
	//$setup_info['phpbrain']['hooks'][] = 'preferences';

	/* Dependencies for this app to work */
	$setup_info['phpbrain']['depends'][] = array(
		 'appname' => 'phpgwapi',
		 'versions' => Array('0.9.13', '0.9.14', '0.9.15')
	);
?>
