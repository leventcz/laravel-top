<?php

use Leventcz\Top\Data\EventCounter;

it('counts events', function () {
    $counter = new EventCounter();
    $counter->add('foo');
    $counter->add('foo', 4);
    $counter->add('bar', 2);
    $counter->add('bar', 4);

    expect($counter->get())->toBe(['foo' => 5, 'bar' => 6]);
});

it('flushes events', function () {
    $counter = new EventCounter();
    $counter->add('foo', 123);
    $counter->flush();

    expect($counter->get())->toBe([]);
});
