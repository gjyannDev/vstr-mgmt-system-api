<?php
require __DIR__ . '/vendor/autoload.php';

use OpenApi\Generator;
use OpenApi\Analysis;
use OpenApi\Context;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;

class CollectLogger implements LoggerInterface
{
  public array $messages = [];
  public function emergency(Stringable|string $message, array $context = []): void
  {
    $this->log('emergency', $message, $context);
  }
  public function alert(Stringable|string $message, array $context = []): void
  {
    $this->log('alert', $message, $context);
  }
  public function critical(Stringable|string $message, array $context = []): void
  {
    $this->log('critical', $message, $context);
  }
  public function error(Stringable|string $message, array $context = []): void
  {
    $this->log('error', $message, $context);
  }
  public function warning(Stringable|string $message, array $context = []): void
  {
    $this->log('warning', $message, $context);
  }
  public function notice(Stringable|string $message, array $context = []): void
  {
    $this->log('notice', $message, $context);
  }
  public function info(Stringable|string $message, array $context = []): void
  {
    $this->log('info', $message, $context);
  }
  public function debug(Stringable|string $message, array $context = []): void
  {
    $this->log('debug', $message, $context);
  }
  public function log($level, Stringable|string $message, array $context = []): void
  {
    $this->messages[] = [$level, (string)$message];
  }
}

$paths = [__DIR__ . '/app'];
$logger = new CollectLogger();
$gen = new Generator($logger);
$rootContext = new Context(['version' => $gen->getVersion(), 'logger' => $logger]);
$analysis = new Analysis([], $rootContext);

// use reflection to call protected scanSources
$rm = new ReflectionMethod(Generator::class, 'scanSources');
$rm->setAccessible(true);
$rm->invoke($gen, $paths, $analysis, $rootContext);

echo "Logger messages:\n";
print_r($logger->messages);

// Summary of all parsed annotation types
echo "\nParsed annotations summary:\n";
foreach ($analysis->annotations as $annotation) {
  $ctx = $analysis->annotations[$annotation] ?? null;
  echo get_class($annotation) . " | identity=" . ($annotation->identity() ?? '') . " | file=" . ($ctx->file ?? 'unknown') . "\n";
}

$ops = $analysis->getAnnotationsOfType(OA\Operation::class);
echo "Total Operation annotations found: " . count($ops) . "\n";
foreach ($ops as $op) {
  $ctx = $op->_context ?? null;
  $loc = $ctx?->file ?? ($ctx?->line ?? '');
  echo $op->identity() . " | path=" . ($op->path ?? '') . " | file=" . ($ctx?->file ?? 'unknown') . "\n";
}

$pathsAnn = $analysis->getAnnotationsOfType(OA\PathItem::class);
echo "Total PathItem annotations found: " . count($pathsAnn) . "\n";
foreach ($pathsAnn as $p) {
  $ctx = $p->_context ?? null;
  echo "PathItem: path=" . ($p->path ?? '') . " | file=" . ($ctx?->file ?? 'unknown') . "\n";
}

// Process pipeline
$gen->getProcessorPipeline()->process($analysis);

echo "After processing:\n";
$pathsOut = $analysis->openapi->paths ?? null;
if ($pathsOut === null) {
  echo "No paths present in final OpenAPI\n";
} else {
  $iter = is_array($pathsOut) || $pathsOut instanceof Traversable ? $pathsOut : (array)$pathsOut;
  echo "Paths in output: " . count($iter) . "\n";
  foreach ($iter as $p) {
    $path = $p->path ?? (is_string($p) ? $p : '');
    echo " - " . $path . "\n";
  }
}

// show unmerged operations
$unmerged = $analysis->unmerged();
$unmergedOps = $unmerged->getAnnotationsOfType(OA\Operation::class);
echo "Unmerged operations: " . count($unmergedOps) . "\n";
foreach ($unmergedOps as $uop) {
  echo "UNMERGED: " . $uop->identity() . " | path=" . ($uop->path ?? '') . "\n";
}

echo "Done.\n";
