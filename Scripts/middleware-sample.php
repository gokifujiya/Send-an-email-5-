<?php
interface Middleware{
    public function handle(string $request, Callable $next): string;
}

class MiddlewareA implements Middleware{
    public function handle(string $request, callable $next): string
    {
        // 前処理
        error_log(sprintf("Running Middleware %s Preprocess", self::class));
        $preprocessMessage = "Editing request from middleware {A}! ";
        $request = $request . $preprocessMessage;

        $response = $next($request);

        // 後処理
        error_log(sprintf("Running Middleware %s Postprocess", self::class));
        $postprocessMessage = "Editing response from middleware {A}! ";
        return $response . $postprocessMessage;
    }
}

class MiddlewareB implements Middleware{
    public function handle(string $request, callable $next): string
    {
        // 前処理
        error_log(sprintf("Running Middleware %s Preprocess", self::class));
        $preprocessMessage = "Editing request from middleware {B}! ";
        $request = $request . $preprocessMessage;

        $response = $next($request);

        // 後処理
        error_log(sprintf("Running Middleware %s Postprocess", self::class));
        $postprocessMessage = "Editing response from middleware {B}! ";
        return $response . $postprocessMessage;
    }
}

class MiddlewareC implements Middleware{
    public function handle(string $request, callable $next): string
    {
        // 前処理
        error_log(sprintf("Running Middleware %s Preprocess", self::class));
        $preprocessMessage = "Editing request from middleware {C}! ";
        $request = $request . $preprocessMessage;

        $response = $next($request);

        // 後処理
        error_log(sprintf("Running Middleware %s Postprocess", self::class));
        $postprocessMessage = "Editing response from middleware {C}! ";
        return $response . $postprocessMessage;
    }
}

$middlewares = [
    new MiddlewareA(),
    new MiddlewareB(),
    new MiddlewareC(),
];

$action = fn(string $request): string => $request;

$middlewares = array_reverse($middlewares);

// これは、ネストされた長い関数コールを持つ関数スタックのように動作します。先入れ先出し (FIFO) なので、$middlewares配列を逆順にします。ミドルウェアは、元の順番に従って順次実行されます。
foreach ($middlewares as $middleware){
    $action = fn(string $request) => $middleware->handle($request, $action);
}

// 最初から始めます。
echo $action("") . PHP_EOL;

