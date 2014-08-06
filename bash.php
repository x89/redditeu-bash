<?php

$MySQL['user'] = 'bash';	
$MySQL['pass'] = 'bash';
$MySQL['db'] = 'bash';

$password = 'pisswurd';	
$the_title = 'SCReddit QDB';
$RequireCaptcha = 0;

$Num['latest'] = 10;
$Num['top'] = 25;
$Num['browsePP'] = 50;
$Num['random'] = 25;
$Num['search'] = 25;

mysql_connect('localhost', $MySQL['user'], $MySQL['pass']);
mysql_select_db($MySQL['db']);

function isAdmin(){
	global $password;
	if (isset($_COOKIE['bc_login'])){
		if (base64_decode($_COOKIE['bc_login']) == $password) { // Security 101
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

function smallMenu(){
	$Menu = '<div class="menu"><a href="bash.php">home</a> / <a href="?latest">latest</a> / <a href="?browse">browse</a> / <a href="?random">random</a> / <a href="?add">add</a> / <a href="?top">top</a> / <a href="?search">search</a> / <a href="?chat">chat</a>';
	if (isAdmin()){
		$Menu .= ' / <a href="?moderation">awaiting moderation</a> / <a href="?logout">logout</a>';
	}
	return $Menu .= '</div>';
}

function start_page($Title='', $Msg='') {

	global $the_title;
	if ($Title == '') $Title = $the_title;
	header('Content-type: text/html; charset=utf-8');	

echo <<<__HEREDOC__
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="bash.css">
	<title>$Title</title>
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
</head>
<body>
<div class="top-menu">$Title</div>
__HEREDOC__;

	echo smallMenu();
	if ($Msg) echo '<br><div class="welcome-box">'.$Msg.'</div>';

}

function show_quote($Quote, $ShowTime = FALSE) {

	if (isAdmin()){
		$del = '<a href="#" onclick="javascript:ConfirmChoice(\'?del='.$Quote['id'].'\')">[x]</a>';
		if ($Quote['active'] == 0){
			$approve = '<a href="?moderation=1&approve='.$Quote['id'].'">[a]</a>';
		}
	} else {
		$del = $approve = '';
	}

	if ($Quote){
		echo '<div class="quote"><a href="?'.$Quote['id'].'">#'.$Quote['id'].'</a> <span class="score">('.$Quote['popularity'].')</span> '.($ShowTime!=FALSE ? date('jS/M/Y',$Quote['timestamp']) : '').' <a class="rox" href="?'.$Quote['id'].'&amp;v=rox">[+]</a><a class="sux" href="?'.$Quote['id'].'&amp;v=sux">[-]</a>'.$del.$approve.'<br><span class="quote-text">'.nl2br(htmlspecialchars(stripslashes($Quote['quote']))).'</span></div>';
	} else {
		echo 'Quote not found or doesn\'t exist yet.';
	}
}

if (isset($_GET['pass'])) {

	if ($_GET['pass'] == $password) {
		setcookie('bc_login', base64_encode($_GET['pass']), time() + 60 * 60 * 24 * 90); // 90 days
		start_page($the_title.' - Logged In','Vilkommen administrator, you are now logged in and can browse to delete quotes.<br><br>');
	}

} elseif (isset($_GET['chat'])) {
	start_page($the_title.' - Chitty Chatty','<iframe src="http://kiwiirc.com/client/irc.quakenet.org/redditeu/?nick=webber|?" style="border:0; width:100%; height:450px;"></iframe><br /><strong>irc.quakenet.org #redditeu</strong><br><br>');

} elseif (isset($_GET['logout'])) {

	setcookie('bc_login', '', time() - 3600);
	start_page($the_title.' - Logged out','You have been logged out. Cheerio!');
	echo '</body></html>';

} elseif (isset($_GET['del'])) {

	if (isAdmin()){
		$quoteId = mysql_real_escape_string(intval($_GET['del']));
		mysql_query("DELETE FROM bc_quotes WHERE id = $quoteId LIMIT 1");
		mysql_query("DELETE FROM bc_votes WHERE quote_id = $quoteId LIMIT 1");
	}
	start_page($the_title.' - Quote Deleted','Quote deleted squire.');

} elseif (isset($_GET['del_all'])) {

	if (isAdmin()){
		mysql_query("DELETE FROM bc_quotes WHERE active = 0");
	}
	start_page($the_title.' - Quotes Deleted','Quote queue deleted squire.');

} elseif (isset($_GET['browse'])) {

	start_page($the_title.' - Browse');

	$page = intval(mysql_real_escape_string($_GET['browse'])) - 1;
	if ($page < 1) {
		$page = 0;
	}
	$NumPP = $Num['browsePP'];

	$sql = mysql_query("SELECT COUNT(*) AS count FROM bc_quotes WHERE active = 1");
	$total = mysql_result($sql, 0, 0);

	for ($x = 0; $x < intval($total/$NumPP)+1; $x++) {
		$pagesString .= '<a href="?browse='.($x + 1).'">'.($x+1).'</a> - ';
	}

	echo '<br>'.substr($pagesString,0,-3).'<br><br/>';

	$start = $page * $NumPP;
	$end = $page + $NumPP;

	$sql = mysql_query("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY id ASC LIMIT $start, $end");

	if (@mysql_num_rows($sql) > 0){
		while ($row = mysql_fetch_assoc($sql)){
			show_quote($row);
		}
	} else {
		echo 'No results found homeslice.';
	}

} elseif (isset($_GET['search'])){

	start_page($the_title.' - Search');

	if (strlen($_GET['search']) > 0){
		$searchQuery = mysql_real_escape_string($_GET['search']);
	}

	echo '<br><form method="GET" action="'.$_SERVER['REQUEST_URI'].'">
		Search for: <input type="text" name="search" value="'.htmlspecialchars($searchQuery).'">
		<input type="submit" name="submit" value="Search">
		</form>';

if (strlen($_GET['search'])>0){
	$sql = mysql_query("SELECT * FROM bc_quotes WHERE active = 1 AND quote LIKE '%$searchQuery%' LIMIT {$Num['search']}");

	if (@mysql_num_rows($sql) > 0){
		while ($row = mysql_fetch_assoc($sql)){
			show_quote($row);
		}

		echo '<form method="GET" action="'.$_SERVER['REQUEST_URI'].'">
			Search for: <input type="text" name="search" value="'.htmlspecialchars($searchQuery).'">
			<input type="submit" name="submit" value="Search">
			</form>';

	} else {
		echo 'No results found homeslice.';
	}
}

} elseif (isset($_GET['add'])){

	if ($RequireCaptcha){
		require_once('./recaptchalib.php'); // We don't actually have this file.
	}

	if (isset($_POST['submit'])){

		if ($RequireCaptcha){
			$privatekey = "6Lc8Q8ESAAAAADAgiufKhG7J8vlTJnXMsHrAtOww";
			$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

			if (!$resp->is_valid) {
				$Msg = "<strong>The reCAPTCHA wasn't entered correctly. Try it again.</strong><br>";
			}
		}

		if (!$Msg){
			$timestamp = time();
			$ip = $_SERVER['REMOTE_ADDR'];
			$quote = mysql_real_escape_string(substr($_POST['quote'], 0, 9993000));

			if ($_POST['strip'] == 'on'){
				$quoteSplode = explode('\n', $quote);
				foreach ($quoteSplode as $Line => $Value){
					$temp = strpos($Value, '<');

					if ($temp === FALSE){
						$quoteStripped .= $Value;
					} else {
						$quoteStripped .= substr($Value, $temp);
					}
				}
				$quoteStripped = str_replace(' ', ' ', $quoteStripped);
				$quote = $quoteStripped;
			}
			if (strlen($quote)>5){
				mysql_query("INSERT INTO bc_quotes (timestamp, ip, quote, active) VALUES('$timestamp','$ip','$quote',0)");
				$lastId = mysql_insert_id();
				$Msg = '<strong>Quote added as <a href="?'.$lastId.'">#'.$lastId.'</a>. Thanks for participating :-)</strong><br />';
			} else {
				$Msg = 'Quote not added.';
			}
		}
	}

	start_page($the_title.' - Add',$Msg);

	echo '<br><form style="background-color:#CCC;margin:10px;padding:10px;border:1px solid #333;" method="POST" action="'.$_SERVER['REQUEST_URI'].'">
		<textarea name="quote" maxlength="3000" style="width:700px;height:300px;">'.stripslashes($_POST['quote']).'</textarea><br>
		Attempt to strip timestamps? <input type="checkbox" name="strip" checked="checked"><br /><br />';

	if ($RequireCaptcha){
		$publickey = "6Lc8Q8ESAAAAAB8ZgZFKotSQ5dJQ7-IWVoYeTKlE";
		echo recaptcha_get_html($publickey);
	}

	echo '<br><input type="submit" name="submit" value="Submit Quote"> <input type="reset" name="reset" value="Reset">
		</form>';

} elseif (isset($_GET['approve'])){

	if (isAdmin()){
		$quoteId = intval(mysql_real_escape_string($_GET['approve']));
		mysql_query("UPDATE bc_quotes SET active = 1 WHERE id = $quoteId");
		start_page($the_title,'Quote approved and is now live on the site.');
	}

} elseif (isset($_GET['random'])){

	start_page($the_title.' - Random Quotes','Refresh page to view more random quotes.');

	$getRand=mysql_query("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY RAND() LIMIT {$Num['random']}");

	if (mysql_num_rows($getRand)>0){
		while ($Rand=mysql_fetch_assoc($getRand)){
			show_quote($Rand);
		}
	}

} elseif (isset($_GET['top'])){

	start_page($the_title.' - Top Rated Quotes');

	$sql = mysql_query("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY popularity DESC LIMIT {$Num['top']}") or die(mysql_error());

	if (mysql_num_rows($sql) > 0){
		while ($row = mysql_fetch_assoc($sql)){
			show_quote($row);
		}
	}

} elseif (isset($_GET['latest'])){

	start_page($the_title.' - Latest Quotes');
	$sql = mysql_query("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY id DESC LIMIT {$Num['latest']}") or die(mysql_error());
	if (mysql_num_rows($sql)>0){
		while ($row = mysql_fetch_assoc($sql)){
			show_quote($row);
		}
	}

} elseif (isset($_GET['json'])){

	header('Content-type: application/json');
	$all_quotes = mysql_query("SELECT id, quote, popularity FROM bc_quotes ORDER BY id");
	if (!$all_quotes) {
		die(mysql_error());
	}
	$quotes = array();
	while ($result = mysql_fetch_assoc($all_quotes)) {
		array_push($quotes, $result);
	}
	echo(json_encode($quotes));

} elseif (isset($_GET['moderation'])) {

	start_page($the_title.' - Quotes in Moderation');

	if (isAdmin()){
		$getModeration = mysql_query("SELECT * FROM bc_quotes WHERE active = 0 ORDER BY id ASC LIMIT 100");

		if (mysql_num_rows($getModeration)>0){
			while ($Moderation = mysql_fetch_assoc($getModeration)){
				show_quote($Moderation);
			}
		} else {
			echo 'No quotes awaiting moderation =(';
		}
	}

} elseif (empty($_GET)){

	start_page($the_title.' - Home','<a href="http://www.screddit.eu/chat/">irc.quakenet.org #redditeu</a>');

	$sql = mysql_query("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY RAND() LIMIT 5");
	$result = mysql_fetch_assoc($sql);
	if (!$result) {
		die(mysql_error());
	}
	while ($row = mysql_fetch_assoc($sql)) {
		show_quote($row);
	}

} else {

	if (isset($_SERVER['QUERY_STRING'])){

		$array = array(0 => '&v=rox', 1 => '&v=sux');
		$quoteId = mysql_real_escape_string(str_replace($array, '', $_SERVER['QUERY_STRING']));

		if ($_GET['v'] == 'sux' or $_GET['v'] == 'rox') {
			$voteCheck = mysql_query("SELECT * FROM bc_votes WHERE quote_id = $quoteId AND ip = '$ip'");
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		if ($_GET['v'] == 'sux'){

			if (mysql_num_rows($voteCheck) < 1){
				mysql_query("INSERT INTO bc_votes (quote_id,ip,type) VALUES('$quoteId','$ip',1)") or die(mysql_error());
				mysql_query("UPDATE bc_quotes SET popularity = popularity - 1 WHERE id = $quoteId") or die(mysql_error());
			}
			start_page($the_title.' - Voted','Thanks for your vote.<br><br>');

		} elseif ($_GET['v'] == 'rox'){

			if (mysql_num_rows($voteCheck) < 1){
				mysql_query("INSERT INTO bc_votes (quote_id,ip,type) VALUES('$quoteId','$ip',2)") or die(mysql_error());
				mysql_query("UPDATE bc_quotes SET popularity = popularity + 1 WHERE id = $quoteId") or die(mysql_error());
			}
			start_page($the_title.' - Voted','Thank you for your vote!<br><br>');

		} else {

			$sql = mysql_query("SELECT * FROM bc_quotes WHERE id = '$quoteId'");
			$Quote = mysql_fetch_assoc($sql);

			if (empty($Quote) || !isset($Quote)){
				start_page($the_title);
			} elseif ($Quote['active'] == 0){
				start_page($the_title.' - Quote Pending Approval','Quote Pending Approval');
			} elseif ($Quote['active'] == 1){
				$ReplaceMe = array(0 => '\n', 1 => '\r', 2 => '\t');
				start_page($the_title.' - '.substr(str_replace($ReplaceMe,' ',htmlspecialchars($Quote['quote'])), 0, 80));
				show_quote($Quote, 1);
			}
		}
	}
}


if (!isset($_GET['json'])) {

	$sql = mysql_query("SELECT COUNT(*) AS count FROM bc_quotes GROUP BY active ORDER BY active DESC");

	$Approved = @mysql_result($sql, 0, 0);
	if ($Approved < 1){
		$Approved = 0;
	}

	$Pending = @mysql_result($sql, 1, 0);
	if ($Pending < 1){
		$Pending = 0;
	}
	echo smallMenu();
echo <<<__HEREDOC__
<div class="bottom-menu">
	<div>
		$Approved quotes approved; $Pending quotes pending
	</div>
</div>
</body>
</html>
__HEREDOC__;
}
?>
