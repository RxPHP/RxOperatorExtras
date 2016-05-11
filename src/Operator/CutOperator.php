<?php

namespace Rx\Extra\Operator;

use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Operator\OperatorInterface;
use Rx\SchedulerInterface;

/**
 * Cuts the stream based upon a delimiter.
 *
 * Class CutOperator
 * @package Rx\React\Operator
 */
class CutOperator implements OperatorInterface
{

    private $delimiter;

    public function __construct($delimiter = PHP_EOL)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        
        $buffer = "";

        return $observable
            ->defaultIfEmpty(Observable::just(null))
            ->concat(Observable::just($this->delimiter))
            ->concatMap(function ($x) use (&$buffer) {

                if ($x === null || $buffer === null) {
                    $buffer = null;
                    return Observable::emptyObservable();
                }

                $items  = explode($this->delimiter, $buffer . $x);
                $buffer = array_pop($items);

                return Observable::fromArray($items);
            })
            ->subscribe($observer, $scheduler);
    }
}
