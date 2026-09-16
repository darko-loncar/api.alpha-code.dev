<?php

declare(strict_types=1);

namespace Components;

use RuntimeException;
use Throwable;

class View
{
    private const DEFAULT_BASE_PATH = __DIR__ . '/../App/Views/';

    private static string $baseViewPath = self::DEFAULT_BASE_PATH;

    private ?string $layoutName = null;

    public function __construct(
        private readonly string $viewName,
        private readonly array $variables = [],
    ) {
    }

    /**
     * Change the base directory used for views.
     */
    public static function setBaseViewPath(string $baseViewPath): void
    {
        static::$baseViewPath = rtrim($baseViewPath, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Use a layout for this view.
     */
    public function layout(string $layoutName): self
    {
        $this->layoutName = $layoutName;

        return $this;
    }

    /**
     * Render the view and optionally wrap it in a layout.
     */
    public function getHtml(): string
    {
        $content = $this->renderFile(
            $this->viewName,
            $this->variables
        );

        if ($this->layoutName === null) {
            return $content;
        }

        return $this->renderFile(
            $this->layoutName,
            [
                ...$this->variables,
                'content' => $content,
            ]
        );
    }

    /**
     * Render another view/partial from within a template.
     *
     * Example:
     * <?= $this->partial('partials.nav', ['user' => $user]) ?>
     */
    public function partial(
        string $viewName,
        array $variables = []
    ): string {
        return $this->renderFile(
            $viewName,
            [
                ...$this->variables,
                ...$variables,
            ]
        );
    }

    /**
     * Render a PHP template file.
     */
    private function renderFile(
        string $viewName,
        array $variables = []
    ): string {
        $viewFile = $this->resolveViewFile($viewName);

        extract($variables, EXTR_SKIP);

        ob_start();

        try {
            require $viewFile;

            return ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }
    }

    /**
     * Resolve a view name to an actual PHP file.
     *
     * Example:
     * auth.login
     * becomes:
     * views/auth/login.php
     */
    private function resolveViewFile(string $viewName): string
    {
        $viewFile = static::$baseViewPath
            . str_replace('.', DIRECTORY_SEPARATOR, $viewName)
            . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException(
                "View file not found: {$viewFile}"
            );
        }

        return $viewFile;
    }
}
