<?php

$MySQL['user']='boobtit';		// Your MySQL details
$MySQL['pass']='urgay';
$MySQL['db']='db';

$Password='pisswurd';					// Password for admin area. This is hardcoded and ISN'T majorly secure.
$TheTitle='<a href="http://www.screddit.eu/">SCReddit.EU</a> QDB';			// The title of your site (displayed within <title> tags and at top of most pages.
$RequireCaptcha=TRUE;				// If set to TRUE reCaptcha.net will be used to verify submissions as human.

$Num['latest']=10;					// Number of latest quotes to show on latest
$Num['top']=25;						// Number to show on top
$Num['browsePP']=50;				// Number per page on browse
$Num['random']=25;					// Number to show on random
$Num['search']=25;					// Max number of results on search

mysql_connect('localhost',$MySQL['user'],$MySQL['pass']);
mysql_select_db($MySQL['db']);

/*

Bash.org Clone PHP Script from http://www.seanbluestone.com/bash-org-clone-php-script-for-irc-quotes

Sean Bluestone of SeanBluestone.com's Bash.org clone script. This script attempts to replicate the functionality and feel of bash.org as closely as possible (minus some minor changes (i.e. Captcha).

To Install:
```````````
1. Run the SQL below in the database you've selected in the $MySQL settings above:


CREATE TABLE IF NOT EXISTS `bc_quotes` (
  `id` int(11) NOT NULL auto_increment,
  `timestamp` int(11) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `quote` text NOT NULL,
  `active` tinyint(1) NOT NULL,
  `popularity` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

mysql_query("CREATE TABLE IF NOT EXISTS `bc_votes` (
  `id` int(11) NOT NULL auto_increment,
  `quote_id` int(11) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `type` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2;

2. Upload bashclone.php to your webserver wherever you'd like it.

3. Done. Simple huh? Navigate to bashclone.php to view your site.

To Administrate:
````````````````
This script uses a hardcoded password which isn't the most secure method of protection but for the purposes of this script it's plenty.
To adminstrate, browse to http://www.yoursite.com/bashclone.php?pass=passhere where passhere is whatever you supplied for $Password above.
You can give this URL to anyone you want to give admin access to. Admin cookie will be kept for 90 days.

*/

function isAdmin(){

	global $Password;
	if(isset($_COOKIE['bc_login'])){
		if(base64_decode($_COOKIE['bc_login'])==$Password){
			return TRUE;
		}else{
			return FALSE;
		}
	}else{
		return FALSE;
	}
}

function smallMenu(){

	$Menu='<div class="menu" style="align:right"><a href="bash.php">home</a> / <a href="?latest">latest</a> / <a href="?browse">browse</a> / <a href="?random">random</a> / <a href="?add">add</a> / <a href="?top">top</a> / <a href="?search">search</a> / <a href="?chat">chat</a>';

	if(isAdmin()){
		$Menu.=' / <a href="?moderation">awaiting moderation</a> / <a href="?logout">logout</a>';
	}

	return $Menu.='</div>';
}


function start_page($Title='',$Msg=''){

	global $TheTitle;
	if($Title==''){ $Title=$TheTitle; }
	
	echo <<<EOHTML
        <!DOCTYPE html>
        <html>
        <head>
	<style type="text/css" media="screen">
body, html{
background: url('http://hello.eboy.com/eboy/wp-content/uploads/shop/ECB_LA_28k.png');
background-size:100% auto;
background-repeat:repeat; background-color:black;
font-family:courier new,lucida console,fixed;
}
a:link {
text-decoration:none;
color: blue;
}
a:visited {
text-decoration:none;
color: purple;
}
a:hover {
text-decoration:blink;
color: blue;
}
.left-element {
		float: left;
		width: 49%;
	}
	.right-element {
		float: right;
		width: 49%;
		text-align: right; /* depends on element width */
	} 	
	.topMenu{
		background-color:#397489;
		font-weight:bold;
font-size:30px;
color:#ffffff;
padding:30px;
margin:15px;
	}
	.menu{
		background-color:#f0f0f0;
		font-weight:bold;
margin:15px;
padding:10px;
left-padding:5px;
	}
	.quote{
		padding:8px;
		margin:15px;
		/*line-height:1.5;*/
		font-size:13px;
		margin-bottom:30px;
		background-color:#fafafa;
		border:2px dashed #397489;
	}
	</style>
	<title>$Title</title>
        <script type="text/javascript" src="http://jqueryjs.googlecode.com/files/jquery-1.3.2.min.js"></script>
	<script type="text/javascript">
	/* <![CDATA[ */
	function ConfirmChoice(linkto){
		answer = confirm("Are you sure?")
		if (answer !=0){
			location = linkto
		}
	}
	$(document).ready(function(){
	  $('.rox,.sux').click(function(){
	    score = $(this).siblings('.score');
            ajaxhref = $(this).attr('href');
	    $.ajax({
	      url: ajaxhref,
	      success: function(data, textStatus) {
		score.load(ajaxhref.split('&v',1)[0]+' .score').fadeIn("slow");
	      }
	    });
	    return false;
	  });
	});
        /* ]]> */
	</script>
	</head>
	<body>
	<div class="topMenu">$Title</div>
EOHTML;

	echo smallMenu();

	if($Msg){ echo '<br/><div style="background-color:#CCC;margin:10px;padding:5px;border:1px dashed #333;">'.$Msg.'</div>'; }

}


