<?php
class Controller
{
	protected $app;
	protected $quotes;
	protected $json = false;

	public function __construct(Application $app, QuoteManager $quotes)
	{
		$this->app = $app;
		$this->quotes = $quotes;
	}

	public function loginAction($password)
	{
		if ($password == $this->app->password) {
			$days = 90;
			$expires = time() + 60 * 60 * 24 * $days;
			setcookie('bc_login', base64_encode($password), $expires);
		}

		// get the url without query string
		$url = strtok($_SERVER["REQUEST_URI"], '?');
		header('HTTP/1.0 302 Found');
		header('Location: ' . $url);
		return 'Redirecting to ' . $url;
	}

	public function chatAction()
	{
		return $this->app->template('chat');
	}

	public function logoutAction()
	{
		setcookie('bc_login', '', time() - 3600);

		header('HTTP/1.0 302 Found');
		header('Location: ' . $url);
		return 'Redirecting to ' . $url;
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

		$pagesString = '';

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
		if (strlen($searchFor) > 0) {
			$quotes = $this->quotes->getBySearch($searchFor, $this->app->search);
		} else {
			$quotes = [];
		}

		$form = '<form method="GET" action="'.$_SERVER['REQUEST_URI'].'">
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

	public function addAction(array $data, $ip, $checkCaptcha, $captchaKey)
	{
		$valid = true;

		$quote = (string) $data['quote'];

		if (isset($data['strip']) && $data['strip'] == 'on') {
			$quoteStripped = '';
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
			$resp = recaptcha_check_answer($captchaKey, $ip, $data["recaptcha_challenge_field"], $data["recaptcha_response_field"]);

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
			return $this->addFormAction($checkCaptcha);
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
		$quotes = $this->quotes->getRandom($this->app->random);

		return $this->app->template('list', [
			'quotes' => $quotes,
		]);
	}

	public function topAction()
	{
		$quotes = $this->quotes->getTop($this->app->top);

		return $this->app->template('list', [
			'quotes' => $quotes,
		]);
	}

	public function latestAction()
	{
		$quotes = $this->quotes->getLatest($this->app->latest);

		return $this->app->template('list', [
			'quotes' => $quotes,
		]);
	}

	public function jsonAction()
	{
		$this->json = true;
		$quotes = $this->quotes->getAll();

		array_walk($quotes, function(&$quote) {
			$quote['quote'] = stripslashes($quote['quote']);
		});

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
		$this->app->msg = 'Thanks for your vote!';

		return $this->showQuoteAction($id);
	}

	public function showQuoteAction($id)
	{
		$quote = $this->quotes->find($id);

		if (!$quote) {
			// redirect?
		}

		if (!$quote['active']) {
			$this->app->msg = 'Quote pending approval';
		}

		return $this->app->template('single', [
			'quote' => $quote,
		]);
	}

	public function sendResponse($body)
	{
		if ($this->json) {
			header('Content-type: application/json');
		} else {
			header('Content-type: text/html; charset=utf-8');
		}

		echo $body;
	}
}
