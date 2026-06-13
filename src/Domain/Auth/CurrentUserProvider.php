<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

interface CurrentUserProvider
{
	public function user(): ?AuthenticatedUser;
}
