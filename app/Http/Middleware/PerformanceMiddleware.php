<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class PerformanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Aplicar otimizações apenas para respostas HTML
        if ($this->shouldOptimize($request, $response)) {
            $this->optimizeResponse($response);
        }

        return $response;
    }

    /**
     * Determinar se a resposta deve ser otimizada
     */
    private function shouldOptimize(Request $request, $response): bool
    {
        return $response instanceof Response &&
               $response->headers->get('Content-Type', '') === 'text/html; charset=UTF-8' &&
               !$request->ajax() &&
               !$request->wantsJson();
    }

    /**
     * Otimizar a resposta
     */
    private function optimizeResponse(Response $response): void
    {
        $content = $response->getContent();

        if ($content) {
            // Minificar HTML
            $content = $this->minifyHtml($content);
            
            // Adicionar headers de performance
            $this->addPerformanceHeaders($response);
            
            // Aplicar compressão se necessário
            if (config('performance.compression.gzip', true)) {
                $this->applyCompression($response, $content);
            }
            
            $response->setContent($content);
        }
    }

    /**
     * Minificar HTML
     */
    private function minifyHtml(string $html): string
    {
        // Remover comentários HTML (exceto condicionais do IE)
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
        
        // Remover espaços em branco desnecessários
        $html = preg_replace('/\s+/', ' ', $html);
        
        // Remover espaços em branco entre tags
        $html = preg_replace('/>\s+</', '><', $html);
        
        // Remover espaços em branco no início e fim
        $html = trim($html);
        
        return $html;
    }

    /**
     * Adicionar headers de performance
     */
    private function addPerformanceHeaders(Response $response): void
    {
        $headers = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'X-DNS-Prefetch-Control' => 'on',
            'X-Powered-By' => 'Laravel/Optimized',
        ];

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        // Adicionar preload hints para recursos críticos
        $preloadHints = [
            '</css/app.css>; rel=preload; as=style',
            '</js/app.js>; rel=preload; as=script',
            '<https://fonts.googleapis.com>; rel=preconnect',
            '<https://cdn.tailwindcss.com>; rel=preconnect',
        ];

        $response->headers->set('Link', implode(', ', $preloadHints));
    }

    /**
     * Aplicar compressão
     */
    private function applyCompression(Response $response, string &$content): void
    {
        if (function_exists('gzencode') && 
            strlen($content) > config('performance.compression.min_length', 1000)) {
            
            $compressed = gzencode($content, config('performance.compression.level', 6));
            
            if ($compressed !== false && strlen($compressed) < strlen($content)) {
                $response->headers->set('Content-Encoding', 'gzip');
                $response->headers->set('Vary', 'Accept-Encoding');
                $content = $compressed;
            }
        }
    }
}