<?php

	$show_tree = array(
		'all'			=> 'The present category and all subcategories under it',
		'only_cat'		=> 'The present category only'
	);

	create_select_box('Show articles belonging to:', 'show_tree', $show_tree, 'When navigating through categories, choose whether the list of articles shown corresponds only to the present category, or the present category and all categories under it.', 'all');

	$num_lines = array(
		"3"		=> "3",
		"5"		=> "5",
		"10"	=> "10",
		"15"	=> "15"
	);

	create_select_box('Maximum number of most popular articles, latest articles and unanswered questions to show in the main view:', 'num_lines', $num_lines, '', '3');

	$num_comments = array(
		"5"		=> "5",
		"10"	=> "10",
		"15"	=> "15",
		"20"	=> "20",
		"All"	=> "All"
	);

	create_select_box('Maximum number of comments to show:', 'num_comments', $num_comments, '', '5');
