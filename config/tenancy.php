<?php

$tenants = array_filter(explode(',', env('TENANCY_TENANTS', 'test tenant 1,test tenant 2,test tenant 3')));
if (empty($tenants)) {
    $tenants = ['test tenant 1', 'test tenant 2', 'test tenant 3'];
}

return [
    'tenants' => $tenants,
];
