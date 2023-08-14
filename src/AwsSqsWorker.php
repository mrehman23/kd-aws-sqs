<?php

namespace KdAwsSqs;

use Aws\Exception\AwsException;

class AwsSqsWorker extends AwsSqsBase
{
    public $sleep = 10;
    public $waitTimeSeconds = 20;
    public $maxNumberOfMessages = 1;
    public $visibilityTimeout = 3600;
    public $workerProcess = false;

    private $queueUrl;

    /**
     * Listener
     *
     * @param string $queueUrl
     * @param callable $workerProcess
     * @param callable|null $errorHandlerCallback
     * @throws \Exception
     */
    public function listen(
        string $queueUrl,
        callable $workerProcess,
        ?callable $errorHandlerCallback = null
    ) {
        $this->queueUrl = $queueUrl;

        $this->validateWorkerProcess($workerProcess);

        if ($errorHandlerCallback !== null) {
            $this->validateErrorHandlerCallback($errorHandlerCallback);
        }

        $this->printHeader();

        $checkForMessages = true;
        $counterCheck = 0;
        $errorCounter = 0;

        while ($checkForMessages) {
            $this->out("Check(" . $counterCheck . ") time: " . date("Y-m-d H:i:s"));

            try {
                $this->processMessages($workerProcess);
                $errorCounter = 0;
            } catch (AwsException $e) {
                $errorCounter++;
                $this->handleAwsException($e, $errorCounter, $errorHandlerCallback);
            }

            $counterCheck++;
        }

        $this->printFooter();
    }

    /**
     * Delete message
     *
     * @param string $receiptHandle
     * @throws \Exception
     */
    public function deleteMessage(string $receiptHandle): void
    {
        $this->validateSqsClient();
        
        $this->sqsClient->deleteMessage([
            'QueueUrl' => $this->queueUrl,
            'ReceiptHandle' => $receiptHandle,
        ]);
    }

    /**
     * Process messages.
     *
     * @param callable $workerProcess
     * @throws \Exception
     */
    private function processMessages(callable $workerProcess): void
    {
        $this->validateSqsClient();

        $result = $this->sqsClient->receiveMessage([
            'AttributeNames' => ['SentTimestamp'],
            'MaxNumberOfMessages' => $this->maxNumberOfMessages,
            'MessageAttributeNames' => ['All'],
            'QueueUrl' => $this->queueUrl,
            'WaitTimeSeconds' => $this->waitTimeSeconds,
            'VisibilityTimeout' => $this->visibilityTimeout,
        ]);

        $messages = $result->get('Messages');

        if ($messages !== null) {
            $this->out("Messages found");
            $this->handleMessages($messages, $workerProcess);
        } else {
            $this->out("No messages found");
            sleep($this->sleep);
        }
    }

    /**
     * Handle messages.
     *
     * @param array $messages
     * @param callable $workerProcess
     * @throws \Exception
     */
    private function handleMessages(array $messages, callable $workerProcess): void
    {
        foreach ($messages as $message) {
            $completed = $workerProcess($message);

            if ($completed) {
                $this->ackMessage($message);
            } else {
                $this->nackMessage($message);
            }
        }
    }

    /**
     * Acknowledge message.
     *
     * @param array $message
     * @throws \Exception
     */
    private function ackMessage(array $message): void
    {
        $this->validateSqsClient();

        $this->sqsClient->deleteMessage([
            'QueueUrl' => $this->queueUrl,
            'ReceiptHandle' => $message['ReceiptHandle'],
        ]);
    }

    /**
     * Nack message.
     *
     * @param array $message
     * @throws \Exception
     */
    private function nackMessage(array $message): void
    {
        $this->validateSqsClient();

        $this->sqsClient->changeMessageVisibility([
            'VisibilityTimeout' => 0,
            'QueueUrl' => $this->queueUrl,
            'ReceiptHandle' => $message['ReceiptHandle'],
        ]);
    }

    /**
     * Validate the worker process callback.
     *
     * @param callable $workerProcess
     * @throws \InvalidArgumentException
     */
    private function validateWorkerProcess(callable $workerProcess): void
    {
        if (!is_callable($workerProcess)) {
            throw new \InvalidArgumentException("WorkerProcess is not callable");
        }
    }

    /**
     * Validate the error handler callback.
     *
     * @param callable $errorHandlerCallback
     * @throws \InvalidArgumentException
     */
    private function validateErrorHandlerCallback(callable $errorHandlerCallback): void
    {
        if (!is_callable($errorHandlerCallback)) {
            throw new \InvalidArgumentException("ErrorHandlerCallback is not callable");
        }
    }

    /**
     * Handle AWS exception with retries and error callback.
     *
     * @param AwsException $e
     * @param int $errorCounter
     * @param callable|null $errorHandlerCallback
     */
    private function handleAwsException(
        AwsException $e,
        int $errorCounter,
        ?callable $errorHandlerCallback
    ): void {
        if ($errorCounter >= 5) {
            $checkForMessages = false;
        }

        error_log($e->getMessage());

        if ($errorHandlerCallback !== null) {
            $errorHandlerCallback($e->getMessage(), $errorCounter);
        }
    }

    // ... (Other private methods)

    /**
     * Print header.
     */
    private function printHeader(): void
    {
        echo "\n\n";
        echo "\n*****************************************************************";
        echo "\n**** Worker started at " . date("Y-m-d H:i:s");
        echo "\n*****************************************************************";
    }

    /**
     * Print footer.
     */
    private function printFooter(): void
    {
        echo "\n\n";
        echo "\n*****************************************************************";
        echo "\n**** Worker finished at " . date("Y-m-d H:i:s");
        echo "\n*****************************************************************";
        echo "\n\n";
    }

    /**
     * Output a message.
     *
     * @param string $message
     */
    private function out(string $message): void
    {
        echo "\n" . $message;
    }
}
