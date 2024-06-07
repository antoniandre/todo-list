<?php

// MAIN.
// --------------------------------------------------------
defineConstants();
autoload();
loadController();
runControllerMethod();
// --------------------------------------------------------


// Functions.
// --------------------------------------------------------
function defineConstants() {
  // The full route (after /api/) requested by the frontend.
  define('ROUTE', preg_replace('/^.*\/api\//', '', rtrim($_SERVER['REQUEST_URI'], '/')));
  // The request method (E.g. get, post, put, delete).
  define('METHOD', strtolower($_SERVER['REQUEST_METHOD']));
  // Any user input provided by the frontend.
  define('INPUT', json_decode(file_get_contents('php://input')));
};

function autoload() {
  require __DIR__ . '/vendor/autoload.php';

  // Autoload the PHP classes.
  spl_autoload_register(function ($className) {
    include __DIR__ . '/classes/' . strtolower($className) . '.php';
  });
}

function loadController() {
  list($controller, $action) = array_pad(explode('/', ROUTE), 2, '');

  // Load controller in plural form regardless of how it's written in controller.
  // For instance /api/user/login will still load the `users` controller.
  $controllerScript = rtrim($controller, 's');
  $controllersDir = __DIR__ . '/controllers';
  if (!is_file("$controllersDir/$controllerScript.php")) $controllerScript = "{$controllerScript}s";
  if (!is_file("$controllersDir/$controllerScript.php")) output(404, 'Controller not found.') && exit;
  else include_once "$controllersDir/$controllerScript.php";
}

function runControllerMethod() {
  $found = false;
  $params = [];

  foreach(ENDPOINTS as $endpoint => $method) {
    list($endpointMethod, $endpoint) = explode(':', $endpoint);

    // 1. Skip if the method is different.
    if (METHOD !== $endpointMethod) continue;

    // 2. If the route is exactly like the endpoint, we found it.
    if ($endpoint === ROUTE) {
      $found = true;
      break;
    }

    // 3. If there are variables in the endpoint, try to match on the route.
    if (strstr($endpoint, '{')) {
      $endpointPattern = preg_replace('~\{[\w-]+?\}~', '([\w-]+?)', $endpoint);
      if (preg_match("~^$endpointPattern$~", ROUTE, $matches)) {
        $found = true;
        $params = array_slice($matches, 1);
        break;
      }
    }
  }

  if ($found && is_callable($method)) $method(...$params);
  else output(404, 'Method not found.') && exit;
}

/**
 * Outputs the JSON payload to the frontend with correct response code.
 *
 * @param ?integer $code the response code to send back to the frontend.
 * @param ?string $message a potential message in case of error.
 * @param ?array $data some data to send back to the frontend if any.
 * @return void
 */
function output(?int $code = 200, ?string $message = '', ?array $data = []): true {
  $output = ['error' => $code < 200 || $code >= 300, 'message' => $message];
  $output = array_merge($output, $data ?: []);

  echo json_encode((object)$output);
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');

  return true; // So we can chain.
}
// --------------------------------------------------------

?>
