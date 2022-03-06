<?php

namespace Core;

class Router {
    protected $routes;

    public function __construct(array $definitions)
    {
        $this->routes = $this->compileRoutes($definitions);
    }

    private function compileRoutes(array $definitions)
    {
        $routes = [];
        foreach ($definitions as $url => $params) {
            $tokens = explode('/', ltrim($url, '/'));
            foreach ($tokens as $i => $token) {
                if (0 === strpos($token, ':')) {
                    $name = substr($token, 1);
                    $token = '(?P<' . $name . '>[^/]+)';
                }
                $tokens[$i] = $token;
            }

            $pattern = '/' . implode('/', $tokens);
            $routes[$pattern] = $params;
        }

        return $routes;
    }

    public function resolve(string $pathInfo)
    {
        if ('/' !== substr($pathInfo, 0, 1)) {
            $pathInfo = '/'. $pathInfo;
        }

        foreach ($this->routes as $pattern => $params) {
            if (preg_match('#^' . $pattern . '$#', $pathInfo, $matches)) {
                $params = array_merge($params, $matches);

                return $params;
            }
        }

        return false;
    }
}
