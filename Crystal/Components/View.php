<?php
declare(strict_types=1);
namespace Crystal\Components;
use RuntimeException;
use Throwable;

class View
{
    private static string $baseViewPath = __DIR__;
    private function __construct(private readonly string $viewName, private readonly array $variables = []) {}

    /**
     * Factory method
     */
    public static function response(string $viewName, array $variables = []): callable
    {
        $instance = new self($viewName, $variables);
        $html = $instance->renderFile($instance->viewName, $instance->variables);
        return function() use ($html) {
            http_response_code(200);
            header("Content-Type: text/html; charset=UTF-8");
            echo $html;
        };
    }

    /**
     * Factory method
     */
    public static function getHtml(string $viewName, array $variables = []): string
    {
        $instance = new self($viewName, $variables);
        return $instance->renderFile($instance->viewName, $instance->variables);
    }

    /**
     * Change the base directory used for views.
     */
    public static function setBaseViewPath(string $baseViewPath): void
    {
        static::$baseViewPath = rtrim($baseViewPath, '/\\') . DIRECTORY_SEPARATOR;
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
     * Render another view/partial from within a template.
     * Example: <?= $this->partial('partials.nav', ['user' => $user]) ?>
     */
    public function partial(string $viewName, array $variables = []): string
    {
        return $this->renderFile($viewName, [...$this->variables, ...$variables]);
    }

    /**
     * Escape value
     */
    public function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Render a PHP template file.
     */
    private function renderFile(string $viewName, array $variables = []): string
    {
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
     * Example: auth.login becomes views/auth/login.php
     */
    private function resolveViewFile(string $viewName): string
    {
        $viewFile = static::$baseViewPath
            . str_replace('.', DIRECTORY_SEPARATOR, $viewName)
            . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException("View file not found: {$viewFile}");
        }

        return $viewFile;
    }
}
