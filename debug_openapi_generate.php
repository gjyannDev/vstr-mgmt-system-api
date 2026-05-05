<?php
require __DIR__ . '/vendor/autoload.php';

use OpenApi\Generator;

$paths = [__DIR__ . '/app'];
echo "Using Generator::generate on paths:\n";
print_r($paths);
try {
  $gen = new Generator();
  $openapi = $gen->generate($paths, null, true);
  if ($openapi) {
    echo "Generated OpenAPI spec (truncated):\n";
    echo substr($openapi->toJson(), 0, 2000) . "\n";
  } else {
    echo "No OpenAPI generated.\n";
  }
} catch (Throwable $e) {
  echo "Exception: " . $e->getMessage() . "\n";
  echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
