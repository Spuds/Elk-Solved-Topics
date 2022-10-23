<?php

/**
 * @name      SolveTopic
 * @copyright 2014-2022 ElkArte Forum contributors
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * This software is a derived product, based on:
 *
 * TopicSolved 1.1.1
 * Copyright 2006-2008 Blue Dream (http://www.simpleportal.net)
 *
 * @version 1.0.3
 *
 */

/**
 * Solve Topic controller.
 * This class handles requests that allow for marking of a topic solved or unsolved
 */
class SolveTopic_Controller extends Action_Controller
{
	/**
	 * Default method
	 *
	 * @see Action_Controller::action_index()
	 */
	public function action_index()
	{
		// Where do you want to go today, umm, good choice
		$this->action_SolveTopic();
	}

	/**
	 * This method is executed before any action handler.
	 *
	 * - Loads common needed items
	 */
	public function pre_dispatch()
	{
		// Language and helper functions
		loadLanguage('SolveTopic');
		require_once(SUBSDIR . '/SolveTopic.subs.php');
	}

	/**
	 * Mark a topic solved.
	 *
	 * - Toggles between solved/not solved.
	 * - Requires the solve_own or solve_any permission.
	 * - Logs the action to the moderator log.
	 * - Returns to the topic after it is done.
	 * - Accessed via ?action=solvetopic.
	 */
	public function action_SolveTopic()
	{
		global $topic, $user_info, $board, $modSettings;

		// See if its enabled in this board.
		if (empty($modSettings['solvetopic_board_' . $board]))
		{
			throw new Elk_Exception('solvetopic_not_enabled', false);
		}

		// You need a topic to solve.
		if (empty($topic))
		{
			throw new Elk_Exception('not_a_topic', false);
		}

		checkSession('get');

		// Get the topic details, owner, solved status, etc
		list ($starter, $solved, $firstmsg) = getSolveTopicDetails($topic);

		// With the owner, we validate they can do this
		$user_solve = !allowedTo('solve_topic_any', $board);
		if ($user_solve && (int) $starter === (int) $user_info['id'])
		{
			isAllowedTo('solve_topic_own', $board);
		}
		else
		{
			isAllowedTo('solve_topic_any', $board);
		}

		// Update the solved status, change the icon to reflect this
		markSolveTopic($topic, $firstmsg, $solved);

		// Log this if enabled, someone may want to track progress
		if (!empty($modSettings['enable_solved_log']))
		{
			logAction(empty($solved) ? 'solve' : 'unsolve', array('topic' => $topic, 'board' => $board, 'member' => $starter), 'solve');
		}

		// Let's go back home.
		redirectexit('topic=' . $topic . '.' . $_REQUEST['start']);
	}

