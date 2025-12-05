<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Media\MediaProcessor;
use App\Models\MediaFile;
use App\Models\Project;

echo "Checking classes...\n";

if (class_exists(MediaProcessor::class)) {
    echo "MediaProcessor exists.\n";
} else {
    echo "MediaProcessor MISSING.\n";
    exit(1);
}

if (trait_exists(\App\Traits\HasMedia::class)) {
    echo "HasMedia Trait exists.\n";
} else {
    echo "HasMedia Trait MISSING.\n";
    exit(1);
}

// Reflection check
$reflection = new ReflectionClass(MediaProcessor::class);
if ($reflection->hasMethod('processUpload')) {
    echo "MediaProcessor::processUpload exists.\n";
} else {
    echo "MediaProcessor::processUpload MISSING.\n";
}

echo "Syntax check passed.\n";
