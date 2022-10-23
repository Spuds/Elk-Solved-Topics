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
 * Show a header that the topic is solved
 */
function template_solvetopic_header_above()
{
	global $txt;

	echo '<div class="successbox">', $txt['solvetopic_solved'], '</div>';
}

/**
 * Callback to display a list of boards that the solved topic can be enabled on
 */
function template_callback_selectboards()
{
	global $context, $modSettings;

	// Since we are in a callback, close the config_vars DL we are currently in
	echo '
					</dl>
					<dl>';

	foreach ($context['categories'] as $category)
	{
		// Start with a category header
		echo '
						<dt class="category">
							<div class="strong success">', $category['name'], '</div>
							<hr />
						</dt>';

		// Every board in this category
		foreach ($category['boards'] as $board)
		{
			// Board name checkbox
			echo '
						<dd style="margin-', $context['right_to_left'] ? 'right' : 'left', ': ', $board['child_level'] * 2, 'em;">
							<label for="solvetopic_board_', $board['id'], '">
								<input type="checkbox" id="solvetopic_board_', $board['id'], '" name="solvetopic_board_', $board['id'], '" value="', $board['id'], '"', !empty($modSettings['solvetopic_board_' . $board['id']]) ? ' checked="checked"' : '', ' /> ', $board['name'], '
							</label>
						</dd>';
		}

		echo '<br />';
	}

	// Close our list and enter a Dl, just like we never left
	echo '
					
					</dl>
					<dl class="settings">';
}