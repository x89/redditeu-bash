<?php
class Controller
{
	protected $app;
	protected $quotes;

	public function __construct(Application $app, QuoteManager $quotes)
	{
		$this->app = $app;
		$this->quotes = $quotes;
	}

	public function loginAction($password)
	{
		if ($password == $this->app->password) {
			setcookie('bc_login', base64_encode($password), time() + 60 * 60 * 24 * 90); // 90 days
			$this->app->msg = 'Vilkommen administrator, you are now logged in and can browse to delete quotes.<br><br>';
		}

		return $this->indexAction();
	}

	public function chatAction()
	{
		return $this->app->template('chat');
	}

	public function logoutAction()
	{
		setcookie('bc_login', '', time() - 3600);
		$this->app->msg = 'You have been logged out. Cheerio!';

		return $this->indexAction();
	}

	public function deleteAction($id)
	{
		$this->quotes->delete($id);
		$this->app->msg = 'Quote deleted squire.';

		return $this->indexAction();
	}

	public function deleteAllAction()
	{
		$this->quotes->deleteAll();
		$this->app->msg = 'Quote deleted squire.';

		return $this->indexAction();
	}

	public function browseAction($page)
	{
		$page = intval($page) - 1;

		if ($page < 1) {
			$page = 0;
		}

		$perPage = $this->app->browsePP;

		$total = $this->quotes->getActiveCount();

		for ($x = 0; $x < intval($total/$perPage)+1; $x++) {
			$pagesString .= '<a href="?browse='.($x + 1).'">'.($x+1).'</a> - ';
		}

		$pagesString = substr($pagesString,0,-3);

		$start = $page * $perPage;
		$end = $page + $perPage;

		$quotes = $this->quotes->getActive($start, $end);

		return $this->app->template('list', [
			'quotes' => $quotes,
			'pagesString' => $pagesString,
		]);
	}

	public function searchAction($searchFor)
	{
		if (strlen($searchFor) < 1) {
			return $this->indexAction();
		}

		$form = '<br><form method="GET" action="'.$_SERVER['REQUEST_URI'].'">
			Search for: <input type="text" name="search" value="'.htmlspecialchars($searchFor).'">
			<input type="submit" name="submit" value="Search">
			</form>';

		return $this->app->template('list', [
			'quotes' => $quotes,
			'searchForm' => $form,
		]);
	}

	public function addFormAction($enableCaptcha)
	{
		return $this->app->template('add', [
			'enableCaptcha' => $enableCaptcha,
		]);
	}

	public function addAction(array $data, $ip, $checkCaptcha)
	{
		$valid = true;

		$quote = (string) $data['quote'];

		if (isset($data['strip']) && $data['strip'] == 'on') {
			$quoteSplode = explode('\n', $quote);
			foreach ($quoteSplode as $line => $value){
				$temp = strpos($value, '<');

				if ($temp === false){
					$quoteStripped .= $value;
				} else {
					$quoteStripped .= substr($value, $temp);
				}
			}

			// badfurday space?
			$quoteStripped = str_replace(' ', ' ', $quoteStripped);

			$quote = $quoteStripped;
		}

		if (strlen($quote) < 6) {
			$this->app->msg = 'Quote must be at least 6 characters.';
			$valid = false;
		}

		if ($checkCaptcha) {
			$privatekey = "6Lc8Q8ESAAAAADAgiufKhG7J8vlTJnXMsHrAtOww";
			$resp = recaptcha_check_answer($privatekey, $ip, $data["recaptcha_challenge_field"], $data["recaptcha_response_field"]);

			if (!$resp->is_valid) {
				$this->app->msg = "The reCAPTCHA wasn't entered correctly. Try it again.";
				$valid = false;
			}
		}

		if ($valid) {
			$lastId = $this->quotes->add($ip, $quote);
			$this->app->msg = '<strong>Quote added as <a href="?'.$lastId.'">#'.$lastId.'</a>. Thanks for participating :-)</strong><br />';
			return $this->indexAction();
		} else {
			return $this->addFormAction();
		}
	}

	public function approveAction($id)
	{
		if ($this->app->userIsAdmin()) {
			$this->quotes->approve($id);
			$this->app->msg = 'Quote approved.';
		}

		return $this->indexAction();
	}

	public function randomAction()
	{
		$quotes = $this->quotes->getRandom();

		return $this->app->template('list', [
			'quotes' => $quotes,
		]);
	}

	public function topAction()
	{
		$quotes = $this->quotes->getTop();

		return $this->app->template('list', [
			'quotes' => $quotes,
		]);
	}

	public function latestAction()
	{
		$quotes = $this->quotes->getLatest();

		return $this->app->template('list', [
			'quotes' => $quotes,
		]);
	}

	public function jsonAction()
	{
		$quotes = $this->quotes->getAll();

		return json_encode($quotes);
	}

	public function moderationAction()
	{
		$quotes = $this->quotes->getPending();

		return $this->app->template('list', [
			'quotes' => $quotes,
		]);
	}

	public function indexAction()
	{
		return $this->randomAction();
	}

	public function voteAction($id, $ip, $vote)
	{
		$this->quotes->vote($ip, $id, $vote == 'rox');
		$this->app->msg('Thanks for your vote!');

		return $this->indexAction();
	}

	public function showQuoteAction($id)
	{
		$quote = $this->quotes->getById($id);

		if (!$quote) {
			// redirect?
		}

		if (!$quote['active']) {
			$this->app->msg = 'Quote pending approval';
		}

		return $this->app->template('single', ['quote' => $quote]);
	}
}
