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
		'phpgw_kb_articles' => array(
			'fd' => array(
				'art_id'			=> array('type' => 'auto','nullable' => False),
				'q_id'				=> array('type' => 'int', 'precision' => 8, 'nullable' => False),
				'title'				=> array('type' => 'text','nullable' => False),
				'topic'				=> array('type' => 'text', 'nullable' => False),
				'text'				=> array('type' => 'text','nullable' => False),
				'cat_id'			=> array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'published' 		=> array('type' => 'int','precision' => '2','nullable' => False,'default' => '0'),
				'keywords'			=> array('type' => 'text','nullable' => False),
				'user_id'			=> array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'views'				=> array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'created'			=> array('type' => 'int','precision' => '4','nullable' => True),
				'modified'			=> array('type' => 'int','precision' => '4','nullable' => True),
				'modified_user_id'	=> array('type' => 'int','precision' => '4','nullable' => False),
				'files'				=> array('type' => 'text', 'nullable' => False),
				'urls'				=> array('type' => 'text', 'nullable' => False),
				'votes_1'			=> array('type' => 'int','precision' => '4','nullable' => False),
				'votes_2'			=> array('type' => 'int','precision' => '4','nullable' => False),
				'votes_3'			=> array('type' => 'int','precision' => '4','nullable' => False),
				'votes_4'			=> array('type' => 'int','precision' => '4','nullable' => False),
				'votes_5'			=> array('type' => 'int','precision' => '4','nullable' => False)
			),
			'pk' => array('art_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

		'phpgw_kb_comment' => array(
			'fd' => array(
				'comment_id'	=> array('type' => 'auto','nullable' => False),
				'user_id'		=> array('type' => 'int','precision' => '4','nullable' => False),
				'comment'		=> array('type' => 'text','nullable' => False),
				'entered'		=> array('type' => 'int','precision' => '4','nullable' => True),
				'art_id'		=> array('type' => 'int','precision' => '4','nullable' => False),
				'published'		=> array('type' => 'int','precision' => '2','nullable' => False)
			),
			'pk' => array('comment_id'),
			'fk' => array(),
			'ix' => array('art_id'),
			'uc' => array()
		),

		'phpgw_kb_questions' => array(
			'fd' => array(
				'question_id'	=> array('type' => 'auto','nullable' => False),
				'user_id'		=> array('type' => 'int','precision' => '4', 'nullable' => False),
				'summary'		=> array('type' => 'text','nullable' => False),
				'details'		=> array('type' => 'text','nullable' => False),
				'cat_id'		=> array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'creation'		=> array('type' => 'int','precision' => '4','nullable' => True),
				'published'		=> array('type' => 'int','precision' => '2','nullable' => False)
			),
			'pk' => array('question_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

		'phpgw_kb_ratings' => array(
			'fd' => array(
				'user_id'	=> array('type' => 'int','precision' => '4', 'nullable' => False),
				'art_id'	=> array('type' => 'int','precision' => '4','nullable' => False)
			),
			'pk' => array('user_id', 'art_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

		'phpgw_kb_related_art' => array(
			'fd' => array(
				'art_id'			=> array('type' => 'int','precision' => '4','nullable' => False),
				'related_art_id'	=> array('type' => 'int','precision' => '4','nullable' => False)
			),
			'pk' => array('art_id', 'related_art_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),

		'phpgw_kb_search' => array(
			'fd' => array(
				'keyword'	=> array('type' => 'varchar', 'precision' => '10','nullable' => False),
				'art_id'	=> array('type' => 'int','precision' => '4','nullable' => False),
				'score'		=> array('type' => 'int','precision' => '8','nullable' => False)
			),
			'pk' => array('keyword', 'art_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
