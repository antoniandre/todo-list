<?php

// Autoload the PHP classes.
spl_autoload_register(function ($className) {
  include __DIR__ . '/classes/' . strtolower($className) . '.php';
});

// MAIN.
// --------------------------------------------------------
runController();
// --------------------------------------------------------

// Functions.
// --------------------------------------------------------
function runController () {
  $params = json_decode(file_get_contents('php://input'));
  $endpoint = preg_replace('/^.*\/api\//', '', $_SERVER['REQUEST_URI']);
  list($endpoint, $action) = array_pad(explode('/', $endpoint), 2, '');
  $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

  if (is_file(__DIR__ . "/controllers/$endpoint.php")) {
    list($code, $message, $data) = include_once __DIR__ . "/controllers/$endpoint.php";
  }

  else {
    $code = 400; // Method not allowed.
    $message = 'Incorrect endpoint.';
  }

  output($code ?? 200, $message ?? '', $data ?? []);
  Database::get()->close();
}

/**
 * Outputs the JSON payload to the frontend with correct response code.
 *
 * @param integer $code the response code to send back to the frontend.
 * @param string $message a potential message in case of error.
 * @param array $data some data to send back to the frontend if any.
 * @return void
 */
function output(int $code, string $message, array $data = []): void {
  $output = ['error' => $code < 200 || $code >= 300, 'message' => $message];
  $output = array_merge($output, $data);

  echo json_encode((object)$output);
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
}
// --------------------------------------------------------

?>
