<?php
	require_once 'funcs.php';
	require_once 'user_funcs.php';
	require_once 'logic/stat.php';
	require_once 'review.php';
	require_once 'logic/photo.php';
	require_once 'media.php';

	$username = array_key_exists ('id', $_GET) ? $_GET['id'] : 0;
	if (!$username)
	{
    return_to_main();
	}

	$userdata = get_user_by_name ($username);

	if (!is_array($userdata))
	{
    return_to_main();
	}

  $id = $userdata['user_id'];
  $email = $userdata['email'];

	write_header('Kogda-igra.Ru :: Пользователи :: '. $username);
	$topmenu = new TopMenu();
	$topmenu -> pagename = $username;
	$topmenu -> show();
	
	$gravatar_email = $email ? $email : 'nobody@kogda-igra.ru';
	$gravatar_email = md5( strtolower( trim( $gravatar_email ) ) );
	
	echo "<img src=\"http://www.gravatar.com/avatar/$gravatar_email.jpg?d=mm\">";

  $date = $userdata['lastvisit'] ? formate_single_date ($userdata['lastvisit']) : 'Никогда';
  $editor_stat = get_editor_stat_by_id ($id);
	echo '<p>';
	$privs = get_privs_desc_for_user($id);
	if ($privs)
	{
    echo "<b>На сайте</b>: $privs<br>";
  }
  
  if (strpos($username, '@') === FALSE)
  {
    echo "<b>ЖЖ</b>: " . show_lj_user($username) . " <br>";
  }

	echo "<b>Был в последний раз</b>: $date <br>";

	if ($email)
	{
    echo "<b>Email</b>: <a href=\"mailto:{$email}\">{$email}</a> <br>";
  }
	$update_count = $editor_stat['update_count'];
  $new_count = $editor_stat['new_count'];
  if ($update_count > 0 || $new_count > 0)
  {
    echo "<b>Правок в базе за 3 месяца:</b> $update_count ";
    if (check_edit_priv())
    {
      echo "(<a href=\"/lenta/user/$id\">Лента правок</a>)";
    }
    echo "<br>";
    echo "<b>Новых игр в базе за 3 месяца:</b> $new_count <br>";
  }

  $review = new ReviewForUser ($id);
  $review -> show();

  show_media (get_photo_by_user($id));

  if (check_my_priv(USERS_CONTROL_PRIV))
  {
    echo "<a href=\"/edit/users/$id\">Редактировать права пользователя</a>";
  }

	write_footer();



?>