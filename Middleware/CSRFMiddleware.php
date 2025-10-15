<?php
namespace Middleware;

use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;

class CSRFMiddleware implements Middleware
{
    public function handle(callable $next): HTTPRenderer
    {
        // セッションにCSRFトークンが存在するかチェックします
        if (empty($_SESSION['csrf_token'])) {
            // 32個のランダムバイトを生成し、16進数に変換してCSRFトークンとしてセッションに格納します
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['GET','HEAD','OPTIONS'], true)) {
            $provided = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
            if (!is_string($provided) || !hash_equals($_SESSION['csrf_token'], $provided)) {
                FlashData::setFlashData('error', 'Access has been denied.');
                return new RedirectRenderer('/login');            }
        }

        return $next();
    }
}
