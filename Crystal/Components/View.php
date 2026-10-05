<?php
declare(strict_types=1);
namespace Crystal\Components;
use Throwable;

final class View
{
    private static string $baseViewPath = __DIR__."/Views/";

    /**
     * Change the base directory used for views.
     */
    public static function setBaseViewPath(string $baseViewPath): void
    {
        static::$baseViewPath = rtrim($baseViewPath, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Factory method
     * @param string $viewName
     * @param array $variables
     */
    public static function render(string $viewName, array $variables = []): string
    {
        $instance = new self($viewName, $variables);
        return $instance->renderFile($instance->viewName, $instance->variables);
    }

    /**
     * Constructor
     * @param string $viewName
     * @param array $variables
     */
    private function __construct(private readonly string $viewName, private readonly array $variables = [])
    {
    }

    /**
     * Render a PHP template file.
     */
    private function renderFile(string $viewName, array $variables = []): string
    {
        $viewFile = static::$baseViewPath . str_replace('.', DIRECTORY_SEPARATOR, $viewName) . '.php';
        if (!is_file($viewFile)) throw new \RuntimeException("View file not found: {$viewFile}");

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
     * Render another view/partial from within a template.
     * Example: <?= $this->partial('partials.nav', ['user' => $user]) ?>
     */
    public function partial(string $viewName, array $variables = []): string
    {
        return $this->renderFile($viewName, [...$this->variables, ...$variables]);
    }

    /**
     * Retrieve a variable passed to the view.
     *
     * @template T
     * @param string $key
     * @param T|null $default
     * @return mixed|T
     */
    public function getVar(string $key, mixed $default = null): mixed
    {
        return $this->variables[$key] ?? $default;
    }

    /**
     * Escape value
     */
    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

}
