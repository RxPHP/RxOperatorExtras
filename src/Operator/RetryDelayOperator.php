<?php

namespace Rx\Operator;

use Rx\AsyncSchedulerInterface;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

class RetryDelayOperator implements OperatorInterface
{
    private $initialRetryDelay;
    private $maxRetryDelay;
    private $maxRetries;
    private $retryDelayGrowth;
    private $currentRetryCount;
    private $scheduler;

    public function __construct(int $initialRetryDelay = 1500, int $maxRetries = 150, int $maxRetryDelay = 150000, float $retryDelayGrowth = 1.5, AsyncSchedulerInterface $scheduler = null)
    {
        $this->initialRetryDelay = $initialRetryDelay;
        $this->maxRetryDelay     = $maxRetryDelay;
        $this->maxRetries        = $maxRetries;
        $this->retryDelayGrowth  = $retryDelayGrowth;
        $this->scheduler         = $scheduler;
        $this->currentRetryCount = 0;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable
            ->retryWhen(function (Observable $errors) {
                return $errors
                    ->flatMap(function (\Throwable $e) {
                        $delay = min($this->maxRetryDelay, $this->initialRetryDelay * ($this->retryDelayGrowth ** $this->currentRetryCount++));
                        return Observable::timer((int)$delay, $this->scheduler);
                    })
                    ->take($this->maxRetries);
            })
            ->subscribe($observer);
    }
}

