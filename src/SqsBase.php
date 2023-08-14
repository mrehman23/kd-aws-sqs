<?php

namespace KdSqs;

use Aws\Credentials\Credentials;
use Aws\Sdk;
use Aws\Sqs\SqsClient;

class SqsBase
{
    public $sqsClient = null;

    /**
     * SqsBase constructor.
     *
     * @param array $awsConfig
     */
    public function __construct(array $awsConfig)
    {
        $sharedConfig = [
            'region'  => $awsConfig['AWS_REGION'],
            'version' => $awsConfig['API_VERSION'],
        ];

        if (isset($awsConfig['AWS_KEY'], $awsConfig['AWS_SECRET_KEY'])) {
            $sharedConfig['credentials'] = new Credentials($awsConfig['AWS_KEY'], $awsConfig['AWS_SECRET_KEY']);
        }

        // Create an Amazon SQS client using the shared configuration data.
        $sdk = new Sdk($sharedConfig);

        $this->sqsClient = $sdk->createSqs();
    }

    /**
     * Set client.
     *
     * @param SqsClient $sqsClient
     */
    public function setClient(SqsClient $sqsClient)
    {
        $this->sqsClient = $sqsClient;
    }

    /**
     * Set params.
     *
     * @param array $params
     */
    public function setParams(array $params)
    {
        foreach ($params as $param => $value) {
            $this->{$param} = $value;
        }
    }
}
