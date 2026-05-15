<?php

test('check environment', function () {
    expect(isTesting())->toBeTrue();
});
