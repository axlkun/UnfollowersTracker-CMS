<?php
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ArticlesController;
use App\Http\Controllers\Api\ContactUsController;
use Illuminate\Support\Facades\Route;

Route::get('articles',[ArticlesController::class,'index']);
Route::get('articles/{article:slug}',[ArticlesController::class,'show']);
Route::get('related-articles/{article:slug}',[ArticlesController::class,'relatedArticles']);

Route::get('settings',[SettingsController::class,'index']);

Route::post('contact-us',[ContactUsController::class,'store']);