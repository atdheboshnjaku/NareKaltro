<?php

declare(strict_types=1);

namespace Fin\Narekaltro\App;

class User extends Database
{

	protected $table = "Users";
	private $table_2 = "User_Roles";
	private $table_3 = "User_Tokens";

	private $table_4 = "Business_Locations";

	public int $id;

	public function getColumnName(): array
	{

		return $this->getTableColumnName($this->table);
	}

	public function authenticate(string $email, string $password, bool $remember = false): bool
	{

		if (!empty($email) && !empty($password)) {

			$sql = "SELECT * FROM {$this->table}
					WHERE `email` = '" . $this->escape($email) . "'";
			$user = $this->fetchOne($sql);
			if ($user) {
				if (password_verify($password, $user["password"])) {
					$this->id = (int) $user['id'];
					//$this->id = $user['id'];
					$_SESSION['username'] = $user['name'];
					$_SESSION['userId'] = $this->id;
					//$_SESSION['userId'] = $user['id'];
					if ($remember) {
						$this->rememberMe($this->id);
					}
					return true;
				} else {
					return false;
				}
			}
			return false;
		}
		return false;
	}

	public function rememberMe(int $userID, int $day = 90): void
	{

		[$selector, $validator, $token] = $this->generateTokens();

		$this->deleteUserToken($userID);

		$expiredSeconds = time() + 60 * 60 * 24 * $day;

		$hashValidator = password_hash($validator, PASSWORD_DEFAULT);
		$expiry = date('Y-m-d H:i:s', $expiredSeconds);

		if ($this->insertUserToken($userID, $selector, $hashValidator, $expiry)) {
			setcookie('remember_me', $token, $expiredSeconds);
		}
	}

	public function createUser(array $args = null, ?string $password = null): bool
	{

		if (!empty($args)) {
			$args["date"] = date("Y-m-d H:i:s");
			$args["hash"] = $this->generateHash();
			$this->prepareToInsert($args);
			if ($this->insert($this->table)) {
				return true;
			}
			return false;
		}
		return false;
	}

	public function registerUser(array $args = null): bool
	{

		if (!empty($args)) {
			$this->prepareToInsert($args);
			if ($this->insert($this->table)) {
				if ($this->sendEmail($args['email'], $args['hash'])) {

					return true;
				}
			}
			return false;
		}
		return false;
	}

	public function generateHash(): string
	{

		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$randomHash = '';
		$length = 8;

		for ($i = 0; $i < $length; $i++) {
			$index = rand(0, strlen($characters) - 1);
			$randomHash .= $characters[$index];
		}

		return $randomHash;
	}

	public function verifyHash(string $hash): ?array
	{

		if (!empty($hash)) {
			if ($this->hashExists($hash)) {
				return $this->hashExists($hash);
			}
			return false;
		}
	}

	public function deleteHash(int $id): bool
	{

		if (!empty($id)) {

			$args['hash'] = '';
			$this->prepareToUpdate($args);
			if ($this->update($this->table, $id)) {
				return true;
			}
		}
	}

	public function generateTokens(): array
	{

		$selector = bin2hex(random_bytes(16));
		$validator = bin2hex(random_bytes(32));

		return [$selector, $validator, $selector . ':' . $validator];
	}

	public function insertUserToken(int $userID, string $selector, string $hashValidator, string $expiry): bool
	{

		$params = [
			"user_id"          => $userID,
			"selector"         => $selector,
			"hashed_validator" => $hashValidator,
			"expiry"           => $expiry
		];
		$this->prepareToInsert($params);
		if ($this->insert($this->table_3)) {
			return true;
		}
		return false;
	}

	// public function getUserTokenBySelector(string $selector): ?array
	// {

	//     $sql = "SELECT *
	//             FROM $this->table_3
	//             -- WHERE `selector` = '". $this->escape($selector) ."'
	//             WHERE `selecto` = '$selector'
	//             AND `expiry` >= NOW()
	//             LIMIT 1";

	//         return $this->fetchOne($sql);

	// }

	public function deleteUserToken(int $userID): bool
	{

		$sql = "DELETE FROM $this->table_3
                WHERE `user_id` = $userID";

		return $this->query($sql);
	}

	// public function getUserByToken(string $token): ?array
	// {

	//     $tokens = $this->parseToken($token);
	//     if(!$tokens) {
	//         return null;
	//     }

	//     $sql = "SELECT `Users.id`, `name`
	//             FROM $this->table
	//             INNER JOIN $this->table_3 ON `user_id` = Users.id
	//             WHERE `selector` = '". $this->escape($tokens[0]) ."'
	//             AND expiry > NOW()
	//             LIMIT 1";

