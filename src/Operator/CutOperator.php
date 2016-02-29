<?php

namespace Rx\Extra\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
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
        $buffer     = null;
        $items      = [];
        $disposable = new CompositeDisposable();
        $recursing  = false;
        $completed  = false;

        $onCompleted = function () use (&$buffer, $observer, $scheduler, &$recursing) {
            if ($recursing) {
                return;
            }
            if ($buffer !== null) {
                $observer->onNext($buffer);
            }
            $observer->onCompleted();
        };

        $onNext = function ($x) use (&$buffer, $observer, $scheduler, &$items, $disposable, &$recursing, &$completed, $onCompleted) {
            if ($buffer === null) {
                $buffer = '';
            }
            $buffer .= $x;
            $items  = array_merge($items, explode($this->delimiter, $buffer));
            $buffer = array_pop($items);

            $action = function ($reschedule) use (&$observer, &$items, &$buffer, &$recursing, &$completed, $onCompleted) {

                if (count($items) === 0) {
                    $recursing = false;
                    if ($completed) {
                        $onCompleted();
                    }
                    return;
                }

                $value = array_shift($items);

                $observer->onNext($value);

                $reschedule();

            };

            $recursing = true;
            $schedulerDisposable = $scheduler->scheduleRecursive($action);

            $disposable->add($schedulerDisposable);
        };

        $callbackObserver = new CallbackObserver($onNext, [$observer, "onError"], $onCompleted);
        $sourceDisposable = $observable->subscribe($callbackObserver);

        $disposable->add($sourceDisposable);

        return $disposable;
    }
}
