<?php
declare(strict_types=1);
namespace Components;

/**
 * Class View
 *
 * Responsible for rendering a view with a set of variables.
 * The view can be dynamically resolved and rendered based on the base view path.
 */
class View
{
    /**
     * Default base view path. This is used if no custom base path is provided.
     * @var string
     */
    private const DEFAULT_BASE_PATH = __DIR__ . '/../views/';

    /**
     * Static property to store the custom base view path.
     * @var string
     */
    private static string $baseViewPath = self::DEFAULT_BASE_PATH;

    /**
     * Name of the view to be rendered.
     * @var string
     */
    private readonly string $viewName;

    /**
     * Variables to be passed to the view.
     * @var array
     */
    private readonly array $variables;

    /**
     * Sets the base path for the views.
     * This allows you to override the default view path for rendering views.
     * @param string $baseViewPath Custom base path to the views folder.
     */
    public static function setBaseViewPath(string $baseViewPath) 
    {
        static::$baseViewPath = rtrim($baseViewPath, '/') . '/';    
    }

    /**
     * View constructor.
     * @param string $viewName The name of the view file to be rendered.
     * @param array $variables Optional. An associative array of data to be passed to the view.
     */
    public function __construct(string $viewName, array $variables = [])
    {
        $this->viewName = $viewName;
        $this->variables = $variables;
    }

    /**
     * Gets the rendered content of the view.
     * This method loads the view file, extracts the variables, and returns the rendered HTML.
     * @throws \Exception If the view file does not exist.
     */
    public function getHtml()
    {
        // Capture the view output into a buffer and return the rendered content
        ob_start();
        $this->includeTemplatePart($this->viewName); //require $viewFile;
        return ob_get_clean();
    }

    /**
     * Include a template part file from the views folder
     * @param string $templatePart The name of the template part to include
     * @param array $variables Variables to be passed to the included template
     * @return void
     */
    private function includeTemplatePart(string $templatePart): void
    {
        // Get the template part file path and extract the variables
        $viewFile = $this->resolveViewFile($templatePart);
        $variables = $this->variables;
        extract($variables, EXTR_SKIP);

        // Include the template part
        require $viewFile;
    }

    /**
     * Resolve the full file path for a view or template part.
     * @param string $viewName The name of the view or template part.
     * @return string The resolved file path.
     */
    private function resolveViewFile(string $viewName): string
    {
        $baseViewPath = static::$baseViewPath;

        // Resolve the full file path
        $viewFile = $baseViewPath . str_replace('.', '/', $viewName) . '.php';

        // Ensure the file exists
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: $viewFile");
        }

        return $viewFile;
    }
}
