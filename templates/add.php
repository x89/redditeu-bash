<?php require __DIR__.'/partial/header.php' ?>

<form style="background-color:#CCC;margin:10px;padding:10px;border:1px solid #333;" method="POST" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
	<textarea name="quote" maxlength="3000" style="width:700px;height:300px;"><?php if (isset($_POST['quote'])) echo stripslashes($_POST['quote']); ?></textarea><br>
	Attempt to strip timestamps? <input type="checkbox" name="strip" checked="checked"><br /><br />

<?php if ($enableCaptcha): ?>
	<?php echo recaptcha_get_html("6Lc8Q8ESAAAAAB8ZgZFKotSQ5dJQ7-IWVoYeTKlE"); ?>
<?php endif; ?>

<br><input type="submit" name="submit" value="Submit Quote"> <input type="reset" name="reset" value="Reset">
</form>

<?php require __DIR__.'/partial/footer.php' ?>
