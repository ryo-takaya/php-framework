<?php

namespace Core;

use Core\Exception\HttpNotFoundException;

abstract class Application{
    protected $debug = false;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
    protected $router;

    public function __construct($debug = false)
    {
        $this->setDebugMode($debug);
        $this->initialize();
        $this->configure();
    }

    protected function setDebugMode($debug)
    {
        if ($this->debug) {
            $this->debug = true;
            ini_set('display_errors', 1);
            error_reporting(-1);
        } else {
            $this->debug = false;
            ini_set('display_errors', 0);
        }
    }

    protected function initialize()
    {
        $this->request = new Request();
        $this->response = new Request();
        $this->session = new Session();
        $this->db_manager = new \DbManager();
        $this->router = new Router($this->registerRoutes());
    }

    protected function configure()
    {}

    abstract public function getRootDir();

    abstract protected function registerRoutes();

    public function isDebugMode()
    {
        return $this->debug;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getSession()
    {
        return $this->session;
    }

    public function getDbManager()
    {
        return $this->db_manager;
    }

    public function getControllerDir()
    {
        return $this->getRootDir(). '/controllers';
    }

    public function getViewDir()
    {
        return $this->getRootDir(). '/views';
    }

    public function getModelDir()
    {
        return $this->getRootDir(). '/models';
    }

    public function getWebDir()
    {
        return $this->getRootDir(). '/web';
    }

    public function run()
    {
        try{
            $params = $this->router->resorve($this->request->getPathInfo());
            if ($params === false) {
                throw new HttpNotFoundException("無効なpathです");
            }

            $controller = $params['controller'];
            $action = $params['action'];

            $this->runAction($controller, $action, $params);
        } catch (HttpNotFoundException $e) {
            $this->render404Page($e);
        }
        $this->response->send();
    }

    protected function runAction($controllerName, $action, $params = []){
        $controllerClass = ucfirst($controllerName). 'Controller';

        $controller = $this->findController($controllerClass);
        if ($controller === false) {
            throw new HttpNotFoundException("コントローラーが見つかりませんでした");
        }

        $content = $controller->run($action, $params);
        $this->response->setContent($content);
    }

    protected function findController($controllerClass)
    {
        if (class_exists($controllerClass)) {
            $controllerFile = $this->getControllerDir() . '/' . $controllerClass. '.php';

            if (!is_readable($controllerFile)) {
                return false;
            } else {
                if (!class_exists($controllerClass)) {
                    return false;
                }
            }
        }
        return new $controllerClass($this);
    }

    protected function render404Page($e)
    {
        $this->response->setStatus(404, 'Not Found');
        $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found';
        $this->response->setContent($message);
    }
}