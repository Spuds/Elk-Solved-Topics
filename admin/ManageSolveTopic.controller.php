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
 * Topic Solved administration controller.
 * This class allows to modify admin topic solved settings for the forum.
 */
class ManageSolveTopic_Controller extends Action_Controller
{
	/**
	 * Default method.
	 *
	 * - Requires admin_forum permissions
	 *
	 * @uses Topics Solved language file
	 */
	public function action_index()
	{
		isAllowedTo('admin_forum');
		loadLanguage('SolveTopic');

		$this->action_TSSettings_display();
	}

	/**
	 * Modify any setting related to SolveTopic.
	 *
	 * - Requires the admin_forum permission.
	 * - Accessed from ?action=admin;sa=solvetopic
	 */
	public function action_TSSettings_display()
	{
		global $txt, $scripturl, $context;

		// Get a list of boards that we can enable this on
		require_once(SUBSDIR . '/Boards.subs.php');
		$context += getBoardList(array('not_redirection' => true));

		// Instantiate the form
		$settingsForm = new Settings_Form(Settings_Form::DB_ADAPTER);

		// Initialize settings
		$settingsForm->setConfigVars($this->_settings());

		// Set up the template
		$context['post_url'] = $scripturl . '?action=admin;area=addonsettings;save;sa=solvetopic';
		$context['settings_title'] = $txt['topic_solved_title'];

		// Saving them ?
		if (isset($this->_req->query->save))
		{
			checkSession();

			// Manage the board selections from the callback
			foreach ($context['categories'] as $category)
			{
				$board_select = array();
				foreach ($category['boards'] as $board)
				{
					$board_select['solvetopic_board_' . $board['id']] = isset($_POST['solvetopic_board_' . $board['id']]);
				}

				updateSettings($board_select);
			}

			// All the rest normally
			$settingsForm->setConfigValues((array) $this->_req->post);
			$settingsForm->save();

			redirectexit('action=admin;area=addonsettings;sa=solvetopic');
		}

		$settingsForm->prepare();
	}

	/**
	 * Returns all solve topic settings in config_vars format.
	 */
	private function _settings()
	{
		global $context;

		loadTemplate('SolveTopic');

		return array(
			array('check', 'enable_solved_log', 'disabled' => !in_array('ml', $context['admin_features'], true)),
			array('check', 'solvetopic_display_notice'),
			array('title', 'solvetopic_board_desc'),
			array('callback', 'selectboards'),
		);
	}

	/**
	 * Public method to return the config settings, used for admin search
	 */
	public function settings_search()
	{
		return $this->_settings();
	}
}
