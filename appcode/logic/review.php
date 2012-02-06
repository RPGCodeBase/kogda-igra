<?php
require_once 'sqlbase.php';
require_once 'user_funcs.php';
require_once 'internal_log.php';

function do_add_game_review($game_id, $author, $topic_id, $uri, $author_lj = NULL)
{
  $sql = connect();
  
  $game_id = intval($game_id);
  
  $topic_id = intval($topic_id);
  $uri = $sql -> QuoteAndClean ($uri);
  
  $sql -> Run ("START TRANSACTION");
  if ($author_lj)
  {
    $author_name = $author_lj;
    $author_lj = get_user_id_from_name ($author_lj, FALSE);
    $author = 'NULL';
  }
  else
  {
    $author_lj = 'NULL';
    $author_name = $author;
    $author = $sql -> QuoteAndClean ($author);
  }
  $row = $sql -> GetRow("SELECT COUNT(*) AS review_count FROM `ki_review` WHERE game_id = $game_id AND show_review_flag = 1");
  $review_count = $row['review_count'] + 1;
  
  $query = "INSERT 
    INTO `ki_review` 
    (game_id, author_name, topic_id, show_review_flag, review_uri, author_id) 
    VALUES ($game_id, $author, $topic_id, 1, $uri, $author_lj)";
    
  internal_log_game (13, $game_id, "Рецензия от $author_name");
  
  $sql -> Run ($query);
  $review_id = $sql -> LastInsert ();
  
  $sql -> Run(
       "UPDATE `ki_games`
        SET review_count = $review_count
        WHERE id = $game_id");
  $sql -> Run ("COMMIT");
}

function do_delete_game_review ($review_id)
{
  $sql = connect();
  $review_id = intval($review_id);
  
  $sql -> Run ("START TRANSACTION");
  
  $prev_data = $sql -> GetRow("SELECT game_id, author_id FROM `ki_review` WHERE review_id = $review_id");
  
  $game_id = intval($prev_data['game_id']);
  $author_id = intval ($prev_data['author_id']);
  if ($author_id > 0)
  {
    $author = get_user_by_id($author_id);
    $author_name = $author['username'];
  }
  else
  {
    $author_name = $prev_data['author_name'];
  }
  
  $row = $sql -> GetRow("SELECT COUNT(*) AS review_count FROM `ki_review` WHERE game_id = $game_id AND show_review_flag = 1");
  $review_count = $row['review_count'] - 1;
  
  $query = "DELETE FROM `ki_review` WHERE review_id = $review_id";
  internal_log_game (14, $game_id, "Рецензия от $author_name");
  
  $sql -> Run ($query);
  
  $sql -> Run(
       "UPDATE `ki_games`
        SET review_count = $review_count
        WHERE id = $game_id");
  $sql -> Run ("COMMIT");
}

function get_game_by_review_id ($review_id)
{
  $sql = connect();
  $prev_data = $sql -> GetRow("SELECT game_id FROM `ki_review` WHERE review_id = $review_id");
  
  return $prev_data ? intval($prev_data['game_id']) : NULL;
}

function update_author_to_user($author, $username)
{
  $sql = connect();

  $sql -> begin();
  $author_id = get_user_id_from_name ($username, false);
  
  $author = $sql -> QuoteAndClean($author);
  
  $sql -> Run ("UPDATE `ki_review` SET author_id = $author_id, author_name = NULL WHERE author_name = $author AND author_id IS NULL");
  $sql -> Run ("UPDATE `ki_photo` SET author_id = $author_id, photo_author = NULL WHERE photo_author = $author AND author_id IS NULL");

  internal_log_user (18, $author_id, "$author");
  $sql -> commit();
}

function _get_reviews($where)
{
  $sql = connect();
  return $sql -> Query ("
    SELECT kr.*, `users`.`username`, kg.name
    FROM `ki_review`  kr
    LEFT JOIN `users` ON kr.author_id = `users`.`user_id`
    LEFT JOIN `ki_games` kg ON kg.id = kr.game_id
    WHERE ($where) AND show_review_flag = 1");
}

function get_reviews_for_user ($author_id)
{
  return _get_reviews("kr.author_id = $author_id");
}
function get_reviews_for_game ($game_id)
{
  
  $game_id = intval($game_id);
  return _get_reviews ("game_id = $game_id");
}
?>