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
					<ul class="ignoreboards floatleft">';

	// Create two columns to make this as compact as we can
	$i = 0;
	$limit = (int) ceil($context['num_boards'] / 2);

	foreach ($context['categories'] as $category)
	{
		// Done with the left column, now start the right one.
		if ($i === $limit)
		{
			echo '
					</ul>
					<ul class="ignoreboards floatright">';

			$i++;
		}

		// Start with a category header
		echo '
						<li class="category">
							<h4 class="strong success">', $category['name'], '</h4>
							<ul>';

		// Every board in this category
		foreach ($category['boards'] as $board)
		{
			// Filled the left column up listing the boards in this category so we need to start the right list
			if ($i === $limit)
				echo '
							</ul>
						</li>
					</ul>
					<ul class="ignoreboards floatright">
						<li class="category">
							<ul>';

			// Board name checkbox
			echo '
								<li class="board" style="margin-', $context['right_to_left'] ? 'right' : 'left', ': ', $board['child_level'] * 2, 'em;">
									<label for="solvetopic_board_', $board['id'], '">
										<input type="checkbox" id="solvetopic_board_', $board['id'], '" name="solvetopic_board_', $board['id'], '" value="', $board['id'], '"', !empty($modSettings['solvetopic_board_' . $board['id']]) ? ' checked="checked"' : '', ' class="input_check" /> ', $board['name'], '
									</label>
								</li>';

			$i++;
		}

		echo '
							</ul>
						</li>';
	}

	// Close our list and enter a Dl, just like we never left
	echo '
					</ul>
					<br class="clear">
					<dl class="settings">';
}