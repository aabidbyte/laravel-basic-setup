<?php

test('check environment', function () {
    echo 'APP_ENV: ' . config('app.env') . "\n";
    echo 'isTesting(): ' . (isTesting() ? 'true' : 'false') . "\n";
    echo 'runningUnitTests(): ' . (app()->runningUnitTests() ? 'true' : 'false') . "\n";

    expect(isTesting())->toBeTrue();
});
