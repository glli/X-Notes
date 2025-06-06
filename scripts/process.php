<?php
	include "../init.php";

	$valid_password = $account["password"];
	
	$action = $_POST['action'];
	
	if($action == "login") {
		if(!empty($_POST['username']) && !empty($_POST['password'])) {			
			$username = strtolower($_POST['username']);
			$password = $_POST["password"];
			$remember = $_POST["remember"];

			$cfg_path = $cfg['xnotes_path'] . $username . "/cfg/";
			if(file_exists($cfg_path . "account.config")) {
				$account = json_decode(file_get_contents($cfg_path . "account.config"), true);
				$valid_username = $account["username"];
				$valid_password = $account["password"];
			}

			if(strtolower($username) == strtolower($valid_username) && password_verify($password, $valid_password)) {
				$_SESSION['Username'] = $valid_username;

				if($remember == "true" && isset($_SERVER['HTTP_USER_AGENT'])) {
					// 28 day ttl
					$ttl = time() + 3600 * 24 * 28;
					$key = str_shuffle(hash("sha512", str_shuffle($ttl)));
					$token = json_decode(file_get_contents($cfg_path . "token.config"), true);
					$token[hash("fnv164", $_SERVER['HTTP_USER_AGENT']) . $key] = $ttl;
					setcookie("x-notes-remember-me", $key, $ttl, $base_dir);
					setcookie("x-notes-data-encoded", bin2hex(str_rot13($username)), $ttl, $base_dir);
					file_put_contents($cfg_path . "token.config", json_encode($token));
				}

				echo "done";
			}
			else {
				echo "Invalid credentials.";
			}
		}
		else {
			echo "Please fill out both input fields.";
		}
	}
	if($action == "logout") { // clear all tokens
		session_start();
		session_destroy();
		$_SESSION = array();
		file_put_contents($cfg_path . "token.config", "{}");
		setcookie("x-notes-remember-me", null, -1, $base_dir);
		setcookie("x-notes-data-encoded", null, -1, $base_dir);
		if(empty($_SESSION)) {
			echo "done";
		}
	}
	
	if($logged_in) {
		if($action == "create-note") {
			$title = $_POST['title'];
			if(empty(trim($title))) {
				$title = "Undefined Title";
			}
			$file_name = time() . "-" . md5(str_shuffle(time())) . ".xnt";
			$file_content = ["time_created" => time(), "time_modified" => time(), "file_name" => $file_name, "locked" => false, "shared" => false, "password" => "", "author" => $valid_username, "title" => $title, "content" => ""];
			if(!file_exists($files_path . $file_name)) {
				$handle = fopen($files_path . $file_name, "w");
				$write = fwrite($handle, json_encode($file_content));
				if($write !== false) {
					echo "done";
				}
			}
		}
		if($action == "rename-note") {
			$title = $_POST['title'];
			$file = $files_path . $_POST['file'];
			if(empty(trim($title))) {
				$title = "Undefined Title";
			}
			$current = json_decode(file_get_contents($file), true);
			$locked = $current["locked"];
			if($locked) {
				if(!empty($_POST['password'])) {
					$password = $_POST['password'];
					$valid_password = $current['password'];
					if(password_verify($password, $valid_password)) {
						$current["title"] = $title;
						$current["time_modified"] = time();
						$handle = fopen($file, "w");
						$write = fwrite($handle, json_encode($current));
						if($write !== false) {
							echo "done";
						}
					}
				}
			}
			else {
				$current["title"] = $title;
				$current["time_modified"] = time();
				$handle = fopen($file, "w");
				$write = fwrite($handle, json_encode($current));
				if($write !== false) {
					echo "done";
				}
			}
		}
		if($action == "save-note") {
			$file = $files_path . $_POST['file'];
			$text = $_POST['text'];
			$current = json_decode(file_get_contents($file), true);
			$locked = $current["locked"];
			if($locked) {
				if(!empty($_POST['password'])) {
					$password = $_POST['password'];
					$valid_password = $current['password'];
					include "./aes.php";
					if(password_verify($password, $valid_password)) {
						$aes = new AES($text, $password, 256);
						$encrypted = $aes->encrypt();
						$current["content"] = $encrypted;
						if($current["content"] != $text) {
							$current["time_modified"] = time();
						}
						$handle = fopen($file, "w");
						$write = fwrite($handle, json_encode($current));
						if($write !== false) {
							echo "done";
						}
					}
				}
			}
			else {
				if($current["content"] != $text) {
					$current["time_modified"] = time();
				}
                $current["content"] = $text;
				$handle = fopen($file, "w");
				$write = fwrite($handle, json_encode($current));
				if($write !== false) {
					echo "done";
				}
			}
		}
		if($action == "delete-note") {
			$file = $files_path . $_POST['file'];
			$bypass = $_POST['bypass'];
			if($bypass) {
				$delete = unlink($file);
				if($delete) {
					echo "done";
				}
			}
			else {
				$current = json_decode(file_get_contents($file), true);
				$locked = $current["locked"];
				if($locked) {
					if(!empty($_POST['password'])) {
						$password = $_POST['password'];
						$valid_password = $current['password'];
						if(password_verify($password, $valid_password)) {
							$delete = unlink($file);
							if($delete) {
								echo "done";
							}
						}
					}
				}
				else {
					$delete = unlink($file);
					if($delete) {
						echo "done";
					}
				}
			}
		}
		if($action == "lock-note") {
			include "./aes.php";
			$file = $files_path . $_POST['file'];
			$password = $_POST['password'];
			$current = json_decode(file_get_contents($file), true);
			if(!$current["locked"]) {
				$text = $current["content"];
				if(!empty($text)) {
					$aes = new AES($text, $password, 256);
					$encrypted = $aes->encrypt();
					$current["content"] = $encrypted;
				}
				$current["locked"] = true;
				$current["time_modified"] = time();
				$current["password"] = password_hash($password, PASSWORD_BCRYPT);
				$handle = fopen($file, "w");
				$write = fwrite($handle, json_encode($current));
				if($write !== false) {
					echo "done";
				}
			}
		}
		if($action == "unlock-note") {
			include "./aes.php";
			$file = $files_path . $_POST['file'];
			$password = $_POST['password'];
			$current = json_decode(file_get_contents($file), true);
			$valid_password = $current["password"];
			if(password_verify($password, $valid_password)) {
				if(!empty($current["content"])) {
					$encrypted = $current["content"];
					$aes = new AES($encrypted, $password, 256);
					$decrypted = $aes->decrypt();
					$current["content"] = $decrypted;
				}
				$current["locked"] = false;
				$current["time_modified"] = time();
				$current["password"] = "";
				$handle = fopen($file, "w");
				$write = fwrite($handle, json_encode($current));
				if($write !== false) {
					echo "done";
				}
			}
		}
		if($action == "relock-note") {
			include "./aes.php";
			$file = $files_path . $_POST['file'];
			$current_password = $_POST['current_password'];
			$new_password = $_POST['new_password'];
			$current = json_decode(file_get_contents($file), true);
			$valid_password = $current["password"];
			if(password_verify($current_password, $valid_password)) {
				$encrypted = $current["content"];
				$aes = new AES($encrypted, $current_password, 256);
				$decrypted = $aes->decrypt();
				$aes = new AES($decrypted, $new_password, 256);
				$encrypted = $aes->encrypt();
				$current["content"] = $encrypted;
				$current["time_modified"] = time();
				$current["password"] = password_hash($new_password, PASSWORD_BCRYPT);
				$handle = fopen($file, "w");
				$write = fwrite($handle, json_encode($current));
				if($write !== false) {
					echo "done";
				}
			}
		}
		if($action == "publicize-note") {
			$file = $files_path . $_POST['file'];
			$current = json_decode(file_get_contents($file), true);
			$locked = $current["locked"];
			if($locked) {
				if(!empty($_POST['password'])) {
					$password = $_POST['password'];
					$valid_password = $current['password'];
					if(password_verify($password, $valid_password)) {
						$current["shared"] = true;
						$current["time_modified"] = time();
						$handle = fopen($file, "w");
						$write = fwrite($handle, json_encode($current));
						if($write !== false) {
							echo "done";
						}
					}
				}
			}
			else {
				$current["shared"] = true;
				$current["time_modified"] = time();
				$handle = fopen($file, "w");
				$write = fwrite($handle, json_encode($current));
				if($write !== false) {
					echo "done";
				}
			}
		}
		if($action == "privatize-note") {
			$file = $files_path . $_POST['file'];
			$current = json_decode(file_get_contents($file), true);
			$locked = $current["locked"];
			if($locked) {
				if(!empty($_POST['password'])) {
					$password = $_POST['password'];
					$valid_password = $current['password'];
					if(password_verify($password, $valid_password)) {
						$current["shared"] = false;
						$current["time_modified"] = time();
						$handle = fopen($file, "w");
						$write = fwrite($handle, json_encode($current));
						if($write !== false) {
							echo "done";
						}
					}
				}
			}
			else {
				$current["shared"] = false;
				$current["time_modified"] = time();
				$handle = fopen($file, "w");
				$write = fwrite($handle, json_encode($current));
				if($write !== false) {
					echo "done";
				}
			}
		}
		if($action == "raw-data") {
			$file = $_POST['file'];
			if(!empty($_POST['password'])) {
				$password = $_POST['password'];
			}
			$note = json_decode(file_get_contents($files_path . $file), true);
			$valid_password = $note["password"];
			$locked = $note["locked"];
			if($locked) {
				if(password_verify($password, $valid_password)) {
					echo json_encode($note);
				}
				else {
					echo "incorrect";
				}
			}
			else {
				$note["file_name"] = "REDACTED";
				echo json_encode($note);
			}
		}
		if($action == "save-settings") {
			$config = $_POST["config"];
			$write = file_put_contents($cfg_path . "preferences.config", $config);
			if($write) {
				echo "done";
			}
		}
		if($action == "reset-settings") {
			$config = array("appearance" => array("theme" => "light", "note-icons" => "colored", "formatting-buttons" => "square", "search-box" => "visible", "separators" => "visible"), "behavior" => array("reopen-notes" => "automatically", "tooltips" => "enabled", "default-settings-page" => "appearance", "notifications" => "enabled"));
			$json = json_encode($config);
			$write = file_put_contents($cfg_path . "preferences.config", $json);
			if($write) {
				echo "done";
			}
		}
		if($action == "change-username") {
			if(!empty($_POST['username']) && !empty($_POST['password'])) {
				$posted_username = $_POST['username'];
				$posted_password = $_POST['password'];
				if(password_verify($posted_password, $valid_password)) {
					if(ctype_alnum($posted_username)) {
						if(!file_exists($cfg['xnotes_path'] . $posted_username)) {
							$account["username"] = $posted_username;
							$json = json_encode($account);
							file_put_contents($cfg_path . "account.config", $json);
							rename($cfg['xnotes_path'] . $valid_username, $cfg['xnotes_path'] . $posted_username);
							echo "done";
						} else {
							echo "Username exists! Please choose another one.";
						}
					}
					else {
						echo "Username can only have letters and numbers.";
					}
				}
				else {
					echo "Wrong password.";
				}
			}
			else {
				echo "Please fill out both fields.";
			}
		}
		if($action == "change-password") {
			if(!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
				$posted_current_password = $_POST['current_password'];
				$posted_new_password = $_POST['new_password'];
				if(password_verify($posted_current_password, $valid_password)) {
					$hashed = password_hash($posted_new_password, PASSWORD_BCRYPT);
					$account["password"] = $hashed;
					$json = json_encode($account);
					file_put_contents($cfg_path . "account.config", $json);
					echo "done";
				}
				else {
					echo "Wrong password.";
				}
			}
			else {
				echo "Please fill out all fields.";
			}
		}
		if($action == "delete-all-notes") {
			if(!empty($_POST['password'])) {
				$posted_password = $_POST['password'];
				if(password_verify($posted_password, $valid_password)) {
					$files = glob($files_path . "*.xnt");
					foreach($files as $file) {
						unlink($file);
					}
					$notes = glob($files_path . "*.xnt");
					if(empty($notes)) {
						echo "done";
					}
					else {
						echo "Files couldn't be deleted.";
					}
				}
				else {
					echo "Incorrect password.";
				}
			}
			else {
				echo "Please fill out the password field.";
			}
		}
	}
?>