	/**
	 * Prepares the information from the moderation log for viewing solved topic entries
	 *
	 * - Disallows the deletion of events within twenty-four hours of now.
	 * - Accessed via ?action=moderate;area=solvelog
	 *
	 * @uses Modlog template, main sub-template.
	 */
	public function action_view_solved_log()
	{
		global $txt, $context, $scripturl;

		// Some help will be needed
		require_once(SUBSDIR . '/Modlog.subs.php');
		loadLanguage('Modlog');

		// Some things for the template
		$context['can_delete'] = allowedTo('admin_forum');
		$context['page_title'] = $txt['modlog_solve_log'];

		// Build the upper section of the page
		$context[$context['moderation_menu_name']]['tab_data'] = array(
			'title' => $txt['modlog_solve_log'],
			'class' => 'database',
			'description' => $txt['modlog_solve_log_help'],
		);

		// Log Type (fragile, other addons can't tell what numbers have been taken)
		$context['log_type'] = 4;

		// Handle deletion of entries, one at a time or all
		if (isset($_POST['removeall']) && $context['can_delete'])
		{
			checkSession();
			validateToken('mod-ml');
			deleteLogAction(4, 24);
		}
		elseif (!empty($_POST['remove']) && isset($_POST['delete']) && $context['can_delete'])
		{
			checkSession();
			validateToken('mod-ml');
			deleteLogAction(4, 24, $_POST['delete']);
		}

		require_once(SUBSDIR . '/GenericList.class.php');

		// Create a solved topic listing
		$listOptions = array(
			'id' => 'solvedtopic_log_list',
			'items_per_page' => 30,
			'no_items_label' => $txt['modlog_solve_log_no_entries'],
			'base_href' => $scripturl . '?action=moderate;area=solvedlog',
			'default_sort_col' => 'time',
			'get_items' => array(
				'function' => array($this, 'list_loadSolvedEntires'),
				'params' => array(
					$context['log_type'],
				),
			),
			'get_count' => array(
				'function' => array($this, 'list_countSolvedEntires'),
				'params' => array(
					$context['log_type'],
				),
			),
			'columns' => array(
				'action' => array(
					'header' => array(
						'value' => $txt['modlog_action'],
						'class' => 'lefttext',
					),
					'data' => array(
						'db' => 'action_text',
						'class' => 'smalltext',
					),
					'sort' => array(
						'default' => 'lm.action',
						'reverse' => 'lm.action DESC',
					),
				),
				'time' => array(
					'header' => array(
						'value' => $txt['modlog_date'],
						'class' => 'lefttext',
					),
					'data' => array(
						'db' => 'time',
						'class' => 'smalltext',
					),
					'sort' => array(
						'default' => 'lm.log_time DESC',
						'reverse' => 'lm.log_time',
					),
				),
				'moderator' => array(
					'header' => array(
						'value' => $txt['modlog_member'],
						'class' => 'lefttext',
					),
					'data' => array(
						'db' => 'moderator_link',
						'class' => 'smalltext',
					),
					'sort' => array(
						'default' => 'mem.real_name',
						'reverse' => 'mem.real_name DESC',
					),
				),
				'position' => array(
					'header' => array(
						'value' => $txt['modlog_position'],
						'class' => 'lefttext',
					),
					'data' => array(
						'db' => 'position',
						'class' => 'smalltext',
					),
					'sort' => array(
						'default' => 'mg.group_name',
						'reverse' => 'mg.group_name DESC',
					),
				),
				'ip' => array(
					'header' => array(
						'value' => $txt['modlog_ip'],
						'class' => 'lefttext',
					),
					'data' => array(
						'db' => 'ip',
						'class' => 'smalltext',
					),
					'sort' => array(
						'default' => 'lm.ip',
						'reverse' => 'lm.ip DESC',
					),
				),
				'delete' => array(
					'header' => array(
						'value' => '<input type="checkbox" name="all" class="input_check" onclick="invertAll(this, this.form);" />',
						'class' => 'centertext',
					),
					'data' => array(
						'function' => function($entry) {
							return '<input type="checkbox" class="input_check" name="delete[]" value="' . $entry['id'] . '"' . ($entry['editable'] ? '' : ' disabled="disabled"') . ' />';
						},
						'class' => 'centertext',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=moderate;area=solvedlog',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
				'token' => 'mod-ml',
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '
						' . ($context['can_delete'] ? '
						<input type="submit" name="remove" value="' . $txt['modlog_remove'] . '" onclick="return confirm(\'' . $txt['modlog_solve_log_remove_selected_confirm'] . '\');" class="right_submit" />
						<input type="submit" name="removeall" value="' . $txt['modlog_removeall'] . '" onclick="return confirm(\'' . $txt['modlog_solve_log_remove_all_confirm'] . '\');" class="right_submit" />' : ''),
					'class' => 'floatright',
				),
			),
		);

		createToken('mod-ml');

		// Create the solved topic listing.
		createList($listOptions);

		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'solvedtopic_log_list';
	}

	/**
	 * Callback for createList()
	 *
	 * - Returns a list of moderation log entries
	 * - Uses list_getModLogEntries in modlog subs
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 * @param int $log_type
	 */
	public function list_loadSolvedEntires($start, $items_per_page, $sort, $log_type)
	{
		// Get all of our solved topic entries
		return list_getModLogEntries($start, $items_per_page, $sort, '', array(), $log_type);
	}

	/**
	 * Callback for createList()
	 *
	 * - Returns a count of solved topic moderation log entries
	 * - Uses list_getModLogEntryCount in modlog subs
	 *
	 * @param int $log_type
	 */
	public function list_countSolvedEntires($log_type)
	{
		// Get the count of our solved topic entries
		return list_getModLogEntryCount('', array(), $log_type);
	}
}
