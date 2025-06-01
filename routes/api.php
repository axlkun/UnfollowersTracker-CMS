<?php
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ArticlesController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\DataController;
use Illuminate\Support\Facades\Route;

Route::get('articles',[ArticlesController::class,'index']);
Route::get('articles/{article:slug}',[ArticlesController::class,'show']);
Route::get('related-articles/{article:slug}',[ArticlesController::class,'relatedArticles']);
Route::post('image-upload',[ArticlesController::class,'storeImage']);

Route::get('settings',[SettingsController::class,'index']);

Route::post('contact-us',[ContactUsController::class,'store']);

Route::post('store',[DataController::class,'store']);
Route::get('unfollowers/{user}',[DataController::class,'getUnfollowers']);
Route::get('unfollowing/{user}',[DataController::class,'getNotFollowing']);
Route::get('pending-follow-requests/{user}', [DataController::class, 'getPendingFollowRequests']);