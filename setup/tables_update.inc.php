<?php
/**
 * EGroupware Knowledgebase - Setup
 *
 * Started off as a port of phpBrain - http://vrotvrot.com/phpBrain/ but quickly became a full rewrite
 *
 * @link http://www.egroupware.org
 * @author Alejandro Pedraza <alpeb(at)users.sourceforge.net>
 * @package phpbrain
 * @subpackage setup
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

function phpbrain_upgrade0_9_14_001()
{
	global $DEBUG;

	$db1 = $GLOBALS['egw_setup']->db;

	$GLOBALS['egw_setup']->oProc->CreateTable(
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

	$GLOBALS['egw_setup']->oProc->CreateTable(
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

	$GLOBALS['egw_setup']->oProc->CreateTable(
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
	if ($DEBUG) echo '<br>tables_update: new tables created';

	$GLOBALS['egw_setup']->oProc->RenameTable('phpgw_kb_faq', 'phpgw_kb_articles');
	$GLOBALS['egw_setup']->oProc->RenameColumn('phpgw_kb_articles', 'faq_id', 'art_id');
	$GLOBALS['egw_setup']->oProc->RenameColumn('phpgw_kb_articles', 'url', 'urls');
	$GLOBALS['egw_setup']->oProc->AlterColumn('phpgw_kb_articles', 'urls', array('type' => 'text', 'nullable' => False));
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_articles', 'q_id', array('type' => 'int', 'precision' => 8, 'nullable' => False));
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_articles', 'topic', array('type' => 'text', 'nullable' => False));
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_articles', 'created', array('type' => 'int', 'precision' => '4', 'nullable' => True));
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_articles', 'modified_user_id', array('type' => 'int', 'precision' => '4', 'nullable' => False));
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_articles', 'votes_1', array('type' => 'int', 'precision' => '4', 'nullable' => False));
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_articles', 'votes_2', array('type' => 'int', 'precision' => '4', 'nullable' => False));
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_articles', 'votes_3', array('type' => 'int', 'precision' => '4', 'nullable' => False));
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_articles', 'votes_4', array('type' => 'int', 'precision' => '4', 'nullable' => False));
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_articles', 'votes_5', array('type' => 'int', 'precision' => '4', 'nullable' => False));
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_articles', 'files', array('type' => 'text', 'nullable' => False));
	if ($DEBUG) echo '<br>tables_update: added columns to phpgw_kb_articles';

	$sql = "SELECT art_id, user_id, modified, urls, votes, total FROM phpgw_kb_articles";
	$GLOBALS['egw_setup']->oProc->query($sql, __LINE__, __FILE__);
	if ($DEBUG) echo '<br>tables_update: query on phpgw_kb_articles executed';
	while($GLOBALS['egw_setup']->oProc->next_record())
	{
		$art_id		= $GLOBALS['egw_setup']->oProc->f('art_id');
		$user_id	= $GLOBALS['egw_setup']->oProc->f('user_id');
		$keywords	= $GLOBALS['egw_setup']->oProc->f('keywords');
		$modified	= $GLOBALS['egw_setup']->oProc->f('modified');
		$urls 		= $GLOBALS['egw_setup']->oProc->f('urls');
		$votes		= $GLOBALS['egw_setup']->oProc->f('votes');
		$total		= $GLOBALS['egw_setup']->oProc->f('total');

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
			$average = -1;
		}
		else
		{
			$average = round($total / $votes);
		}
		$votes_str = array();
		for ($j=1; $j<=5; $j++)
		{
			if ($j == $average)
			{
				$votes_str[] = "votes_" . $j . "=$votes";
			}
			else
			{
				$votes_str[] = "votes_" . $j . "=0";
			}
		}
		$votes_str = implode(', ', $votes_str);
		$sql = "UPDATE phpgw_kb_articles SET created=$modified, modified_user_id=$user_id, $votes_str, urls='$new_urls', q_id='0', topic='', files=''  WHERE art_id=$art_id";
		$db1->query($sql, __LINE__, __FILE__);

		$sql = "INSERT INTO phpgw_kb_search (keyword, art_id, score) VALUES ('', $art_id, 1)";
		$db1->query($sql, __LINE__, __FILE__);
	}
	$new_table_def = array(
		'fd' => array(
			'art_id'			=> array('type' => 'auto','nullable' => False),
			'title'				=> array('type' => 'text','nullable' => False),
			'text'				=> array('type' => 'text','nullable' => False),
			'cat_id'			=> array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'published' 		=> array('type' => 'int','precision' => '2','nullable' => False,'default' => '0'),
			'keywords'			=> array('type' => 'text','nullable' => False),
			'user_id'			=> array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'views'				=> array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'modified'			=> array('type' => 'int','precision' => '4','nullable' => True),
			'urls'				=> array('type' => 'text', 'nullable' => False),
			'votes'				=> array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'total'				=> array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'q_id'				=> array('type' => 'int', 'precision' => 8, 'nullable' => False),
			'topic'				=> array('type' => 'text', 'nullable' => False),
			'created'			=> array('type' => 'int','precision' => '4','nullable' => True),
			'modified_user_id'	=> array('type' => 'int','precision' => '4','nullable' => False),
			'votes_1'			=> array('type' => 'int','precision' => '4','nullable' => False),
			'votes_2'			=> array('type' => 'int','precision' => '4','nullable' => False),
			'votes_3'			=> array('type' => 'int','precision' => '4','nullable' => False),
			'votes_4'			=> array('type' => 'int','precision' => '4','nullable' => False),
			'votes_5'			=> array('type' => 'int','precision' => '4','nullable' => False),
			'files'				=> array('type' => 'text', 'nullable' => False)
		),
		'pk' => array('art_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	);
	$GLOBALS['egw_setup']->oProc->DropColumn('phpgw_kb_articles', $new_table_def, 'is_faq');
	if ($DEBUG) echo '<br>tables_update: dropped column is_faq in phpgw_kb_articles';
	unset($new_table_def['fd']['votes']);
	$GLOBALS['egw_setup']->oProc->DropColumn('phpgw_kb_articles', $new_table_def, 'votes');
	if ($DEBUG) echo '<br>tables_update: dropped column votes in phpgw_kb_articles';
	unset($new_table_def['fd']['total']);
	$GLOBALS['egw_setup']->oProc->DropColumn('phpgw_kb_articles', $new_table_def, 'total');
	if ($DEBUG) echo '<br>tables_update: dropped column total in phpgw_kb_articles';

	$GLOBALS['egw_setup']->oProc->RenameColumn('phpgw_kb_comment', 'faq_id', 'art_id');
	if ($DEBUG) echo '<br>tables_update: renamed column faq_id to art_id in phpgw_kb_articles';
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_comment', 'published', array('type' => 'int', 'precision' => '2', 'nullable' => False));
	if ($DEBUG) echo '<br>tables_update: added column published in phpgw_kb_articles';
	$sql = "UPDATE phpgw_kb_comment SET published=1";
	$db1->query($sql, __LINE__, __FILE__);


	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_questions', 'user_id', array('type' => 'int', 'precision' => '4', 'nullable' => False));
	if ($DEBUG) echo '<br>tables_update: added column user_id in phpgw_kb_questions';
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_questions', 'details', array('type' => 'text', 'nullable' => False));
	if ($DEBUG) echo '<br>tables_update: added column details in phpgw_kb_questions';
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_questions', 'cat_id', array('type' => 'int', 'precision' => '4', 'nullable' => False, 'default' => '0'));
	if ($DEBUG) echo '<br>tables_update: added column cat_id in phpgw_kb_questions';
	$GLOBALS['egw_setup']->oProc->AddColumn('phpgw_kb_questions', 'creation', array('type' => 'int', 'precision' => '4', 'nullable' => True));
	if ($DEBUG) echo '<br>tables_update: added column creation in phpgw_kb_questions';

	$sql = "SELECT question_id, pending FROM phpgw_kb_questions";
	$GLOBALS['egw_setup']->oProc->query($sql, __LINE__, __FILE__);
	while($GLOBALS['egw_setup']->oProc->next_record())
	{
		$question_id	= $GLOBALS['egw_setup']->oProc->f('question_id');
		$published		= $GLOBALS['egw_setup']->oProc->f('pending');
		$published = $published? 0 : 1;
		$sql = "UPDATE phpgw_kb_questions SET pending=$published, user_id='0', details='', cat_id='0', creation='". time() ."' WHERE question_id=$question_id";
		$db1->query($sql, __LINE__, __FILE__);
	}
	$GLOBALS['egw_setup']->oProc->RenameColumn('phpgw_kb_questions', 'question', 'summary');
	if ($DEBUG) echo '<br>tables_update: renamed column question to summary in phpgw_kb_questions';
	$GLOBALS['egw_setup']->oProc->RenameColumn('phpgw_kb_questions', 'pending', 'published');
	if ($DEBUG) echo '<br>tables_update: renamed column pending to published in phpgw_kb_questions';

	$GLOBALS['setup_info']['phpbrain']['currentver'] = '1.0RC5';
	return $GLOBALS['setup_info']['phpbrain']['currentver'];
}


function phpbrain_upgrade1_0RC5()
{
	$GLOBALS['setup_info']['phpbrain']['currentver'] = '1.0.0';
	return $GLOBALS['setup_info']['phpbrain']['currentver'];
}


function phpbrain_upgrade1_0_0()
{
	$GLOBALS['egw_setup']->oProc->AlterColumn('phpgw_kb_search', 'keyword', array('type' => 'varchar', 'precision' => '30', 'nullable' => False));

	$GLOBALS['setup_info']['phpbrain']['currentver'] = '1.0.1';
	return $GLOBALS['setup_info']['phpbrain']['currentver'];
}


// this upgrade changes \n from older version's plain text to <br>
function phpbrain_upgrade1_0_1()
{
	$db1 = $GLOBALS['egw_setup']->db;

	$sql = "SELECT art_id, text FROM phpgw_kb_articles";
	$GLOBALS['egw_setup']->oProc->query($sql, __LINE__, __FILE__);
	while($GLOBALS['egw_setup']->oProc->next_record())
	{
		$art_id = $GLOBALS['egw_setup']->oProc->f('art_id');
		$text = $GLOBALS['egw_setup']->oProc->f('text');

		if (!preg_match('/'."<[^<]+>.+<[^\\/]*\\/.+>".'/', $text))
		{
			// text doesn't have html -> proceed to replace all \n by <br>
			$new_text = preg_replace('/'."\n".'/', "<br />", $text);

			$sql ="UPDATE phpgw_kb_articles SET text='$new_text' WHERE art_id = $art_id";
			$db1->query($sql, __LINE__, __FILE__);
		}
	}

	$GLOBALS['setup_info']['phpbrain']['currentver'] = '1.0.2';
	return $GLOBALS['setup_info']['phpbrain']['currentver'];
}


function phpbrain_upgrade1_0_2()
{
	$GLOBALS['egw_setup']->oProc->CreateTable('phpgw_kb_files',array(
		'fd' => array(
			'art_id' => array('type' => 'int','precision' => '4'),
			'art_file' => array('type' => 'varchar','precision' => '255'),
			'art_file_comments' => array('type' => 'varchar','precision' => '255'),
		),
		'pk' => array('art_id','art_file'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	));

	$db2 = $GLOBALS['egw_setup']->db;
	$GLOBALS['egw_setup']->oProc->query("SELECT art_id,files FROM phpgw_kb_articles WHERE files != ''",__LINE__,__FILE__);
	while ($GLOBALS['egw_setup']->oProc->next_record())
	{
		$art_id = $GLOBALS['egw_setup']->oProc->f('art_id');
		$files = unserialize($GLOBALS['egw_setup']->oProc->f('files'));
		if (is_array($files))
		{
			foreach($files as $file)
			{
				$db2->insert('phpgw_kb_files',array(
					'art_id' => $art_id,
					'art_file'   => $file['file'],
					'art_file_comments'	=> $file['comment'],
				),false,__LINE__,__FILE__,'phpbrain');
			}
		}
	}

	$GLOBALS['setup_info']['phpbrain']['currentver'] = '1.0.3';
	return $GLOBALS['setup_info']['phpbrain']['currentver'];
}


function phpbrain_upgrade1_0_3()
{
	$GLOBALS['egw_setup']->oProc->CreateTable('phpgw_kb_urls',array(
		'fd' => array(
			'art_id' => array('type' => 'int','precision' => '4'),
			'art_url' => array('type' => 'varchar','precision' => '255'),
			'art_url_title' => array('type' => 'varchar','precision' => '255')
		),
		'pk' => array('art_id','art_url'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	));

	$db2 = $GLOBALS['egw_setup']->db;
	$GLOBALS['egw_setup']->oProc->query("SELECT art_id,urls FROM phpgw_kb_articles WHERE urls != ''",__LINE__,__FILE__);
	while ($GLOBALS['egw_setup']->oProc->next_record())
	{
		$art_id = $GLOBALS['egw_setup']->oProc->f('art_id');
		$urls = unserialize($GLOBALS['egw_setup']->oProc->f('urls'));
		if (is_array($files))
		{
			foreach($urls as $url)
			{
				$db2->insert('phpgw_kb_files',array(
					'art_id' => $art_id,
					'art_url'    => $url['link'],
					'art_url_title'	=> $url['title'],
				),false,__LINE__,__FILE__,'phpbrain');
			}
		}
	}

	$GLOBALS['setup_info']['phpbrain']['currentver'] = '1.0.4';
	return $GLOBALS['setup_info']['phpbrain']['currentver'];
}


function phpbrain_upgrade1_0_4()
{
	$GLOBALS['egw_setup']->oProc->DropColumn('phpgw_kb_articles',array(
		'fd' => array(
			'art_id' => array('type' => 'auto','nullable' => False),
			'q_id' => array('type' => 'int','precision' => '8','nullable' => False),
			'title' => array('type' => 'text','nullable' => False),
			'topic' => array('type' => 'text','nullable' => False),
			'text' => array('type' => 'text','nullable' => False),
			'cat_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'published' => array('type' => 'int','precision' => '2','nullable' => False,'default' => '0'),
			'user_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'views' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'created' => array('type' => 'int','precision' => '4','nullable' => True),
			'modified' => array('type' => 'int','precision' => '4','nullable' => True),
			'modified_user_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'files' => array('type' => 'text','nullable' => False),
			'urls' => array('type' => 'text','nullable' => False),
			'votes_1' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_2' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_3' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_4' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_5' => array('type' => 'int','precision' => '4','nullable' => False)
		),
		'pk' => array('art_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),'keywords');
	$GLOBALS['egw_setup']->oProc->DropColumn('phpgw_kb_articles',array(
		'fd' => array(
			'art_id' => array('type' => 'auto','nullable' => False),
			'q_id' => array('type' => 'int','precision' => '8','nullable' => False),
			'title' => array('type' => 'text','nullable' => False),
			'topic' => array('type' => 'text','nullable' => False),
			'text' => array('type' => 'text','nullable' => False),
			'cat_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'published' => array('type' => 'int','precision' => '2','nullable' => False,'default' => '0'),
			'user_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'views' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'created' => array('type' => 'int','precision' => '4','nullable' => True),
			'modified' => array('type' => 'int','precision' => '4','nullable' => True),
			'modified_user_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'urls' => array('type' => 'text','nullable' => False),
			'votes_1' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_2' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_3' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_4' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_5' => array('type' => 'int','precision' => '4','nullable' => False)
		),
		'pk' => array('art_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),'files');
	$GLOBALS['egw_setup']->oProc->DropColumn('phpgw_kb_articles',array(
		'fd' => array(
			'art_id' => array('type' => 'auto','nullable' => False),
			'q_id' => array('type' => 'int','precision' => '8','nullable' => False),
			'title' => array('type' => 'text','nullable' => False),
			'topic' => array('type' => 'text','nullable' => False),
			'text' => array('type' => 'text','nullable' => False),
			'cat_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'published' => array('type' => 'int','precision' => '2','nullable' => False,'default' => '0'),
			'user_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'views' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
			'created' => array('type' => 'int','precision' => '4','nullable' => True),
			'modified' => array('type' => 'int','precision' => '4','nullable' => True),
			'modified_user_id' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_1' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_2' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_3' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_4' => array('type' => 'int','precision' => '4','nullable' => False),
			'votes_5' => array('type' => 'int','precision' => '4','nullable' => False)
		),
		'pk' => array('art_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),'urls');
	$GLOBALS['egw_setup']->oProc->AlterColumn('phpgw_kb_articles','title',array(
		'type' => 'varchar',
		'precision' => '255',
		'nullable' => False
	));
	$GLOBALS['egw_setup']->oProc->AlterColumn('phpgw_kb_articles','topic',array(
		'type' => 'varchar',
		'precision' => '255',
		'nullable' => False
	));

	return $GLOBALS['setup_info']['phpbrain']['currentver'] = '1.0.5';
}


function phpbrain_upgrade1_0_5()
{
	$GLOBALS['egw_setup']->oProc->AlterColumn('phpgw_kb_articles','text',array('type' => 'longtext'));

	return $GLOBALS['setup_info']['phpbrain']['currentver'] = '1.5.001';
}


function phpbrain_upgrade1_5_001()
{
	// Firsts phpgw -> egw prefix
	foreach(array('phpgw_kb_articles','phpgw_kb_comment','phpgw_kb_questions','phpgw_kb_ratings','phpgw_kb_related_art','phpgw_kb_search','phpgw_kb_files','phpgw_kb_urls') as $table)
	{
		$GLOBALS['egw_setup']->oProc->RenameTable($table,str_replace('phpgw_kb','egw_kb',$table));
	}

	// Second rename egw_kb_comment.comment column (Oracle reserved work)
	$GLOBALS['egw_setup']->oProc->RenameColumn('egw_kb_comment', 'comment', 'kb_comment');

	return $GLOBALS['setup_info']['phpbrain']['currentver'] = '1.5.002';
}


function phpbrain_upgrade1_5_002()
{
	// make kb files regular attachments
	$current_is_root = egw_vfs::$is_root; egw_vfs::$is_root = true;
	$current_user = egw_vfs::$user; egw_vfs::$user = 0;

	$sqlfs = new sqlfs_stream_wrapper();
	$ok = $sqlfs->mkdir('/apps/phpbrain',0,STREAM_MKDIR_RECURSIVE);
	foreach($GLOBALS['egw_setup']->db->query("SELECT * FROM egw_kb_files ORDER BY art_id",__LINE__,__FILE__,0,-1,false,egw_db::FETCH_ASSOC) as $file)
	{
		if ($art_id != $file['art_id'])
		{
			$ok = $sqlfs->mkdir('/apps/phpbrain/'.$file['art_id'],0,0);
			$ok = $sqlfs->stream_metadata('/apps/phpbrain/'.$file['art_id'],STREAM_META_OWNER, 0);	// no default access
			$art_id = $file['art_id'];
		}
		list(,$fname) = explode('-',$file['art_file'],2);
		$sqlfs->rename('/kb/'.$file['art_file'],'/apps/phpbrain/'.$file['art_id'].'/'.$fname);
	}
	$ok = $sqlfs->rmdir('/kb',0);
	egw_vfs::$is_root = $current_is_root;
	egw_vfs::$user = $current_user;

	// drop not longer used table
	$GLOBALS['egw_setup']->oProc->DropTable('egw_kb_files');

	return $GLOBALS['setup_info']['phpbrain']['currentver'] = '1.5.003';
}


function phpbrain_upgrade1_5_003()
{
	return $GLOBALS['setup_info']['phpbrain']['currentver'] = '1.6';
}


function phpbrain_upgrade1_6()
{
	return $GLOBALS['setup_info']['phpbrain']['currentver'] = '1.8';
}


function phpbrain_upgrade1_8()
{
	$GLOBALS['egw_setup']->oProc->CreateIndex('egw_kb_articles','cat_id');
	$GLOBALS['egw_setup']->oProc->CreateIndex('egw_kb_articles',array('art_id','cat_id'));

	return $GLOBALS['setup_info']['phpbrain']['currentver'] = '1.8.001';
}


function phpbrain_upgrade1_8_001()
{
	return $GLOBALS['setup_info']['phpbrain']['currentver'] = '14.1';
}