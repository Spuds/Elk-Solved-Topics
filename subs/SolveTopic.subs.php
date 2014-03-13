<?php

/**
 * @name      SolveTopic
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

if (!defined('ELK'))
	die('No access...');

/**
 * Fetches the topic owner, current solved status, and the id of the first message in the topic
 *
 * @param int $topic
 */
function getSolveTopicDetails($topic)
{
	$db = database();

	// Get the topic owner.
	$request = $db->query('', '
		SELECT id_member_started, solved, id_first_msg
		FROM {db_prefix}topics
		WHERE id_topic = {int:current_topic}
		LIMIT 1',
		array(
			'current_topic' => $topic,
		)
	);
	$details = $db->fetch_row($request);
	$db->free_result($request);

	return $details;
}

/**
 * Change the status of a topic, solved or not
 *
 * @param int $topic id to the topic we are working on
 * @param int $firstmsg id of the first message so we can set the topic icon
 * @param int $solved 0 is solved, 1 is unsolved
 */
function markSolveTopic($topic, $firstmsg, $solved)
{
	$db = database();

	// Mark the topic solved or unsolved in the database.
	$db->query('', '
		UPDATE {db_prefix}topics
		SET solved = {int:solved_status}
		WHERE id_topic = {int:current_topic}',
		array(
			'current_topic' => $topic,
			'solved_status' => empty($solved) ? 1 : 0,
		)
	);

	// Also change the message icon to match the current status
	$db->query('', '
		UPDATE {db_prefix}messages
		SET icon = {string:icon}
		WHERE id_msg = {int:message}',
		array(
			'message' => $firstmsg,
			'icon' => empty($solved) ? 'solved' : 'exclamation',
		)
	);
}