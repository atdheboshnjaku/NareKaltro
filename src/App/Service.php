<?php

declare(strict_types=1);

namespace Fin\Narekaltro\App;

class Service extends Database
{

	private string $table = "Services";

	public function getServices(string $accountID): array
	{

		$sql = "SELECT * FROM {$this->table}
                WHERE `status` = 1
                AND `account_id` = '" . $this->escape($accountID) . "'";
		return $this->fetchAll($sql);

	}

	public function serviceCount(string $accountID): array
	{

		$sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE `status` = 1
                AND `account_id` = '" . $this->escape($accountID) . "'";
		return $this->fetchOne($sql);

	}

	public function getServiceByName(string $name, int $id = null): ?array
	{

		if (!empty($name)) {
			$sql = "SELECT * FROM {$this->table}
                    WHERE `name` = '" . $this->escape($name) . "' AND `status` > 0 ";
			if (!empty($id)) {
				$sql .= "AND NOT `id` = '" . (int) $id . "'";
			}
			return $this->fetchOne($sql);
		}

	}

	public function deleteService(int $id): bool
	{

		return $this->deactivateService($this->table, $id);

	}

	public function getServiceById(int $id): array
	{

		$sql = "SELECT * FROM {$this->table}
                WHERE `id` = '" . (int) $id . "'";
		return $this->fetchOne($sql);

	}

	public function createService(array $args): bool
	{

		if (!empty($args)) {
			$this->prepareToInsert($args);
			if ($this->insert($this->table)) {
				return true;
			}
			return false;
		}
		return false;

	}

	public function updateService(array $args, int $id): bool
	{

		if (!empty($args) && !empty($id)) {
			$this->prepareToUpdate($args);
			return $this->update($this->table, $id);
		}

	}











}







