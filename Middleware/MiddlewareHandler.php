<?php
namespace Middleware;

use Response\HTTPRenderer;

class MiddlewareHandler
{
    /**
     * @param Middleware[] $middlewares
     */
    public function __construct(private array $middlewares) {}

    public function run(callable $action): HTTPRenderer
    {
        // Build a nested chain so the first middleware runs first.
        $stack = array_reverse($this->middlewares);
        foreach ($stack as $mw) {
            $action = fn() => $mw->handle($action);
        }
        return $action();
    }
}
