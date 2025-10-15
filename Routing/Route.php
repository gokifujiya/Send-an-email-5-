<?php
namespace Routing;

use Closure;

class Route
{

    private string $path;
    /**
     * @var string[]
     */
    private array $middleware;
    private Closure $callback;

    public function __construct(string $path, callable $callback){
        $this->path = $path;
        // これは、Closure::fromCallable($callable) の代替構文です
        // クロージャはコールバックをクラスにカプセル化し、そのスコープをカプセル化します。元のスコープの変数を使用するにはuseキーワードを使用します
        $this->callback = $callback(...);
    }

    // ルートを作成するための静的関数
    // (new Route($callback))->setMiddleware(...)の代わりに、Route::create($callback)->setMiddleware(...)を実行できます
    public static function create(string $path, callable $callback): Route{
        return new self($path, $callback);
    }

    public function setMiddleware(array $middleware): Route{
        $this->middleware = $middleware;
        return $this;
    }

    public function getMiddleware(): array{
        return $this->middleware ?? [];
    }

    public function getCallback(): Closure
    {
        return $this->callback;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
