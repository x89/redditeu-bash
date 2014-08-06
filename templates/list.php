<?php require __DIR__.'/partial/header.php' ?>

<?php if (isset($pagesString)): ?>
	<p><?php echo $pagesString; ?></p>
<?php endif; ?>
<?php if (isset($searchForm)): ?>
	<p><?php echo $searchForm; ?></p>
<?php endif; ?>

<?php foreach ($quotes as $quote): ?>
	<?php require __DIR__.'/partial/quote.php' ?>
<?php endforeach; ?>

<?php if (isset($pagesString)): ?>
	<p><?php echo $pagesString; ?></p>
<?php endif; ?>
<?php if (isset($searchForm)): ?>
	<p><?php echo $searchForm; ?></p>
<?php endif; ?>

<?php require __DIR__.'/partial/footer.php' ?>