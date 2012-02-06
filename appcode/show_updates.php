<?php
require_once 'funcs.php';
function write_update_line($game, $colspan)
{
    echo "<tr><td colspan=\"$colspan\">";
		$update_text = htmlspecialchars ($game['ki_update_type_name']);
		$user = htmlspecialchars ($game['username']);
		$date = formate_single_date($game['update_date']);
		$user = show_user_link($user, $game['user_id']);
		if ($game['update_type_polygon_flag'] > 0)
		{
      $update_text .= ' ('. $game['polygon_name'].')';
		}
		if ($game['update_type_photo_flag'] > 0 || $game['update_type_game_flag'] > 0 || $game['update_type_review_flag'] > 0)
		{
      $update_text .= ' (<a href="/game/'. $game['id'] . '">' . $game['name'].'</a>)';
		}
		if ($game['updated_user_id'] > 0)
		{
      $update_text .= " (" . show_user_link ($game['updated_user_name'], $game['updated_user_id']).")";
		}
		echo "$date $user: $update_text {$game['msg']}";
		echo "</td></tr>";
}

?>