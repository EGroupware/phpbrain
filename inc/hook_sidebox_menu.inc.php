<?php
  /**************************************************************************\
  * phpGroupWare - Calendar's Sidebox-Menu for idots-template                *
  * http://www.phpgroupware.org                                              *
  * Written by Pim Snel <pim@lingewoud.nl>                                   *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */
{

 /*
	This hookfile is for generating an app-specific side menu used in the idots
	template set.

	$menu_title speaks for itself
	$file is the array with link to app functions

	display_sidebox can be called as much as you like
 */

	$menu_title = $GLOBALS['phpgw_info']['apps'][$appname]['title'] . ' '. lang('Menu');
	$file = Array(
	array('','text'=>'Browse','link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=phpbrain.uikb.browse')),
	array('','text'=>'add_answer','link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=phpbrain.uikb.add')),
	array('','text'=>'add_question','link'=>$GLOBALS['phpgw']->link('/index.php','menuaction=phpbrain.uikb.unanswered')),
	);
	display_sidebox($appname,$menu_title,$file);

	if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
	{
		$menu_title = lang('Administration');
		$file = Array(
			'Site Configuration'=>$GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=phpbrain'),
			'Global Categories' =>$GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicategories.index&appname=phpbrain'),
			'Maintain Answers'=>$GLOBALS['phpgw']->link('/index.php','menuaction=phpbrain.uikb.maint_answer'),
			'Maintain Questions'=>$GLOBALS['phpgw']->link('/index.php','menuaction=phpbrain.uikb.maint_question'),
		);
		display_sidebox($appname,$menu_title,$file);
	}
}
?>
