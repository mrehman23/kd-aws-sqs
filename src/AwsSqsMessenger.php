<?php

namespace KdAwsSqs;


use Aws\Exception\AwsException;

class AwsSqsMessenger extends SqsBase
{
    public $retryTimesOnFail = 2;
    public $waitBeforeRetry = 1;

    /**
     * Publish a message to an SQS queue.
     *
     * @param string $queueUrl
     * @param string $message
     * @param array $messageAttributes
     * @param int $delaySeconds
     * @param string $messageGroupId
     * @param string $messageDeduplicationId
     *
     * @return \Aws\Result|bool
     * @throws \Exception
     */
    public function publish(
        string $queueUrl,
        string $message,
        array $messageAttributes = [],
        int $delaySeconds = 10,
        string $messageGroupId = '',
        string $messageDeduplicationId = ''
    ) {
        if ($this->sqsClient === null) {
            throw new \Exception("No SQS client defined");
        }

        $params = [
            'QueueUrl' => $queueUrl,
            'MessageBody' => $message,
            'MessageAttributes' => $messageAttributes,
        ];

        if ($delaySeconds) {
            $params['DelaySeconds'] = $delaySeconds;
        }

        if ($messageGroupId) {
            $params['MessageGroupId'] = $messageGroupId;
        }

        if ($messageDeduplicationId) {
            $params['MessageDeduplicationId'] = $messageDeduplicationId;
        }

        $tryAgain = false;
        $errorCounter = 0;
        do {
            try {
                $result = $this->sqsClient->sendMessage($params);
                $tryAgain = false;
            } catch (AwsException $e) {
                if ($this->retryTimesOnFail > 0) {
                    $result = false;
                    $tryAgain = true;

                    if ($errorCounter >= $this->retryTimesOnFail) {
                        break;
                    }

                    if ($errorCounter >= 2 && $this->waitBeforeRetry > 0) {
                        sleep($this->waitBeforeRetry);
                    }

                    // Output error message if fails
                    error_log($e->getMessage());
                    $errorCounter++;
                }
            }
        } while ($tryAgain);

        return $result;
    }
}