	//         return $this->fetchOne($sql);


	// }

	// public function tokenIsValid(string $token): bool
	// {

	//     [$selector, $validator] = $this->parseToken($token);
	//     $tokens = $this->getUserTokenBySelector($selector);
	//     if(!$tokens) {
	//         return false;
	//     }

	//     return password_verify($validator, $tokens['hashed_validator']);

	// }

	public function verifyUser(array $args, string $password, string $hash): ?bool
	{

		if (!empty($args) && !empty($password) && !empty($hash)) {

			$user = $this->hashExists($hash);
			if ($user) {
				$id = $user['id'];
				$args['status'] = "1";
				$this->prepareToUpdate($args);
				if ($this->update($this->table, $id)) {
					return $this->authenticate($user['email'], $password);
				}
			}
		}
	}

	public function hashExists(string $hash): array|null
	{

		$sql = "SELECT `id`, `email`, `hash` FROM {$this->table}
                WHERE `hash` = '" . $this->escape($hash) . "'";

		return $this->fetchOne($sql);
	}

	public function hashVerified(string $hash): bool
	{

		if (!empty($hash)) {
			$sql = "SELECT `hash` FROM $this->table
                    WHERE `hash` = '" . $this->escape($hash) . "' AND `status` = 1";
			$result = $this->fetchOne($sql);
			if ($result) {
				return true;
			}
			return false;
		}
	}

	public function sendEmail(string $email, string $hash): bool
	{

		$to = $email;
		$subject = 'Please verify your account';
		$message = '
                    <html>
                    <head>
                        <title>Please verify email</title>
                    </head>
                    <body>
                        <h1>Verify account by clicking on the link below</h1><br>
                        <p><a href="https://fin.narekaltro.com/verify?hash=' . $hash . '">Verify Email</a></p>
                        <p>If the link above does not work, please visit this url to verify: <br>
                        https://fin.narekaltro.com/verify?hash=' . $hash . '
                        </p>
                    </body>
                    </html>';
		$headers = 'MIME-Version: 1.0' . "\r\n" .
			'Content-Type: text/html; charset=UTF-8' . "\r\n" .
			'From: noreply@narekaltro.com' . "\r\n" .
			'Reply-To: noreply@narekaltro.com' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();

		if (mail($to, $subject, $message, $headers)) {
			return true;
		}
	}

	public function getUserAccountID(int $id): string
	{

		$user = $this->getUser($id);
		return $user['account_id'];
	}

	public function updateUser(array $args, int $id): bool
	{

		if (!empty($args) && !empty($id)) {
			$this->prepareToUpdate($args);
			return $this->update($this->table, $id);
		}
	}

	public function getCreatedUserID(): int
	{

		return $this->lastId();
	}

	public function getUserByEmail(string $email, int $id = null): array|null
	{
		if (!empty($email)) {
			$sql = "SELECT `id` FROM {$this->table}
                    WHERE `email` = '" . $this->escape($email) . "' ";
			if (!empty($id)) {
				$sql .= "AND NOT `id` = '" . (int) $id . "'";
			}
			return $this->fetchOne($sql);
		}
	}


	public function getUser(int $id): ?array
	{

		$sql = "SELECT * FROM {$this->table}
                 WHERE `id` =" . (int) $id;
		return $this->fetchOne($sql);
	}

	public function getUsers(int $id, string $accountID): array
	{

		$sql = "SELECT * FROM {$this->table}
                WHERE `status` = 1
                AND `role_id` > 1
                AND `account_id` = '" . $this->escape($accountID) . "'
                AND NOT `id` = '" . (int) $id . "'";
		return $this->fetchAll($sql);
	}

	public function getUserLocationID(int $id): ?array
	{

		$sql = "SELECT `location_id` FROM $this->table
                WHERE `id` = '" . (int) $id . "'";
		return $this->fetchOne($sql);
	}

	public function getClients(string $accountID): array
	{

		$sql = "SELECT * FROM {$this->table}
                WHERE `status` = 1
                AND `role_id` = 1
                AND `account_id` = '" . $this->escape($accountID) . "'
                AND NOT `account_id` = 0";
		return $this->fetchAll($sql);
	}

	public function getClient(string $value): array
	{

		$columnName = $this->getColumnName();
		$column = $columnName['COLUMN_NAME'];
		return $this->getRecordFromTableColumnValue($this->table, $column, $value);
		// $sql = "SELECT * FROM {$this->table}
		//         WHERE `".$this->escape($columnName['COLUMN_NAME'])."` = '". (int)$id ."'";
		//         return $this->fetchOne($sql);

	}

