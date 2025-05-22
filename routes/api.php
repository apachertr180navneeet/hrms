<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// You can define your API routes here.

Route::middleware('api')->get('/ping', function () {
    return response()->json(['message' => 'pong']);
});
