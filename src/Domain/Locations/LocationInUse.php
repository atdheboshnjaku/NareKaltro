<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Locations;

use RuntimeException;

final class LocationInUse extends RuntimeException
{
	public function __construct()
	{
		parent::__construct('You cannot remove a location that has users assigned to it.');
	}
}
