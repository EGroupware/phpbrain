<?php
  /**************************************************************************\
  * phpGroupWare - Setup                                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */


	$phpgw_baseline = array(
		'phpgw_kb_faq' => array(
			'fd' => array(
				'faq_id' => array('type' => 'auto','nullable' => False),
				'title' => array('type' => 'text','nullable' => False),
				'text' => array('type' => 'text','nullable' => False),
				'cat_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'published' => array('type' => 'int','precision' => '2','nullable' => False,'default' => '0'),
				'keywords' => array('type' => 'text','nullable' => False),
				'user_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'views' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'modified' => array('type' => 'int','precision' => '4','nullable' => True),
				'is_faq' => array('type' => 'int','precision' => '2','nullable' => False,'default' => '1'),
				'url' => array('type' => 'varchar','precision' => '128','nullable' => False),
				'votes' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'total' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0')
			),
			'pk' => array('faq_id'),
			'fk' => array(),
			'ix' => array('title','text','cat_id','published','keywords','is_faq'),
			'uc' => array()
		)
	);
