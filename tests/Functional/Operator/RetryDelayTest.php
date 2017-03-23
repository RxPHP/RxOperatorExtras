<?php

namespace Rx\React\Tests\Functional\Operator;

use Rx\Exception\Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Operator\RetryDelayOperator;

class RetryDelayTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function repeatDelay_never()
    {
        $xs = Observable::never();

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new RetryDelayOperator(100, 1, 1000, 0, $this->scheduler);
            });
        });
        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function retryDelay_empty()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(230, new Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new RetryDelayOperator(100, 1, 1000, 0, $this->scheduler);
            });
        });
        $this->assertMessages([], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230),
            subscribe(330, 1000)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function retryDelay_once()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onError(30, new Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new RetryDelayOperator(100, 1, 1000, 0, $this->scheduler);
            });
        });

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(340, 1),
            onNext(350, 2),
            onCompleted(360)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230),
            subscribe(330, 360)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function retryDelay_twice()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onError(30, new Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new RetryDelayOperator(100, 2, 1000, 0, $this->scheduler);
            });
        });

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(340, 1),
            onNext(350, 2),
            onNext(371, 1),
            onNext(381, 2),
            onCompleted(391)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230),
            subscribe(330, 360),
            subscribe(361, 391)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function retryDelay_twice_grow_one()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onError(30, new Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new RetryDelayOperator(100, 2, 1000, 1, $this->scheduler);
            });
        });

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(340, 1),
            onNext(350, 2),
            onNext(470, 1),
            onNext(480, 2),
            onCompleted(490)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230),
            subscribe(330, 360),
            subscribe(460, 490)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function retryDelay_twice_grow_one_point_five()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onError(30, new Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new RetryDelayOperator(100, 2, 1000, 1.5, $this->scheduler);
            });
        });

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(340, 1),
            onNext(350, 2),
            onNext(520, 1),
            onNext(530, 2),
            onCompleted(540)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230),
            subscribe(330, 360),
            subscribe(510, 540)
        ], $xs->getSubscriptions());
    }

    /**
     * @test
     */
    public function retryDelay_twice_max_delay_100()
    {
        $xs = $this->createColdObservable([
            onNext(10, 1),
            onNext(20, 2),
            onError(30, new Exception())
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new RetryDelayOperator(200, 2, 100, 100, $this->scheduler);
            });
        });

        $this->assertMessages([
            onNext(210, 1),
            onNext(220, 2),
            onNext(340, 1),
            onNext(350, 2),
            onNext(470, 1),
            onNext(480, 2),
            onCompleted(490)
        ], $results->getMessages());

        $this->assertSubscriptions([
            subscribe(200, 230),
            subscribe(330, 360),
            subscribe(460, 490)
        ], $xs->getSubscriptions());
    }
}
