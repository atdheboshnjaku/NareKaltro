<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Core;

use RuntimeException;

final class View
{
	public function __construct(private string $viewPath)
	{
	}

	public function render(string $template, array $data = [], ?string $layout = 'layouts/app'): string
	{
		$content = $this->renderFile($template, $data);

		if ($layout === null) {
			return $content;
		}

		return $this->renderFile($layout, array_merge($data, ['content' => $content]));
	}

	private function renderFile(string $template, array $data): string
	{
		$file = $this->viewPath . '/' . str_replace('.', '/', $template) . '.php';

		if (!is_file($file)) {
			throw new RuntimeException("View [{$template}] was not found.");
		}

		extract($data, EXTR_SKIP);

		ob_start();
		require $file;

		return (string) ob_get_clean();
	}
}
