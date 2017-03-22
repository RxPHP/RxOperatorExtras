# Extra Operators and Observables for RxPHP


This library houses extra operators that are not part of the official [RxPHP](https://github.com/ReactiveX/RxPHP) library.


## Operators

 
* `cut` - splits a stream with a delimiter
* `repeatDelay` - splits a stream with a delimiter
```PHP
    $observable->repeatDelay($initialDelay, $maxRetries, $maxRetryDelay, $retryDelayGrowth);
```


## Observables

* `FromEventEmitterObservable` - converts event emitters like react streams to RxPHP Observables



