﻿
<?php
	require_once 'funcs.php';
	require_once 'logic/allrpg.php';

	$id = array_key_exists('id', $_GET) ? intval($_GET['id']) : '';
  $result = get_game_by_allrpg_id($id);
  if (is_array($result))
  {
  $response = array();
    $response['id'] = $result['id'];
    $response['allrpg_info_id'] = $result['allrpg_info_id'];
    $response['profile_uri'] = "/game/" . $result['id'];
    echo json_encode($response);
  }
  else
  {
    echo "{}";
  }
	


	?>