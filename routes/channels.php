<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// قناة البث العام - متاحة للجميع (الشاشات)
Broadcast::channel('displays', function ($user, $screenCode) {
    return true; // السماح للجميع بالاستماع
});

// قناة شاشة محددة - متاحة للشاشة المحددة فقط
Broadcast::channel('displays.{screenCode}', function ($user, $screenCode) {
    return true; // السماح للشاشة المحددة بالاستماع
});
