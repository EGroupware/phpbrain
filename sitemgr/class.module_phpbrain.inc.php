<?php
	class module_phpbrain extends Module
	{
		var $ui;
		function module_phpbrain()
		{
			$this->arguments = array(
				'post_questions' => array(
					'type' => 'checkbox', 
					'label' => lang('Allow posting of questions')
				)
			);
			$this->post = array('post_questions' => array('type' => 'checkbox'));
			$this->title = lang('Knowledge Base');
			$this->description = lang('Enterprise Knowledge articles repository');
		}

		function get_content(&$arguments, $properties)
		{
			$GLOBALS['egw_info']['flags']['currentapp'] = 'phpbrain';

			if (isset($_GET['menuaction']))
			{
				list($app,$class,$method) = explode('.',@$_GET['menuaction']);
			}
			else
			{
				$class = 'uikb';
				$method = 'index';
			}
			$app = 'phpbrain';
			$GLOBALS[$class] =& CreateObject(sprintf('%s.%s',$app,$class), $this->find_template_dir(), $this->link(), $arguments);
			if((is_array($GLOBALS[$class]->public_functions) && $GLOBALS[$class]->public_functions[$method]))
			{
				return ExecMethod($app .'.'. $class.'.'.$method);
			}
		}
	}
?>
