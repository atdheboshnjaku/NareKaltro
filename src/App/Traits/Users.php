<?php

declare(strict_types=1);

namespace Fin\Narekaltro\App\Traits;

trait Users
{


	private $user_table = "Users";

	public function getUser(int $id): array
	{

		$sql = "SELECT `name` FROM {$this->user_table}
                WHERE `id` = '" . (int) $id . "'";
		return $this->fetchOne($sql);

	}


}