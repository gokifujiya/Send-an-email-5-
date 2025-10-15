<?php
namespace Response;

use Helpers\Authenticate;

class HTMLRenderer implements HTTPRenderer
{
    private string $view;
    private array $data;

    public function __construct(string $view, array $data = [])
    {
        $this->view = $view;
        $this->data = $data;
    }

    public function getFields(): array
    {
        return ['Content-Type' => 'text/html; charset=UTF-8'];
    }

    private function getViewPath(string $key): string
    {
        return __DIR__ . '/../Views/' . str_replace(['.', '\\'], ['/', '/'], $key) . '.php';
    }

    private function getHeader(): string
    {
        ob_start();
        // Provide $user to the included components
        $user = Authenticate::getAuthenticatedUser();
        require $this->getViewPath('layout/header');
        require $this->getViewPath('component/navigator');
        require $this->getViewPath('component/message-boxes');
        return ob_get_clean();
    }

    private function getFooter(): string
    {
        ob_start();
        require $this->getViewPath('layout/footer');
        return ob_get_clean();
    }

    public function getContent(): string
    {
        // quick inline support
        if (str_starts_with($this->view, 'data:text/html,')) {
            return substr($this->view, strlen('data:text/html,'));
        }

        $viewPath = $this->getViewPath($this->view);
        if (!is_file($viewPath)) {
            http_response_code(500);
            return "View not found: " . htmlspecialchars($viewPath, ENT_QUOTES, 'UTF-8');
        }

        // expose $data keys as variables to the view
        extract($this->data, EXTR_SKIP);

        ob_start();
        echo $this->getHeader();
        require $viewPath;
        echo $this->getFooter();
        return ob_get_clean();
    }

    // kept for older callers
    public function render(): string
    {
        return $this->getContent();
    }
}

