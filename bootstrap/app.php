<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

if (!class_exists(\DOMDocument::class)) {
    class DOMNode
    {
        public int $nodeType = 1;
        public string $nodeName = 'body';
        public string $nodeValue = '';
        public ?DOMNode $parentNode = null;
        public ?DOMNode $previousSibling = null;
        public ?DOMNode $nextSibling = null;
        public ?DOMNode $firstChild = null;
        public ?DOMNode $lastChild = null;
        /** @var array<mixed> */
        public array $childNodes = [];
        
        public function __construct(string $name = 'body', string $value = '')
        {
            $this->nodeName = $name;
            $this->nodeValue = $value;
            if ($name === 'body') {
                $textNode = new DOMNode('#text', '');
                $textNode->nodeType = 3;
                $this->childNodes = [$textNode];
            }
        }

        public function hasChildNodes(): bool { return !empty($this->childNodes); }
        public function getAttribute(string $name): string { return ''; }
        public function hasAttribute(string $name): bool { return false; }
        public function item(int $index): ?self { return $this->childNodes[$index] ?? null; }
    }

    class DOMDocument
    {
        public bool $preserveWhiteSpace = true;
        public bool $formatOutput = false;

        public function loadHTML(string $source, int $options = 0): bool { return true; }
        public function getElementsByTagName(string $name): object { return new DOMNode('body'); }
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);
        $middleware->validateCsrfTokens(except: [
            'driver-terminal/*',
            'driver-terminal/deliveries/*/accept',
            'driver/*',
        ]);
        $middleware->alias([
            'auth' => \App\Http\Middleware\AutoAuthenticate::class,
            'verified' => \App\Http\Middleware\AutoAuthenticate::class,
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
            'driver.auth' => \App\Http\Middleware\EnsureUserIsDriver::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
