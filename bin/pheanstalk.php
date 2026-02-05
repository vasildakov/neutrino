<?php

use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';


$pheanstalk = Pheanstalk::create('127.0.0.1');
$tube       = new TubeName('testtube');

// Queue a Job
$pheanstalk->useTube($tube);
$pheanstalk->put("job payload goes here\n");

$pheanstalk->useTube($tube);
try {
    $pheanstalk->put(
        data: json_encode(['test' => 'data'], JSON_THROW_ON_ERROR),
        priority: 1,
        delay: 30,
        timeToRelease: 60
    );
    echo "Job added successfully\n";
} catch (JsonException $e) {
    echo "Error: " . $e->getMessage();
}