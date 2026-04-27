<?php

$tenants = explode(',', env('TENANCY_TENANTS', 'test tenant 1,test tenant 2,test tenant 3'));

return [
    'tenants' => $tenants,
];