	public function getClientCountryById(int $id): array
	{

		$columnName = $this->getColumnName();
		$sql = "SELECT `country` FROM {$this->table}
               WHERE `{$this->escape($columnName['COLUMN_NAME'])}` = '" . (int) $id . "'";
		return $this->fetchOne($sql);
	}

	public function getClientInitials(string $fullname): string
	{

		$safeFullname = [];
		$safeFullname[] = $this->escape($fullname);
		$initials = implode('', array_map(function ($name) {
			preg_match_all('/\b\w/', $name, $matches);
			return implode('', $matches[0]);
		}, $safeFullname));
		return $initials;
	}

	public function getUsername(int $id): array
	{

		$columnName = $this->getColumnName();
		$sql = "SELECT `name` FROM {$this->table}
                WHERE `{$this->escape($columnName['COLUMN_NAME'])}` = '" . (int) $id . "'";

		return $this->fetchOne($sql);
	}

	public function deleteUser(int $id): bool
	{

		if (!empty($id)) {
			if ($this->deleteRecord($this->table, $id)) {
				return true;
			}
		}
	}

	public function removeUser(int $id): bool
	{

		if (!empty($id)) {
			if ($this->deactivateUser($this->table, $id)) {
				return true;
			}
		}
	}

	public function userCount(string $accountID, int $userID): array
	{

		$sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE `status` = 1
                AND `role_id` > 1
                AND `account_id` = '" . $this->escape($accountID) . "'
                AND NOT `status` = 0
                AND NOT `id` = '" . $this->escape($userID) . "'";
		return $this->fetchOne($sql);
	}

	public function clientCount(string $accountID): array
	{

		$sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE `status` = 1
                AND `role_id` = 1
                AND `account_id` = '" . $this->escape($accountID) . "'
                AND NOT `account_id` = 0";
		return $this->fetchOne($sql);
	}

	public function employeeLocationCountById(int $id, string $accountID): array
	{

		if (!empty($id)) {
			$sql = "SELECT COUNT(*) FROM {$this->table}
                    WHERE `location_id` = '" . (int) $id . "'
                    AND `status` = 1
                    AND `role_id` > 1
                    AND `account_id` = '" . $this->escape($accountID) . "'
                    AND NOT `status` = 0";
		}
		return $this->fetchOne($sql);
	}

	public function getUserLevelName(int $id): array
	{

		if (!empty($id)) {
			$sql = "SELECT `name` FROM {$this->table_2}
                    WHERE `level` = '" . (int) $id . "'";
			return $this->fetchOne($sql);
		}
	}

	public function clientLocationCountById(int $id, string $accountID): array
	{

		if (!empty($id)) {
			$sql = "SELECT COUNT(*) FROM {$this->table}
                    WHERE `location_id` = '" . (int) $id . "'
                    AND `role_id` = 1
                    AND `status` = 1
                    AND `account_id` = '" . $this->escape($accountID) . "'
                    AND NOT `status` = 0";
			return $this->fetchOne($sql);
		}
	}

	public function checkUserHasThisLocation(int $id, string $accountID): bool
	{

		if (!empty($id)) {
			$sql = "SELECT `location_id` FROM {$this->table}
                    WHERE `location_id` = '" . (int) $id . "'
                    AND `status` = 1
                    AND `account_id` = '" . $this->escape($accountID) . "'";
			$result = $this->fetchAll($sql);
			if ($result) {
				return true;
			}
		}
		return false;
	}

	public function getUserRoles(): array
	{

		$sql = "SELECT * FROM {$this->table_2}
                WHERE `level` > 1";
		return $this->fetchAll($sql);
	}

	public function searchClients(string $accountID, string $searchParameter): array
	{

		$sql = "
		SELECT
		  id,
		  name,
		  email,
		  location_id
		FROM {$this->table}
		WHERE account_id = '" . $this->escape($accountID) . "'
		AND (name LIKE '%" . $this->escape($searchParameter) . "%' OR email LIKE '%" . $this->escape($searchParameter) . "%')";
		//var_dump($sql);
		$clients = $this->fetchAll($sql);
		//var_dump($clients);
		$results = [];

		foreach ($clients as $client) {
			$initials = $this->getClientInitials($client['name']);
			$location = $this->getLocationById((int)$client['location_id']);

			$results[] = [
				'id'       => $client['id'],
				'name'     => $client['name'],
				'email'    => $client['email'] ?? '',
				'initials' => $initials,
				'location' => $location['name'] ?? ''
			];
		}
		//var_dump($results);die;
		return $results;
	}

	public function getLocationById(int $id)
	{

		if (!empty($id)) {
			$sql = "SELECT `name` FROM {$this->table} WHERE `id` = '" . (int) $id . "'";
			return $this->fetchOne($sql);
		}

	}
}
