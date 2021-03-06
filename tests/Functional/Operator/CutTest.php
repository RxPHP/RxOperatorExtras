<?php

namespace Rx\React\Tests\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;
use Rx\Operator\CutOperator;


class CutTest extends FunctionalTestCase
{

    /**
     * @test
     */
    public function cut_never()
    {
        $xs = Observable::never();

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(PHP_EOL, $this->scheduler);
            });
        });
        $this->assertMessages([], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_empty()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(PHP_EOL, $this->scheduler);
            });
        });
        $this->assertMessages([onCompleted(233)], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_default_delimiter()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(201, "1" . PHP_EOL . "2"),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(PHP_EOL, $this->scheduler);
            });
        });
        $this->assertMessages([
            onNext(202, "1"),
            onNext(232, "2"),
            onCompleted(233)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_comma_delimiter()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(201, "1,2,3,4,5,,6"),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(',', $this->scheduler);
            });
        });
        $this->assertMessages([
            onNext(202, "1"),
            onNext(203, "2"),
            onNext(204, "3"),
            onNext(205, "4"),
            onNext(206, "5"),
            onNext(207, ""),
            onNext(232, "6"),
            onCompleted(233)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_comma_delimiter_empty_last()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(201, "1,2,3,4,5,6,"),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(',', $this->scheduler);
            });
        });
        $this->assertMessages([
            onNext(202, "1"),
            onNext(203, "2"),
            onNext(204, "3"),
            onNext(205, "4"),
            onNext(206, "5"),
            onNext(207, "6"),
            onNext(232, ""),
            onCompleted(233)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_comma_delimiter_empty_first()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(201, ",2,3,4,5,6,7"),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(',', $this->scheduler);
            });
        });
        $this->assertMessages([
            onNext(202, ""),
            onNext(203, "2"),
            onNext(204, "3"),
            onNext(205, "4"),
            onNext(206, "5"),
            onNext(207, "6"),
            onNext(232, "7"),
            onCompleted(233)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_comma_delimiter_skip_time()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(201, "1,2,3,"),
            onNext(214, "4,5,,6"),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(',', $this->scheduler);
            });
        });
        $this->assertMessages([
            onNext(202, "1"),
            onNext(203, "2"),
            onNext(204, "3"),
            onNext(215, "4"),
            onNext(216, "5"),
            onNext(217, ""),
            onNext(232, "6"),
            onCompleted(233)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_comma_delimiter_buffer_all()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(201, "1,"),
            onNext(202, "2,"),
            onNext(203, "3,"),
            onNext(204, "4"),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(',', $this->scheduler);
            });
        });
        $this->assertMessages([
            onNext(202, "1"),
            onNext(204, "2"),
            onNext(206, "3"),
            onNext(232, "4"),
            onCompleted(233)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_empty_string()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(201, ""),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(PHP_EOL, $this->scheduler);
            });
        });
        $this->assertMessages([
            onNext(232, ""),
            onCompleted(233)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_just_delimiter()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(201, PHP_EOL),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(PHP_EOL, $this->scheduler);
            });
        });
        $this->assertMessages([
            onNext(202, ""),
            onNext(232, ""),
            onCompleted(233)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_split_delimiter()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(201, "1-"),
            onNext(202, "-2-"),
            onNext(203, "-"),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator("--", $this->scheduler);
            });
        });
        $this->assertMessages([
            onNext(203, "1"),
            onNext(205, "2"),
            onNext(232, ""),
            onCompleted(233)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_error()
    {
        $error = new \Exception();

        $xs = $this->createHotObservable([
            onNext(150, 1),
            onError(201, $error),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithCreate(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(PHP_EOL, $this->scheduler);
            });
        });
        $this->assertMessages([
            onError(201, $error)
        ], $results->getMessages());
    }

    /**
     * @test
     */
    public function cut_dispose()
    {
        $xs = $this->createHotObservable([
            onNext(150, 1),
            onNext(202, "1" . PHP_EOL . "2"),
            onCompleted(230)
        ]);

        $results = $this->scheduler->startWithDispose(function () use ($xs) {
            return $xs->lift(function () {
                return new CutOperator(PHP_EOL, $this->scheduler);
            });
        }, 204);

        $this->assertMessages([
            onNext(203, "1")
        ], $results->getMessages());
    }
}
