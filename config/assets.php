<?php

/*
|--------------------------------------------------------------------------
| Assets Configuration
|--------------------------------------------------------------------------
| Reads from resources/assets.json - the single source of truth.
| To add/remove assets, edit resources/assets.json only.
|
| Structure:
| - css.app: CSS for authenticated app layout
| - css.auth: CSS for auth layout (login, register, etc.)
| - js.shared: JS loaded on ALL layouts
| - js.app: JS loaded on app layout only
| - js.auth: JS loaded on auth layout only
*/

$assetsPath = resource_path('assets.json');
$assets = json_decode(file_get_contents($assetsPath), true);

return $assets;
