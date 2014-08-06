	<div class="bottom-menu">
		<?php echo $approved ?> quotes approved; <?php echo $pending ?> quotes pending
	</div>

	<script type="text/javascript" src="jquery-2.1.1.min.js"></script>
	<script type="text/javascript">
		function ConfirmChoice(linkto){
			if (confirm("Are you sure?")) 
				location = linkto;
		};

		$(document).ready(function() {
			$('.rox,.sux').click(function(){
				var score = $(this).siblings('.score');
				var ajaxhref = $(this).attr('href');

				$.ajax({
					url: ajaxhref,
					success: function(data, textStatus) {
						score.load(ajaxhref.split('&v',1)[0]+' .score').fadeIn("slow");
					}
				});
			});
		});
	</script>
</body>
</html>