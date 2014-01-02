<?php

/**
 * @name	SolveTopic
 * @license	BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * @version 1.0
 *
 */

// If we have found SSI.php and we are outside of ElkArte, then we are running standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('ELK'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('ELK')) // If we are outside ElkArte and can't find SSI.php, then throw an error
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as Elkarte\'s SSI.php.');

$db = database();
$dbtbl = db_table();

// Add a column to the topics table since this is for topics being solved
$dbtbl->db_add_column('{db_prefix}topics', array('name' => 'solved', 'type' => 'tinyint', 'size' => 3, 'default' => 0, 'unsigned' => true));
$installed = $dbtbl->db_list_columns('{db_prefix}topics');

if (ELK === 'SSI')
{
	if (in_array('solved', $installed))
		echo 'Congratulations! You have successfully made the Topic Solved Addon database edits!';
	else
		echo 'Database edits failed!';
}