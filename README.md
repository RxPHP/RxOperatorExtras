# Extra Operators and Observables for RxPHP


This library houses extra operators that are not part of the official [RxPHP](https://github.com/ReactiveX/RxPHP) library.


## Operators

 
* `cut` - splits a stream with a delimiter
* `repeatDelay` - repeats an observable sequence with when it completes after a delay
```PHP
    $observable->repeatDelay($initialDelay, $maxRetries, $maxRetryDelay, $retryDelayGrowth);
```
* `retryDelay` - retries an observable sequence when it errors after a delay
```PHP
    $observable->retryDelay($initialDelay, $maxRetries, $maxRetryDelay, $retryDelayGrowth);
```

## Observables

* `FromEventEmitterObservable` - converts event emitters like react streams to RxPHP Observables



