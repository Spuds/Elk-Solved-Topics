<?php

/**
 * Full removal of the SolveTopic Addon, cleans up its DB entries
 *
 * @name      ElkArte Forum
 * @copyright ElkArte Forum contributors
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * This software is a derived product, based on:
 *
 * TopicSolved 1.1.1
 * Copyright 2006-2008 Blue Dream (http://www.simpleportal.net)
 *
 * @version 1.0
 *
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('ELK'))
{
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('ELK'))
{
	die('<b>Error:</b> Cannot uninstall - please verify you put this file in the same place as ElkArte\'s SSI.php.');
}

$db = database();

// Clean up the settings table
global $modSettings;
$remove = array('enable_solved');
foreach ($modSettings as $variable => $value)
{
	if (strpos($variable, 'topicsolved') === 0)
	{
		$remove[] = $variable;
	}
}

$db->query('', '
	DELETE FROM {db_prefix}settings 
	WHERE variable IN ({array_string:vars})',
	array(
		'vars' => $remove
	)
);

// Clean up permissions
$db->query('', '
	DELETE FROM {db_prefix}board_permissions
	WHERE permission LIKE {string:perm}',
	array(
		'perm' => 'solve_topic%',
	)
);

// Clean up the moderation log
$db->query('', '
	DELETE FROM {db_prefix}log_actions
	WHERE id_log = {int:topic_solved_log}',
	array(
		'topic_solved_log' => 4,
	)
);

// Reset topic solved icons to their normal state.
$db->query('', '
	UPDATE {db_prefix}messages
	SET icon = {string:xx}
	WHERE icon = {string:solved}',
	array(
		'xx' => 'xx',
		'solved' => 'solved'
	)
);

if (ELK === 'SSI')
{
	if (in_array('solved', $installed))
	{
		echo 'All topic-solved activity has been removed!';
	}
	else
	{
		echo 'Database edits failed!';
	}
}