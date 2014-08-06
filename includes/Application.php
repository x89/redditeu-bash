<?php
class Application
{
	protected $pdo;
	protected $controller;
	protected $quotes;
	protected $rootDir;

	public $title;
	public $msg = '';
	public $password;
	public $enableCaptcha;
	public $top;
	public $browsePP;
	public $random;
	public $search;
	public $latest;

	public function __construct(array $config)
	{
		$this->parseConfig($config);
	}

	public function parseConfig(array $config)
	{
		foreach ($config as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}
	}

	public function connectMysql($username, $password, $database)
	{
		$dsn = "mysql:host=localhost;dbname={$database}";
		$this->pdo = new PDO($dsn, $username, $password, [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_CASE               => PDO::CASE_NATURAL,
			PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,
			PDO::ATTR_STRINGIFY_FETCHES  => false,
			PDO::ATTR_EMULATE_PREPARES   => false,
		]);
		$this->quotes = new QuoteManager($this->pdo);
	}

	public function userIsAdmin()
	{
		if (isset($_COOKIE['bc_login']) && base64_decode($_COOKIE['bc_login']) === $this->password){
			return true;
		}

		return false;
	}

	public function run()
	{
		if (!isset($this->controller)) {
			$this->controller = new Controller($this, $this->quotes);
		}

		if (isset($_GET['pass'])) {
			$result = $this->controller->loginAction($_GET['pass']);
		} elseif (isset($_GET['chat'])) {
			$result = $this->controller->chatAction();
		} elseif (isset($_GET['logout'])) {
			$result = $this->controller->logoutAction();
		} elseif (isset($_GET['del'])) {
			$result = $this->controller->deleteAction($_GET['del']);
		} elseif (isset($_GET['del_all'])) {
			$result = $this->controller->deleteAllAction();
		} elseif (isset($_GET['browse'])) {
			$result = $this->controller->browseAction($_GET['browse']);
		} elseif (isset($_GET['search'])){
			$result = $this->controller->searchAction($_GET['search']);
		} elseif (isset($_GET['add'])){
			if (isset($_POST['submit'])) {
				$result = $this->controller->addAction($_POST, $_SERVER['REMOTE_ADDR'], $this->enableCaptcha);
			} else {
				$result = $this->controller->addFormAction($this->enableCaptcha);
			}
		} elseif (isset($_GET['approve'])){
			$result = $this->controller->approveAction($_GET['approve']);
		} elseif (isset($_GET['random'])){
			$result = $this->controller->randomAction();
		} elseif (isset($_GET['top'])){
			$result = $this->controller->topAction();
		} elseif (isset($_GET['latest'])){
			$result = $this->controller->latestAction();
		} elseif (isset($_GET['json'])){
			$result = $this->controller->jsonAction();
		} elseif (isset($_GET['moderation'])) {
			$result = $this->controller->moderationAction();
		} elseif (empty($_GET)){
			$result = $this->controller->indexAction();
		} elseif (isset($_GET['v'])) {
			$result = $this->controller->voteAction($this->getQuoteId($_GET), $_SERVER['REMOTE_ADDR'], $_GET['v']);
		} else {
			$result = $this->controller->showQuoteAction($this->getQuoteId($_GET));
		}

		echo $result;
	}

	protected function getQuoteId($getParams)
	{
		$keys = array_keys($_GET);
		return isset($_GET[0]) ? $_GET[0] : false;
	}

	public function template($name, array $data = array())
	{
		if (!file_exists($path = $this->rootDir.'/templates/'.$name.'.php')) {
			throw new InvalidArgumentException("Template not found: $path");
		}

		$data = array_merge($this->getTemplateGlobals(), $data);

		return static::renderTemplate($path, $data);
	}

	protected static function renderTemplate($path, array $data)
	{
		extract($data);
		ob_start();
		require $path;
		return ob_get_clean();
	}

	protected function getTemplateGlobals()
	{
		if (!isset($this->templateGlobals)) {
			$this->templateGlobals = [
				'title' => $this->title,
				'msg' => $this->msg,
				'enableCaptcha' => $this->enableCaptcha,
				'userIsAdmin' => $this->userIsAdmin(),
				'approved' => $this->quotes->getActiveCount(),
				'pending' => $this->quotes->getPendingCount(),
			];
		}

		return $this->templateGlobals;
	}
}