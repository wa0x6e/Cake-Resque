<!DOCTYPE html>
<html lang="<?php echo (!isset($language) ? 'en' : 'fr'); ?>">
<head>
	<meta charset="utf-8">
	<base href="<?php echo $_SERVER["HTTP_HOST"] ?>" />
	<title><?php echo APPLICATION_NAME . ' | ' . (empty($pageTitle) ? 'CakePHP plugin for creating background jobs that can be processed offline later' : $pageTitle); ?></title>
	<meta name="description" content="CakeResque is a CakePHP plugin for creating background jobs that can be processed offline later">
	<?php
	$css = getCssFiles();
	foreach ($css as $name => $url) {
		echo '<link rel="stylesheet" href="' . $url . '">';
		echo "\n\t";
	}
	?><link rel="stylesheet" href="/css/main.css?v=2.0.333">
</head>
<body>
<div class="container">
	<?php include 'menu_' . (!isset($language) ? 'en' : 'fr') . '.php'; ?>