<?php
namespace Middleware;

use Response\HTTPRenderer;

class MiddlewareA implements Middleware{
    public function handle(callable $next): HTTPRenderer
    {
        // 前処理
        $_SESSION['middleware_test_message'] = ""; // キーのセッションを空にします

        error_log(sprintf("Running Middleware %s Preprocess", self::class));
        $preprocessMessage = "Editing request from middleware {A}! ";
        $_SESSION['middleware_test_message'] = $_SESSION['middleware_test_message'] . $preprocessMessage;

        $response = $next();

        // 後処理
        error_log(sprintf("Running Middleware %s Postprocess", self::class));
        $postprocessMessage = "Editing response from middleware {A}! ";
        $_SESSION['middleware_test_message'] = $_SESSION['middleware_test_message'] . $postprocessMessage;

        return $response;
    }
}
