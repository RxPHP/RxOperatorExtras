<?php

namespace Rx\Observable;

use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;

/**
 * Class FromEventEmitterObservable
 * 
 * @package Rx\Extra\Observable
 */
class FromEventEmitterObservable extends Observable
{
    /** @var \stdClass */
    private $object;

    /** @var  string */
    private $nextAction;

    /** @var  string */
    private $errorAction;

    /** @var  string */
    private $completeAction;
    

    public function __construct($object, $nextAction = 'data', $errorAction = 'error', $completeAction = 'close')
    {
        if (!method_exists($object, 'on')) {
            throw new \Exception("Object doesn't have an 'on' method");
        }

        $this->object         = $object;
        $this->nextAction     = $nextAction;
        $this->errorAction    = $errorAction;
        $this->completeAction = $completeAction;
    }

    /**
     * @param ObserverInterface $observer
     * @return DisposableInterface
     */
    public function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $this->object->on($this->nextAction, function () use ($observer) {
            $observer->onNext(func_get_args());
        });

        if ($this->errorAction) {
            $this->object->on($this->errorAction, function ($error = null) use ($observer) {
                $ex = $error instanceof \Exception ? $error : new \Exception($error);
                $observer->onError($ex);
            });
        }

        if ($this->completeAction) {
            $this->object->on($this->completeAction, function () use ($observer) {
                $observer->onCompleted();
            });
        }

        return new EmptyDisposable();
    }
}
