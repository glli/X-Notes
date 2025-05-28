<?php
	session_name("XNOTES_SESSID");
	session_start();

	$base_dir = "/" . basename(__DIR__);
	$cfg = include('config.php');
	$username = $_SESSION['Username'];
	$xnotes_username = $username;
	if (empty($xnotes_username)) {
		$encoded_username = $_COOKIE['x-notes-data-encoded'];
		if (ctype_xdigit($encoded_username) && strlen($encoded_username) % 2 == 0) {
			$xnotes_username = str_rot13(hex2bin($encoded_username));
		}
	}
	if(!file_exists($cfg['xnotes_path'] . $xnotes_username . "/cfg/account.config")) {
		$xnotes_username = "admin";
	}
	$cfg_path = $cfg['xnotes_path'] . $xnotes_username . "/cfg/";
	$files_path = $cfg['xnotes_path'] . $xnotes_username . "/files/";

	$account = json_decode(file_get_contents($cfg_path . "account.config"), true);
	$token = json_decode(file_get_contents($cfg_path . "token.config"), true);
	$token_valid = false;
	$token_change = false;
	$time = time();
	foreach($token as $key => $ttl) {
		if($time > $ttl) {
			unset($token[$key]);
			$token_change = true;
		} elseif(isset($_SERVER['HTTP_USER_AGENT']) && substr($key, 16) == $_COOKIE['x-notes-remember-me']) {
			if(substr($key, 0, 16) == hash("fnv164", $_SERVER['HTTP_USER_AGENT'])) {
				$token_valid = true;
			} else {
				unset($token[$key]);
				$token_change = true;
			}
		}
	}
	if($token_change) {
		file_put_contents($cfg_path . "token.config", json_encode($token));
	}
	if(!$token_valid && isset($_COOKIE['x-notes-remember-me'])) {
		unset($_COOKIE['x-notes-remember-me']);
		unset($_COOKIE['x-notes-data-encoded']);
		setcookie("x-notes-remember-me", null, -1, $base_dir);
		setcookie("x-notes-data-encoded", null, -1, $base_dir);
	}
	$logged_in = false;
	$valid_username = $account["username"];
	if(strtolower($username) == strtolower($valid_username) or $token_valid) {
		$logged_in = true;
	}
?>
