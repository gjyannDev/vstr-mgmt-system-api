<?php
require __DIR__ . '/vendor/autoload.php';
$paths = [__DIR__ . '/app'];
echo "Scanning paths:\n";
print_r($paths);
try {
  $openapi = \OpenApi\scan($paths);
  echo "Scan completed.\n";
  echo "Output (truncated):\n";
  $json = $openapi->toJson();
  echo substr($json, 0, 2000) . "\n";
} catch (Throwable $e) {
  echo "Exception: " . $e->getMessage() . "\n";
  echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
