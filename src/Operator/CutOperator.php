<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

/**
 * Cuts the stream based upon a delimiter.
 *
 * Class CutOperator
 * @package Rx\React\Operator
 */
class CutOperator implements OperatorInterface
{

    private $delimiter, $scheduler;

    public function __construct($delimiter = PHP_EOL, SchedulerInterface $scheduler = null)
    {
        $this->delimiter = $delimiter;
        $this->scheduler = $scheduler ?: Scheduler::getDefault();
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @return \Rx\DisposableInterface
     * @throws \InvalidArgumentException
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $buffer = '';

        /** @var $observable Observable */
        return $observable
            ->defaultIfEmpty(Observable::of(null, $this->scheduler))
            ->concat(Observable::of($this->delimiter, $this->scheduler))
            ->concatMap(function ($x) use (&$buffer) {

                if ($x === null || $buffer === null) {
                    $buffer = null;
                    return Observable::empty($this->scheduler);
                }

                $items  = explode($this->delimiter, $buffer . $x);
                $buffer = array_pop($items);

                return Observable::fromArray($items, $this->scheduler);
            })
            ->subscribe($observer);
    }
}
