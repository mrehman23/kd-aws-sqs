# AWS SQS Queue PHP Package

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://opensource.org/licenses/MIT)

This package provides a simple and convenient way to interact with Amazon Simple Queue Service (SQS) using PHP.

## Features

- Send messages to an SQS queue.
- Receive messages from an SQS queue.
- Delete messages from an SQS queue.

## Requirements

- PHP 7.2 or later
- AWS SDK for PHP (aws/aws-sdk-php)
- AWS account credentials set up (Access Key ID and Secret Access Key)

## Installation

You can install this package via Composer:

```bash
composer require mrehman23/sqs-queue
```

## Examples
```
require 'vendor/autoload.php';
use SqsSimple\SqsMessenger;
$AwsConfig = [
    'AWS_KEY'=>'', //You should put your AWS_KEY 
    'AWS_SECRET_KEY'=>'', //You should put your AWS_SECRET_KEY 
    'AWS_REGION'=>'eu-west-1', //You should put your AWS_REGION 
    'API_VERSION'=>'2012-11-05'
];
$messenger = new SqsMessenger($AwsConfig);
// $messenger->RetryTimesOnFail = 2;
// $messenger->WaitBeforeRetry = 1; //seconds
$queue = "<Your queueUrl>";
$message = "This is a message for SQS";
$messageAttributes['params'] = [
    'DataType' => 'String',
    'StringValue' => json_encode($params)
];
$delaySeconds = 10;
$messageGroupId = '';
$messageDeduplicationId = '';
$result = $messenger->publish($queue, $message, $messageAttributes, $delaySeconds, $messageGroupId, $messageDeduplicationId);
```

### Listener
```
require 'vendor/autoload.php';
use SqsSimple\SqsWorker;

$worker = new SqsWorker($this->awsConfig['credentials']);
$worker->listen(
    $this->awsConfig['queue'],
    [$this, 'processMessage'],
    [$this, 'errorHandler']
);


