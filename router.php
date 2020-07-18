<?php
namespace kamilmakosa\router {

  require_once '../src/request.php';
  use kamilmakosa\router\Request;

  class Router {
    public $routes;

    public $baseURL;

    public $params;

    //list of working methods
    public $methods;

    public $callback_404;

    //dodaje nowy link użyty wraz z metodą GET
    public function __construct($baseURL) {
      $this->routes = array();
      $this->params = array();
      $this->baseURL = preg_replace('/\/$/', '', $baseURL); //remove last '/'
      $this->methods = array('GET', 'HEAD', 'OPTIONS', 'PUT', 'POST', 'UPDATE', 'DELETE', 'ALL');
    }

    //register new endpoint
    // method($path, [$options], $callback)
    public function __call($name, $arguments) {
      if(!in_array(strtoupper($name), $this->methods)) {
        throw new \Exception("Method '$name' is not valid.");
      }

      $path = $arguments[0];

      if(count($arguments) == 2) {
        $options = NULL;
        $callback = $arguments[1];
      } else if (count($arguments) == 3) {
        $options = $arguments[1];
        $callback = $arguments[2];
      } else {
        return false;
      }

      $this->routes[] = array('path' => $path,
                              'method' => strtoupper($name),
                              'options' => $options,
                              'callback' => $callback);
    }

    public function multi($names, $delimiter, ...$arguments) {
      $methods = explode($delimiter, $names);
      foreach ($methods as $key => $value) {
        $this->__call($value, $arguments);
      }
    }

    public function set404($callback) {
      $this->callback_404 = $callback;
    }

    public function mount($baseRoute, $fn)  {
      // Track current base route
      $curBaseRoute = $this->baseRoute;
      // Build new base route string
      $this->baseRoute .= $baseRoute;
      // Call the callable
      call_user_func($fn);
      // Restore original base route
      $this->baseRoute = $curBaseRoute;
    }

    public function run($method, $request) {
      //sanitaze slashes in base URL
      $pattern = str_replace('/', '\/', $this->baseURL);

      //remove baseURL from request
      $request = preg_replace('/^'.$pattern.'/', '', $request);

      //sanitaze last slashes from request if exist
      $request = preg_replace('/\/$/', '', $request);

      foreach($this->routes as $route) {
        if($route['method'] != $method) {
          continue;
        }

        if($request == '') {
          $request = '/';
        }

        if($this->is_full_match($route['path'], $request) == true) {
          $this->params = $this->bind_params($route['path'], $request);

          return call_user_func_array($route['callback'], [new Request($this->baseURL, $this->params, $route)]);
        }

      }

      return call_user_func_array($this->callback_404, [new Request($this->baseURL, $this->params, $this->route)]);
    }

    private function is_full_match($path, $request_path) {
      //replace :param to (.*)
      $path = preg_replace('/:([^\\\\\/]+)/', '([^\\\\\/]+)', $path);
      $path = str_replace('/', '\/', $path);

      if(preg_match_all('/^'.$path.'$/', $request_path, $matches, PREG_PATTERN_ORDER)) {
        return true;
      }
      return false;
    }

    private function bind_params($path, $request_path) {
      //bind params name's
      $params = array();
      if(preg_match_all('/:([^\\^\/]+)/', $path, $matches,PREG_PATTERN_ORDER)) {
        $names = $matches[1];
      } else {
        return false;
      }

      //replace :param to (.*)
      $path = preg_replace('/:([^\\\\\/]+)/', '([^\\\\\/]+)', $path);
      $path = str_replace('/', '\/', $path);

      //bind values
      if(preg_match_all('/^'.$path.'$/', $request_path, $matches, PREG_PATTERN_ORDER)) {
        $values = array_slice($matches, 1); //cut full pattern matches
        $values = array_map(function($r) {  //mapping result from array[$i][0] => array[$i]
          return $r[0];
        }, $values);
      } else {
        return false;
      }

      //if eqaul lengths of arrays return array
      if(isset($names) && isset($values) && count($names) == count($values)) {
        return array_combine($names, $values);
      }
      return false;
    }
  }
}
?>
