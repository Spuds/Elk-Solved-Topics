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
{
	die('No access...');
}

/**
 * Addon Hook, integrate_sa_modify_modifications, Called from AddonSettings_Controller
 * Adds new subActions to the available addon array
 *
 * @param array $subActions
 */
function imm_solvetopic(&$subActions)
{
	$subActions['solvetopic'] = array(
		'file' => 'ManageSolveTopic.controller.php',
		'controller' => 'ManageSolveTopic_Controller',
		'function' => 'action_index',
		'permission' => 'admin_forum'
	);
}

/**
 * Admin Hook, integrate_admin_areas, called from Admin.php
 * Used to add/modify admin menu areas
 *
 * @param array $admin_areas
 * @param array $menuOptions
 */
function iaa_solvetopic(&$admin_areas, &$menuOptions)
{
	global $txt, $context, $modSettings, $scripturl;

	loadlanguage('SolveTopic');

	// Add the solved log menu choice under admin logs
	$solvelog = array();
	$solvelog['solvelog'] = array(
		$txt['modlog_solve_log'],
		'moderate_forum',
		'enabled' => !empty($modSettings['enable_solved_log']) && in_array('ml', $context['admin_features']),
		'url' => $scripturl . '?action=moderate;area=solvedlog'
	);
	$insert_after = 'modlog';
	$admin_areas['maintenance']['areas']['logs']['subsections'] = elk_array_insert($admin_areas['maintenance']['areas']['logs']['subsections'], $insert_after, $solvelog, 'after');

	// Add the settings page to the addon list
	$admin_areas['config']['areas']['addonsettings']['subsections']['solvetopic'] = array($txt['topic_solved_title']);
}

/**
 * Permissions hook, integrate_load_permissions, called from ManagePermissions.subs.php
 * Used to add new permisssions
 *
 * @param array $permissionGroups
 * @param array $permissionList
 * @param array $leftPermissionGroups
 * @param array $hiddenPermissions
 * @param array $relabelPermissions
 */
function ilp_solvetopic(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$permissionList['board']['solve_topic'] = array(true, 'topic', 'moderate', 'moderate');
}

/**
 * integrate_display_buttons hook, called from Display.controller
 * Used to add additional buttons to topic views
 */
function idb_solvetopic()
{
	global $context, $scripturl, $modSettings, $board;

	loadLanguage('SolveTopic');

	// First determine if they can solve a topic and do so in this board
	$context['can_solve'] = allowedTo('solve_topic_any') || ($context['user']['started'] && allowedTo('solve_topic_own'));
	$context['board_solve'] = !empty($modSettings['solvetopic_board_' . $board]);
	$context['can_solve'] &= $context['board_solve'];

	// Showing a title bar in the message view?
	if (!empty($modSettings['solvetopic_display_notice']) && $context['is_solved'] && $context['board_solve'])
	{
		loadTemplate('SolveTopic');
		$template_layers = Template_Layers::instance();
		$template_layers->add('solvetopic_header');
	}

	// Add solve topic to the moderation button array
	$context['mod_buttons']['solve'] = array(
		'test' => 'can_solve',
		'text' => empty($context['is_solved']) ? 'solve_topic' : 'unsolve_topic',
		'lang' => true,
		'custom' => empty($context['is_solved']) ? 'style="font-weight:700;color:green"' : 'style="font-weight:700;color:red"',
		'url' => $scripturl . '?action=SolveTopic;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']
	);
}

/**
 * Topic query hook, integrate_topic_query called from Display.controller
 * Used to add additional query details to the topic display query
 *
 * @param string[] $topic_selects
 * @param string[] $topic_tables
 * @param string[] $topic_parameters
 */
function itq_solvetopic(&$topic_selects, &$topic_tables, &$topic_parameters)
{
	$topic_selects[] = 't.solved';
}

/**
 * Topic display hook, integrate_display_topic called from Display.controller
 * Used to gain access to the topicquery results
 *
 * @param array $topicinfo
 */
function idt_solvetopic($topicinfo)
{
	global $context;

	$context['is_solved'] = !empty($topicinfo['solved']);
}

/**
 * Log Types hook, integrate_log_types, called from Logging.php
 * used to add the solved topic log to the list of log types
 *
 * @param int $log_types
 */
function ilt_solvetopic(&$log_types)
{
	$log_types['solve'] = 4;
}

/**
 * Add moderation menu items, integrate_moderation_areas called from ModerationCenter Controller
 * Provide access from the moderation centrer to the solved topic log
 *
 * @param array $moderation_areas
 * @param array $menuOptions
 */
function ima_solvetopic(&$moderation_areas, &$menuOptions)
{
	global $modSettings, $context, $txt;

	loadLanguage('SolveTopic');

	$insert_after = 'modlog';

	// Define the new solve log menu choice
	$new_menu = array(
		'solvedlog' => array(
			'icon' => 'transparent.png',
			'class' => 'admin_img_logs',
			'enabled' => !empty($modSettings['enable_solved_log']) && in_array('ml', $context['admin_features']),
			'label' => $txt['modlog_solve_log'],
			'file' => 'SolveTopic.controller.php',
			'controller' => 'SolveTopic_Controller',
			'function' => 'action_view_solved_log',
		)
	);

	$moderation_areas['logs']['areas'] = elk_array_insert($moderation_areas['logs']['areas'], $insert_after, $new_menu, 'after');
}

/**
 * Message index hook, integrate_messageindex_topics, called from MessageIndex.controller
 * Used to add additional / adjust query parameters to the messageIndexTopics function
 * Can also add additional items to $context ;)
 *
 * @param string $sort_column
 * @param array $indexOptions
 */
function imt_solvetopic(&$sort_column, &$indexOptions)
{
	global $context, $modSettings, $board;

	$indexOptions['custom_selects'][] = 't.solved';
	$context['board_solve'] = !empty($modSettings['solvetopic_board_' . $board]);
}

/**
 * Message icon hook, integrate_messageindex_icons, called from MessageIndex.subs.php
 * Used to add additional known message index icons
 *
 * @param array $stable_icons
 */
function imi_solvetopic(&$stable_icons)
{
	$stable_icons[] = 'solved';
}