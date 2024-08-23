<?php

use App\Http\Controllers\Api\WhoisxmlapiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('check-site')->group(function() {
    Route::get('/', [WhoisxmlapiController::class, 'checkSiteData']);
});
