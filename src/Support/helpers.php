<?php

declare(strict_types=1);

use Fin\Narekaltro\Core\Csrf;

if (!function_exists('e')) {
	function e(mixed $value): string
	{
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	}
}

if (!function_exists('csrf_token')) {
	function csrf_token(): string
	{
		return Csrf::token();
	}
}

if (!function_exists('csrf_field')) {
	function csrf_field(): string
	{
		return '<input type="hidden" name="' . e(Csrf::fieldName()) . '" value="' . e(Csrf::token()) . '">';
	}
}
