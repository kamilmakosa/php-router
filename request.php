<?php
namespace kamilmakosa\router {

  class Request {
    public $baseUrl;
    public $cookies;
    public $host;
    public $hostname;
    public $ip;
    public $method;
    public $originalUrl;
    public $params;
    public $path;
    public $protocol;
    public $query;
    public $route;
    public $secure;
    public $xhr;

    public function __construct($baseUrl, $params, $route) {
      $this->baseUrl = $baseUrl;
      $this->cookies = $_COOKIE;
      $this->host = $_SERVER["HTTP_HOST"].'::'.$_SERVER["SERVER_PORT"];
      // SERVER_ADDR    127.0.0.1
      // SERVER_NAME    localhost
      $this->hostname = $_SERVER["HTTP_HOST"];
      $this->ip = $_SERVER["REMOTE_ADDR"];
      $this->method = $_SERVER["REQUEST_METHOD"];
      $this->originalUrl = $_SERVER["REQUEST_URI"];
      $this->params = $params;
      $this->path = strpos($_SERVER["REQUEST_URI"], '?') ?
                      substr($_SERVER["REQUEST_URI"], 0, strpos($_SERVER["REQUEST_URI"], '?')) :
                      $_SERVER["REQUEST_URI"];
      $this->protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')));
      $this->query = $_GET;
      $this->route = $route;
      $this->secure = ($this->protocol === 'https');
      $this->xhr = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
    }

    public function get_property(...$names) {
      if(func_num_args() == 1) {
        return (isset(get_object_vars($this)[$names])) ? get_object_vars($this)[$names] : NULL;
      } else {
        // $result = array();
        foreach ($names as $name) {
          $result[$name] = get_object_vars($this)[$name];
        }
        return $result;
      }
    }

    public function get_properties() {
      return get_object_vars($this);
    }

    public function get_header($name) {
      return (isset(getallheaders()[$name])) ? getallheaders()[$name] : NULL;
    }

    public function get_headers() {
      return getallheaders();
    }

    public function get_accepts_language() {
      return explode(',', getallheaders()["Accept-Language"])[0];
    }

    public function get_accepts_languages() {
      $result = explode(',', getallheaders()["Accept-Language"]);
      $result = array_map(function($item) {
        return explode(';', $item);
      }, $result);
      return $result;
    }

  }
}

?>
