<?php
	$test[] = '0.9.14.001';
	function phpbrain_upgrade0_9_14_001()
	{
		$db1 = $GLOBALS['phpgw_setup']->db;

		$GLOBALS['phpgw_setup']->oProc->CreateTable(
			'phpgw_kb_ratings', array(
				'fd' => array(
					'user_id'	=> array('type' => 'int','precision' => '4', 'nullable' => False),
					'art_id'	=> array('type' => 'int','precision' => '4','nullable' => False)
				),
				'pk' => array('user_id', 'art_id'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
			)
		);

		$GLOBALS['phpgw_setup']->oProc->CreateTable(
			'phpgw_kb_related_art', array(
				'fd' => array(
					'art_id'			=> array('type' => 'int','precision' => '4','nullable' => False),
					'related_art_id'	=> array('type' => 'int','precision' => '4','nullable' => False)
				),
				'pk' => array('art_id', 'related_art_id'),
				'fk' => array(),
				'ix' => array(),
				'uc' => array()
			)
		);

		$GLOBALS['phpgw_setup']->oProc->CreateTable(
			'phpgw_kb_search', array(
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

		$GLOBALS['phpgw_setup']->oProc->RenameTable('phpgw_kb_faq', 'phpgw_kb_articles');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_kb_articles', 'faq_id', 'art_id');
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_kb_articles', 'url', 'urls');
		$GLOBALS['phpgw_setup']->oProc->AlterColumn('phpgw_kb_articles', 'urls', array('type' => 'text', 'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_articles', 'q_id', array('type' => 'int', 'precision' => 8, 'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_articles', 'topic', array('type' => 'text', 'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_articles', 'created', array('type' => 'int', 'precision' => '4', 'nullable' => True));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_articles', 'modified_user_id', array('type' => 'int', 'precision' => '4', 'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_articles', 'votes_1', array('type' => 'int', 'precision' => '4', 'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_articles', 'votes_2', array('type' => 'int', 'precision' => '4', 'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_articles', 'votes_3', array('type' => 'int', 'precision' => '4', 'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_articles', 'votes_4', array('type' => 'int', 'precision' => '4', 'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_articles', 'votes_5', array('type' => 'int', 'precision' => '4', 'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_articles', 'files', array('type' => 'text', 'nullable' => False));

		$sql = "SELECT art_id, user_id, modified, urls, votes, total FROM phpgw_kb_articles";
		$GLOBALS['phpgw_setup']->oProc->query($sql, __LINE__, __FILE__);
		while($GLOBALS['phpgw_setup']->oProc->next_record())
		{
			$art_id		= $GLOBALS['phpgw_setup']->oProc->f('art_id');
			$user_id	= $GLOBALS['phpgw_setup']->oProc->f('user_id');
			$keywords	= $GLOBALS['phpgw_setup']->oProc->f('keywords');
			$modified	= $GLOBALS['phpgw_setup']->oProc->f('modified');
			$urls 		= $GLOBALS['phpgw_setup']->oProc->f('urls');
			$votes		= $GLOBALS['phpgw_setup']->oProc->f('votes');
			$total		= $GLOBALS['phpgw_setup']->oProc->f('total');

			if ($urls)
			{
				$new_urls = serialize(array(0=>array('link' => $urls, 'title' => '')));
			}
			else
			{
				$new_urls = '';
			}
			if (!$votes)
			{
				$sql = "UPDATE phpgw_kb_articles SET created=$modified, modified_user_id=$user_id, urls='$new_urls' WHERE art_id=$art_id";
			}
			else
			{
				$average = round($total / $votes);
				$sql = "UPDATE phpgw_kb_articles SET created=$modified, modified_user_id=$user_id, votes_". $average ."=$votes, urls='$new_urls' WHERE art_id=$art_id";
			}
			$db1->query($sql, __LINE__, __FILE__);

			$sql = "INSERT INTO phpgw_kb_search (keyword, art_id, score) VALUES ('', $art_id, 1)";
			$db1->query($sql, __LINE__, __FILE__);
		}
		$new_table_def = array(
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
				'votes'				=> array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'total'				=> array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
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
		);
		$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_kb_articles', $new_table_def, 'is_faq');
		unset($newdef['fd']['votes']);
		$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_kb_articles', $new_table_def, 'votes');
		unset($newdef['fd']['total']);
		$GLOBALS['phpgw_setup']->oProc->DropColumn('phpgw_kb_articles', $new_table_def, 'total');

		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_kb_comment', 'faq_id', 'art_id');
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_comment', 'published', array('type' => 'int', 'precision' => '2', 'nullable' => False));
		$sql = "UPDATE phpgw_kb_comment SET published=1";
		$db1->query($sql, __LINE__, __FILE__);


		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_questions', 'user_id', array('type' => 'int', 'precision' => '4', 'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_kb_questions', 'question', 'summary');
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_questions', 'details', array('type' => 'text', 'nullable' => False));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_questions', 'cat_id', array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'));
		$GLOBALS['phpgw_setup']->oProc->AddColumn('phpgw_kb_questions', 'creation', array('type' => 'int', 'precision' => '4', 'nullable' => True));
		$GLOBALS['phpgw_setup']->oProc->RenameColumn('phpgw_kb_questions', 'pending', 'published');

		$sql = "SELECT question_id, published FROM phpgw_kb_questions";
		$GLOBALS['phpgw_setup']->oProc->query($sql, __LINE__, __FILE__);
		while($GLOBALS['phpgw_setup']->oProc->next_record())
		{
			$question_id	= $GLOBALS['phpgw_setup']->oProc->f('question_id');
			$published		= $GLOBALS['phpgw_setup']->oProc->f('published');
			$published = $published? 0 : 1;
			$sql = "UPDATE phpgw_kb_questions SET published=$published WHERE question_id=$question_id";
			$db1->query($sql, __LINE__, __FILE__);
		}

		$GLOBALS['setup_info']['phpbrain']['currentver'] = '1.0RC5';
		return $GLOBALS['setup_info']['phpbrain']['currentver'];
	}
