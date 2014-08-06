<?php var_dump($quote) ?>
<div class="quote">
	<a href="?<?php echo $quote['id'] ?>">#<?php echo $quote['id'] ?></a> <span class="score">(<?php echo $quote['popularity'] ?>)</span>

	<?php if (isset($showQuoteTimes) && $showQuoteTimes): ?>
		<?php echo date('jS/M/Y',$quote['timestamp']); ?>
	<?php endif; ?>

	<a class="rox" href="?<?php echo $quote['id'] ?>&amp;v=rox">[+]</a>
	<a class="sux" href="?<?php echo $quote['id'] ?>&amp;v=sux">[-]</a>
	
	<?php if ($userIsAdmin): ?>
		<a href="#" onclick="javascript:ConfirmChoice('?del=<?php echo $quote['id'] ?>')">[x]</a>
		<?php if (!$quote['active']): ?>
			<a href="?moderation=1&amp;approve=<?php echo $quote['id'] ?>">[a]</a>
		<?php endif; ?>
	<?php endif; ?>

	<br>

	<span class="quote-text"><?php echo nl2br(htmlspecialchars(stripslashes($quote['quote']))) ?></span>
</div>
