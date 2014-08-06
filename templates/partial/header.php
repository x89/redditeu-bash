<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="bash.css">
	<title><?php echo $title; ?></title>
</head>
<body>
	<div class="top-menu">
		<?php echo $title; ?>
		<?php echo $msg; ?>
	</div>

	<div class="menu">
		<a href="/">home</a> / 
		<a href="?latest">latest</a> / 
		<a href="?browse">browse</a> / 
		<a href="?random">random</a> / 
		<a href="?add">add</a> / 
		<a href="?top">top</a> / 
		<a href="?search">search</a> / 
		<a href="?chat">chat</a>
		<?php if ($userIsAdmin): ?>
			 / <a href="?moderation">awaiting moderation</a>
			 / <a href="?logout">logout</a>
		<?php endif; ?></div>