function show_quote($Quote,$ShowTime=FALSE){

	if(isAdmin()){
		$del='<a href="#" onclick="javascript:ConfirmChoice(\'?del='.$Quote['id'].'\')">[x]</a>';
		if($Quote['active']==0){
			$approve='<a href="?moderation=1&approve='.$Quote['id'].'">[a]</a>';
		}
	}else{
		$del=$approve='';
	}

	if($Quote){
		echo '<div class="quote"><a href="?'.$Quote['id'].'">#'.$Quote['id'].'</a> <span class="score">('.$Quote['popularity'].')</span> '.($ShowTime!=FALSE ? date('jS/M/Y',$Quote['timestamp']) : '').' <a class="rox" href="?'.$Quote['id'].'&amp;v=rox">[+]</a><a class="sux" href="?'.$Quote['id'].'&amp;v=sux">[-]</a>'.$del.$approve.'<br/>'.nl2br(htmlspecialchars(stripslashes($Quote['quote']))).'</div>';
	}else{
		echo 'Quote not found or doesn\'t exist yet.';
	}

}




if(isset($_GET['pass'])){

	if($_GET['pass']==$Password){
		setcookie('bc_login',base64_encode($_GET['pass']),time()+60*60*24*90); // 90 days
		start_page($TheTitle.' - Logged In','Vilkommen administrator, you are now logged in and can browse to delete quotes.<br/><br/>');
	}


}elseif(isset($_GET['chat'])){
		start_page($TheTitle.' - Chitty Chatty','<iframe src="http://kiwiirc.com/client/irc.quakenet.org/redditeu/?nick=webber|?" style="border:0; width:100%; height:450px;"></iframe><br /><strong>irc.quakenet.org #redditeu</strong><br/><br/>');

}elseif(isset($_GET['logout'])){

	setcookie('bc_login','',time()-3600);
	start_page($TheTitle.' - Logged out','You have been logged out. Cheerio!');
	echo '</body></html>';


}elseif(isset($_GET['del'])){

	if(isAdmin()){
		$quoteId=mysql_real_escape_string(intval($_GET['del']));
		mysql_query("DELETE FROM bc_quotes WHERE id = $quoteId LIMIT 1");
		mysql_query("DELETE FROM bc_votes WHERE quote_id = $quoteId LIMIT 1");
	}
	start_page($TheTitle.' - Quote Deleted','Quote deleted squire.');

}elseif(isset($_GET['del_all'])){

	if(isAdmin()){
		mysql_query("DELETE FROM bc_quotes WHERE active = 0");
	}
	start_page($TheTitle.' - Quotes Deleted','Quote queue deleted squire.');

}elseif(isset($_GET['browse'])){

	start_page($TheTitle.' - Browse');

	$page=intval(mysql_real_escape_string($_GET['browse']))-1;
	if($page<1){ $page=0; }
	$NumPP=$Num['browsePP'];

	$getTotal=mysql_query("SELECT COUNT(*) AS count FROM bc_quotes WHERE active = 1");
	$Total=mysql_result($getTotal,0,0);

	for($x=0;$x<intval($Total/$NumPP)+1;$x++){
		$pagesString.='<a href="?browse='.($x+1).'">'.($x+1).'</a> - ';
	}

	echo '<br/>'.substr($pagesString,0,-3).'<br/><br/>';

	$Start=$page*$NumPP;
	$End=$page+$NumPP;

	$getResults=mysql_query("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY id ASC LIMIT $Start,$End");

	if(@mysql_num_rows($getResults)>0){
		while($Result=mysql_fetch_assoc($getResults)){
			show_quote($Result);
		}
	}else{
		echo 'No results found homeslice.';
	}



}elseif(isset($_GET['search'])){

	start_page($TheTitle.' - Search');

	if(strlen($_GET['search'])>0){
		$searchQuery=mysql_real_escape_string($_GET['search']);
	}

	echo '<br/><form method="GET" action="'.$_SERVER['REQUEST_URI'].'">
	Search for: <input type="text" name="search" value="'.htmlspecialchars($searchQuery).'">
	<input type="submit" name="submit" value="Search">
	</form>';

	if(strlen($_GET['search'])>0){
		$getSearch=mysql_query("SELECT * FROM bc_quotes WHERE active = 1 AND quote LIKE '%$searchQuery%' LIMIT {$Num['search']}");

		if(@mysql_num_rows($getSearch)>0){
			while($Search=mysql_fetch_assoc($getSearch)){
				show_quote($Search);
			}

			echo '<form method="GET" action="'.$_SERVER['REQUEST_URI'].'">
			Search for: <input type="text" name="search" value="'.htmlspecialchars($searchQuery).'">
			<input type="submit" name="submit" value="Search">
			</form>';

		}else{
			echo 'No results found homeslice.';
		}
	}


}elseif(isset($_GET['add'])){

	if($RequireCaptcha){
		require_once('./recaptchalib.php');
	}

	if(isset($_POST['submit'])){

		if($RequireCaptcha){
			$privatekey = "6Lc8Q8ESAAAAADAgiufKhG7J8vlTJnXMsHrAtOww";
			$resp = recaptcha_check_answer ($privatekey,$_SERVER["REMOTE_ADDR"],$_POST["recaptcha_challenge_field"],$_POST["recaptcha_response_field"]);
	
			if (!$resp->is_valid) {
				$Msg="<strong>The reCAPTCHA wasn't entered correctly. Try it again.</strong><br/>";
			}
		}

		if(!$Msg){
			$timestamp=time();
			$ip=$_SERVER['REMOTE_ADDR'];
			$quote=mysql_real_escape_string(substr($_POST['quote'],0,9993000));

// preg_match("/.*([<*-].*)$/", $quote, $m); $m[1];
//preg_match("/^[^<*-]*([<*-].*)$/", $quote, $m); $m[1];
			if($_POST['strip']=='on'){
				$quoteSplode=explode('\n',$quote);
				foreach($quoteSplode as $Line => $Value){
					$temp=strpos($Value,'<');
	
					if($temp===FALSE){
						$quoteStripped.=$Value;
					}else{
						$quoteStripped.=substr($Value,$temp);
					}
				}
				$quoteStripped = str_replace(' ', ' ', $quoteStripped);
				$quote=$quoteStripped;
			}
			if(strlen($quote)>5){
				mysql_query("INSERT INTO bc_quotes (timestamp, ip, quote, active) VALUES('$timestamp','$ip','$quote',0)");
				$lastId=mysql_insert_id();
				$Msg='<strong>Quote added as <a href="?'.$lastId.'">#'.$lastId.'</a>. Thanks for participating :-)</strong><br />';
			}else{
				$Msg='Quote not added.';
			}
		}
	}

	start_page($TheTitle.' - Add',$Msg);


	echo '<br/><form style="background-color:#CCC;margin:10px;padding:10px;border:1px solid #333;" method="POST" action="'.$_SERVER['REQUEST_URI'].'">
	<textarea name="quote" maxlength="3000" style="width:700px;height:300px;">'.stripslashes($_POST['quote']).'</textarea><br/>
	Attempt to strip timestamps? <input type="checkbox" name="strip" checked="checked"><br /><br />';

	if($RequireCaptcha){
		$publickey = "6Lc8Q8ESAAAAAB8ZgZFKotSQ5dJQ7-IWVoYeTKlE"; // you got this from the signup page
		echo recaptcha_get_html($publickey);
	}

	echo '<br/><input type="submit" name="submit" value="Submit Quote"> <input type="reset" name="reset" value="Reset">
	</form>';




}elseif(isset($_GET['approve'])){

	if(isAdmin()){
		$quoteId=intval(mysql_real_escape_string($_GET['approve']));
		mysql_query("UPDATE bc_quotes SET active = 1 WHERE id = $quoteId");
		start_page($TheTitle,'Quote approved and is now live on the site.');
	}



}elseif(isset($_GET['random'])){

	start_page($TheTitle.' - Random Quotes','Refresh page to view more random quotes.');

	$getRand=mysql_query("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY RAND() LIMIT {$Num['random']}");

	if(mysql_num_rows($getRand)>0){
		while($Rand=mysql_fetch_assoc($getRand)){
			show_quote($Rand);
		}
	}


}elseif(isset($_GET['top'])){

	start_page($TheTitle.' - Top Rated Quotes');

	$getTop=mysql_query("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY popularity DESC LIMIT {$Num['top']}")or die(mysql_error());

	if(mysql_num_rows($getTop)>0){
		while($Top=mysql_fetch_assoc($getTop)){
			show_quote($Top);
		}
	}



}elseif(isset($_GET['latest'])){

	start_page($TheTitle.' - Latest Quotes');

	$getLatest=mysql_query("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY id DESC LIMIT {$Num['latest']}")or die(mysql_error());

	if(mysql_num_rows($getLatest)>0){
		while($Latest=mysql_fetch_assoc($getLatest)){
			show_quote($Latest);
		}
	}



}elseif(isset($_GET['moderation'])){

	start_page($TheTitle.' - Quotes in Moderation');

	if(isAdmin()){
		$getModeration=mysql_query("SELECT * FROM bc_quotes WHERE active = 0 ORDER BY id ASC LIMIT 100");

		if(mysql_num_rows($getModeration)>0){
			while($Moderation=mysql_fetch_assoc($getModeration)){
				show_quote($Moderation);
			}
		}else{
			echo 'No quotes awaiting moderation =(';
		}
	}



}elseif(empty($_GET)){
	// Display Homepage

	start_page($TheTitle.' - Home','<a href="http://www.screddit.eu/bash.php?chat">irc.quakenet.org #redditeu</a> & <a href="http://www.screddit.eu/bash.php?chat">#starcraft</a><br /><br />');

	$getRand=mysql_query("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY RAND() LIMIT 1");
	$Rand=mysql_fetch_assoc($getRand);
	show_quote($Rand);


}else{
	// Display a Single Quote

	if(isset($_SERVER['QUERY_STRING'])){

		$array=array(0=>'&v=rox',1=>'&v=sux');
		$quoteId=mysql_real_escape_string(str_replace($array,'',$_SERVER['QUERY_STRING']));

		if($_GET['v']=='sux'){

			$ip=$_SERVER['REMOTE_ADDR'];
			$voteCheck=mysql_query("SELECT * FROM bc_votes WHERE quote_id = $quoteId AND ip = '$ip'");

			// Only count vote if not already voted, but display thank you regardless
			if(mysql_num_rows($voteCheck)<1){
				mysql_query("INSERT INTO bc_votes (quote_id,ip,type) VALUES('$quoteId','$ip',1)");
				mysql_query("UPDATE bc_quotes SET popularity = popularity - 1 WHERE id = $quoteId")or die(mysql_error());
			}

			start_page($TheTitle.' - Voted','Thanks for your vote.<br/><br/>');

		}elseif($_GET['v']=='rox'){

			$ip=$_SERVER['REMOTE_ADDR'];
			$voteCheck=mysql_query("SELECT * FROM bc_votes WHERE quote_id = $quoteId AND ip = '$ip'");

			// Only count vote if not already voted, but display thank you regardless
			if(mysql_num_rows($voteCheck)<1){
				mysql_query("INSERT INTO bc_votes (quote_id,ip,type) VALUES('$quoteId','$ip',2)")or die(mysql_error());
				mysql_query("UPDATE bc_quotes SET popularity = popularity + 1 WHERE id = $quoteId")or die(mysql_error());
			}


			start_page($TheTitle.' - Voted','Thank you for your vote!<br/><br/>');

		}else{

			$getQuote=mysql_query("SELECT * FROM bc_quotes WHERE id = '$quoteId'");
			$Quote=mysql_fetch_assoc($getQuote);

			if(empty($Quote) || !isset($Quote)){
				start_page($TheTitle);
			}elseif($Quote['active']==0){
				start_page($TheTitle.' - Quote Pending Approval','Quote Pending Approval');
			}elseif($Quote['active']==1){
				$ReplaceMe=array(0=>'\n',1=>'\r',2=>'\t');
				start_page($TheTitle.' - '.substr(str_replace($ReplaceMe,' ',htmlspecialchars($Quote['quote'])),0,80));
				show_quote($Quote,1);
			}
		}
	}
}



$countQuotes=mysql_query("SELECT COUNT(*) AS count FROM bc_quotes GROUP BY active ORDER BY active DESC");

$Approved=@mysql_result($countQuotes,0,0);
if($Approved<1){
	$Approved=0;
}
$Pending=@mysql_result($countQuotes,1,0);
if($Pending<1){
	$Pending=0;
}

echo smallMenu().'<div class="topMenu">

  <div class="left-element">
  '.$Approved.' quotes approved; '.$Pending.' quotes pending
  </div>
.
  <div class="right-element" style="font-size:11px">
 powered by <a href="http://www.seanbluestone.com/bash-org-clone-php-script-for-irc-quotes">bash.org clone irc quotes php script</a>
  </div>

</div>
</body></html>';

?>
