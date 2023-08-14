# AWS SQS Queue PHP Package

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://opensource.org/licenses/MIT)

This package provides a simple and convenient way to interact with Amazon Simple Queue Service (SQS) using PHP.

## Features

- Send messages to an SQS queue.
- Receive messages from an SQS queue.
- Delete messages from an SQS queue.
- ... (List any additional features your package offers)

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
use SqsSimple\SqsMessenger;

require 'vendor/autoload.php';
use SqsSimple\SqsMessenger;
$AwsConfig = [
    'AWS_KEY'=>'', //You should put your AWS_KEY 
    'AWS_SECRET_KEY'=>'', //You should put your AWS_SECRET_KEY 
    'AWS_REGION'=>'eu-west-1', //You should put your AWS_REGION 
    'API_VERSION'=>'2012-11-05'
];
$messenger = new SqsMessenger($AwsConfig);
/* if a publish message request fails then it will retry again */
// $messenger->RetryTimesOnFail = 2;
/* seconds to wait after failed request to retry again */
// $messenger->WaitBeforeRetry = 1; //seconds
$queue = "<Your queueUrl>";
$message = "This is a message for SQS";
$messageAttributes = [];
$delaySeconds = 10;
$messageGroupId = '';
$messageDeduplicationId = '';
$messenger->publish( $queue, $message, $messageAttributes, $delaySeconds, $messageGroupId, $messageDeduplicationId);
```


