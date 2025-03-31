<?php	
	include "init.php";

	$config_json = file_get_contents($cfg_path . "preferences.config");
	$config = json_decode($config_json, true);
	$theme = $config["appearance"]["theme"];
	$sidebar_items = $config["appearance"]["sidebar-items"];
	$note_icons = $config["appearance"]["note-icons"];
	$formatting_buttons = $config["appearance"]["formatting-buttons"];
	$search_box = $config["appearance"]["search-box"];
	$separators = $config["appearance"]["separators"];
	$reopen_notes = $config["behavior"]["reopen-notes"];
	$tooltips = $config["behavior"]["tooltips"];
	$default_settings_page = $config["behavior"]["default-settings-page"];
	
	include "./assets/function_icons.php";
	
	include "./scripts/detect_device.php";
	if($user_agent_mobile) {
		$device = "mobile";
	}
	else {
		$device = "desktop";
	}
	
	$domain = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
	if($theme == "light") {
		$theme_color = "#ebebeb";
		$theme_body_color = "rgb(255,255,255)";
	}
	elseif($theme == "dark") {
		$theme_color = "#3c3c3c";
		$theme_body_color = "rgb(40,40,40)";
	}
	
	if(!$logged_in) {
		include("./assets/login.php");
	}
	else {
?>
<!-- Copyright <?php echo date('Y'); ?> © Xtrendence -->
<!DOCTYPE html>
<html>
	<head>
		<script src="./source/js/jquery.js"></script>
		<script src="./source/js/tippy.js"></script>
		<script src="./source/js/xnotes.js?<?php echo time(); ?>"></script>
		<link rel="icon" type="image/png" href="./source/images/favicon.png">
		<link rel="stylesheet" href="./source/css/structure.css?<?php echo time(); ?>">
		<link rel="stylesheet" href="./source/css/<?php echo $theme; ?>.css?<?php echo time(); ?>" class="theme-stylesheet">
		<link rel="stylesheet" href="./source/css/resize.css?<?php echo time(); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="theme-color" content="<?php echo $theme_color; ?>" class="navbar-theme-color">
		<title>X:/Notes</title>
	</head>
	
	<body id="<?php echo $device; ?>" data-domain="<?php echo $domain; ?>" style="background:<?php echo $theme_body_color; ?>;">
		<div class="main-page" data-encoded="<?php echo bin2hex(str_rot13($username)); ?>">
			<div class="column sidebar-wrapper noselect">
				<div class="sidebar-top">
					<div class="sidebar-icon-wrapper compose-button">
						<?php echo $compose_icon; ?>
					</div>
				</div>
				<div class="sidebar-middle">
					<div class="sidebar-icon-wrapper notes-button active">
						<?php echo $note_icon; ?>
					</div>
					<div class="sidebar-icon-wrapper locked-button">
						<?php echo $locked_icon; ?>
					</div>
					<div class="sidebar-icon-wrapper shared-button">
						<?php echo $globe_icon; ?>
					</div>
				</div>
				<div class="sidebar-bottom">
					<div class="sidebar-icon-wrapper help-button">
						<?php echo $help_icon; ?>
					</div>
					<div class="sidebar-icon-wrapper settings-button">
						<?php echo $settings_icon; ?>
					</div>
					<div class="sidebar-icon-wrapper logout-button">
						<?php echo $logout_icon; ?>
					</div>
				</div>
			</div>
			<div class="column notes-wrapper noselect">
				<div class="notes-list-wrapper">
					<div class="notes-list"></div>
					<div class="notes-list-padding"></div>
				</div>
				<div class="column-navbar search-wrapper">
					<input class="search-bar" type="text" placeholder="Search..." name="search" autocomplete="one-time-code">
				</div>
			</div>
			<div class="column editor-wrapper">
				<div class="actions-navbar">
					<div class="actions-wrapper-left">
						<div class="action-wrapper action-bold">
							<?php echo $bold_icon; ?>
						</div>
						<div class="action-wrapper action-italic">
							<?php echo $italic_icon; ?>
						</div>
						<div class="action-wrapper action-text">
							<?php echo $text_icon; ?>
						</div>
						<div class="action-wrapper action-heading">
							<?php echo $heading_icon; ?>
						</div>
						
					</div>
					<div class="actions-wrapper-left-overlay"></div>
					<div class="actions-wrapper-right">
						<div class="action-wrapper action-save">
							<?php echo $save_icon; ?>
						</div>
						<div class="action-wrapper action-menu">
							<?php echo $ellipsis_icon; ?>
						</div>
					</div>
				</div>
				<div class="editor-container">
					<div class="editor-content editor-empty"></div>
				</div>
				<div class="editor-menu-wrapper">
					<div class="editor-submenu-lock-wrapper">
						<button type="button" class="editor-menu-button relock-button">Change Password</button>
						<button type="button" class="editor-menu-button lock-button">Set Password</button>
						<button type="button" class="editor-menu-button unlock-button">Remove Password</button>
					</div>
					<div class="editor-submenu-share-wrapper">
						<button type="button" class="editor-menu-button public-button">Make Public</button>
						<button type="button" class="editor-menu-button private-button">Make Private</button>
						<button type="button" class="editor-menu-button link-button">Copy Link</button>
					</div>
					<button type="button" class="editor-menu-button main rename-button">Rename</button>
					<button type="button" class="editor-menu-button main submenu-lock-button">Lock</button>
					<button type="button" class="editor-menu-button main submenu-share-button">Share</button>
					<button type="button" class="editor-menu-button main delete-button">Delete</button>
					<button type="button" class="editor-menu-button main copy-button">Copy</button>
					<button type="button" class="editor-menu-button main raw-button">Raw Data</button>
					<button type="button" class="editor-menu-button main close-button">Close</button>
				</div>
			</div>
		</div>
		<?php include "./assets/ui-elements.php"; ?>
	</body>
</html>
<?php } ?>