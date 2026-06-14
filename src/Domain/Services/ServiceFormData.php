<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Services;

final readonly class ServiceFormData
{
	public function __construct(
		public string $name,
		public string $background,
		public string $color,
		public bool $quoteOnly,
	) {
	}

	public static function fromArray(array $input): self
	{
		return new self(
			name: trim((string) ($input['name'] ?? '')),
			background: self::normalizeColor($input['background'] ?? '#f1faff', '#f1faff'),
			color: self::normalizeColor($input['color'] ?? '#009ef7', '#009ef7'),
			quoteOnly: isset($input['quote_only']) && (string) $input['quote_only'] === '1',
		);
	}

	public function validate(): array
	{
		$errors = [];

		if ($this->name === '') {
			$errors['name'] = 'Service name is required.';
		}

		if (!$this->isHexColor($this->background)) {
			$errors['background'] = 'Background must be a valid hex color.';
		}

		if (!$this->isHexColor($this->color)) {
			$errors['color'] = 'Text color must be a valid hex color.';
		}

		return $errors;
	}

	public function toDatabaseRow(): array
	{
		return [
			'name' => $this->name,
			'background' => $this->background,
			'color' => $this->color,
			'quote_only' => $this->quoteOnly ? 1 : 0,
		];
	}

	private static function normalizeColor(mixed $value, string $fallback): string
	{
		$value = strtolower(trim((string) $value));

		return $value === '' ? $fallback : $value;
	}

	private function isHexColor(string $value): bool
	{
		return (bool) preg_match('/^#[0-9a-f]{6}$/i', $value);
	}
}
