<?php
class QuoteManager
{
	protected $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function find($id)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM bc_quotes WHERE id = :id LIMIT 1");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getAll()
	{
		$stmt = $this->pdo->prepare("SELECT id, quote, popularity FROM bc_quotes");
		$stmt->execute();

		return $stmt->fetchAll();
	}

	public function getActive($start, $end)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY id ASC LIMIT :start, :end");
		$stmt->bindParam(':start', $start, PDO::PARAM_INT);
		$stmt->bindParam(':end', $end, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll();
	}

	public function getPending()
	{
		$stmt = $this->pdo->prepare("SELECT * FROM bc_quotes WHERE active = 0 ORDER BY id ASC LIMIT 100");
		$stmt->execute();

		return $stmt->fetchAll();
	}

	public function getBySearch($searchFor, $limit)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM bc_quotes WHERE active = 1 AND quote LIKE :search ORDER BY id ASC LIMIT :limit");
		$stmt->bindParam(':search', "%{$searchFor}%");
		$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
		$stmt->execute();
		
		return $stmt->fetchAll();
	}

	public function getTop($limit)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY popularity DESC LIMIT :limit");
		$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll();
	}

	public function getLatest($limit)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY timestamp DESC LIMIT :limit");
		$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll();
	}

	public function getRandom($limit)
	{
		$stmt = $this->pdo->prepare("SELECT * FROM bc_quotes WHERE active = 1 ORDER BY RAND() LIMIT :limit");
		$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
		$stmt->execute();
		
		return $stmt->fetchAll();
	}

	public function getActiveCount()
	{
		$stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM bc_quotes WHERE active = 1");
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		return $result['count'];
	}

	public function getPendingCount()
	{
		$stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM bc_quotes WHERE active = 0");
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		return $result['count'];
	}

	public function add($ip, $quote)
	{
		$timestamp = time();
		$stmt = $this->pdo->prepare("INSERT INTO bc_quotes (timestamp, ip, quote, active) VALUES(:timestamp, :ip, :quote, 0)");
		$stmt->bindParam(':timestamp', $timestamp);
		$stmt->bindParam(':ip', $ip);
		$stmt->bindParam(':quote', $quote);
		$stmt->execute();

		return $this->pdo->lastInsertId();
	}

	public function approve($id)
	{
		$stmt = $this->pdo->prepare("UPDATE bc_quotes SET active = 1 WHERE id = :id");
		$stmt->bindParam(':id', $id);
		$stmt->execute();
	}

	public function delete($id)
	{
		$stmt = $this->pdo->prepare("DELETE FROM bc_quotes WHERE id = :id LIMIT 1");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();

		$stmt = $this->pdo->prepare("DELETE FROM bc_votes WHERE quote_id = :id LIMIT 1");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
	}

	public function deleteAll()
	{
		$stmt = $this->pdo->prepare("DELETE FROM bc_quotes WHERE active = 0");
		$stmt->execute();
	}

	public function vote($ip, $quoteId, $voteIsPositive)
	{
		$stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM bc_votes WHERE quote_id = :id AND ip = :ip");
		$stmt->bindParam(':id', $quoteId);
		$stmt->bindParam(':ip', $ip);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($result['count'] > 0) {
			// user has already voted
			return;
		}

		$stmt = $this->pdo->prepare('INSERT INTO bc_votes (quote_id, ip, type) VALUES(:id, :ip, :type)');
		$stmt->bindParam(':id', $quoteId);
		$stmt->bindParam(':ip', $ip);
		// '2' corresponds to +1 -- '1' corresponds to -1
		$stmt->bindValue(':type', $voteIsPositive ? '2' : '1');
		$stmt->execute();

		$popularityOperator = $voteIsPositive ? '+' : '-';
		$stmt = $this->pdo->prepare("UPDATE bc_quotes SET popularity = popularity {$popularityOperator} 1 WHERE id = :id");
		$stmt->bindParam(':id', $quoteId);
		$stmt->execute();
	}
}