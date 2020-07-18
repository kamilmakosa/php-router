<?php
require_once '../router.php';

use kamilmakosa\router\Router;

$router = new Router('/api/v2/');
try {
  $router->get('/', function($req) {
    return 'Root';
  });

  $router->get('/articles', function($req) {
    var_dump($req->params['id']);
    return 'Articles without id';
  });

  $router->get('/articles/:id', function($req) {
    var_dump($req->params['id']);
    return 'Articles with id';
  });

  $router->get('/articles/:id/authors/:sub-id', function() {
    return 'Articles and authors';
  });

  $router->get('/ab+cd', function() {
    return 'ab+cd';
  });

  $router->multi('GET|POST', '|', '/x', function() {
    echo 'multi';
  });

  $router->set404(function () {
    return 'XX';
  });
} catch (\Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

$router->run($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"]);
?>
