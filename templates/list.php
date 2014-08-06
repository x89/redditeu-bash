<?php require __DIR__.'/partial/header.php' ?>

<?php if (isset($pagesString)): ?>
	<div class="menu">Page: <?php echo $pagesString; ?></div>
<?php endif; ?>
<?php if (isset($searchForm)): ?>
	<div class="search-form"><?php echo $searchForm; ?></div>
<?php endif; ?>

<?php foreach ($quotes as $quote): ?>
	<?php require __DIR__.'/partial/quote.php' ?>
<?php endforeach; ?>

<?php if (isset($pagesString)): ?>
	<div class="menu">Page: <?php echo $pagesString; ?></div>
<?php endif; ?>

<?php require __DIR__.'/partial/footer.php' ?>