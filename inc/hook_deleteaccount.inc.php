<?php
	/* $Id$ */

	$bokb = CreateObject('phpbrain.bokb');

	if((int)$_POST['new_owner'] == 0)
	{
		$bokb->delete_owner_articles((int)$_POST['account_id']);
	}
	else
	{
		$bokb->change_articles_owner((int)$_POST['account_id'],(int)$_POST['new_owner']);
	}
?>
