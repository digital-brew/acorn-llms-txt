<?php

use Illuminate\Support\Facades\Route;
use Roots\AcornLlmsTxt\Http\Controllers\LlmsTxtController;

Route::get('/llms.txt', [LlmsTxtController::class, 'index']);
Route::get('/llms-full.txt', [LlmsTxtController::class, 'full']);
Route::get('/llms-small.txt', [LlmsTxtController::class, 'small']);
Route::get('/llms-sitemap.xml', [LlmsTxtController::class, 'sitemap']);
