<?php

use Spatie\TypeScriptTransformer\Enums\RunnerMode;
use Spatie\TypeScriptTransformer\Runners\Runner;
use Spatie\TypeScriptTransformer\Support\Loggers\ArrayLogger;
use Spatie\TypeScriptTransformer\Support\Loggers\SymfonyConsoleLogger;
use Spatie\TypeScriptTransformer\Transformers\AttributedClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;
use Symfony\Component\Console\Logger\ConsoleLogger;

require_once __DIR__ . '/../vendor/autoload.php';

$config = TypeScriptTransformerConfigFactory::create()
	->outputDirectory(__DIR__ . '/../src/types') 
    ->transformDirectories(__DIR__ . '/../includes') 
    ->transformer(new AttributedClassTransformer(), new EnumTransformer()) 
	->writer(new FlatModuleWriter())
	->get();

$runner = new Runner();

return $runner->run(
	logger: new ArrayLogger(),
	config: $config,
	mode: RunnerMode::Direct,
);

echo "Types generated successfully!" . PHP_EOL;