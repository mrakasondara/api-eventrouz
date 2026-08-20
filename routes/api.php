<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\TicketCategoryController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function(){
        Route::get('/profile', [UserController::class, 'show']);
        Route::put('/profile/personal-information', [UserController::class, 'updateDetails']);

        Route::post('/logout', [AuthController::class, 'logout']);
        
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{id}', [EventController::class, 'put']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);

        Route::get('/events/options', [EventController::class, 'options']);
        Route::get('/events/ticket-categories', [TicketCategoryController::class, 'index']);
        
        Route::post('/events/{event_id}/ticket-categories', [TicketCategoryController::class, 'store']);
        Route::delete('/events/{event_id}/ticket-categories/{ticket_id}', [TicketCategoryController::class, 'destroy']);
        Route::get('/events/{event_id}/ticket-categories', [TicketCategoryController::class, 'show']);
        Route::get('/events/{event_id}/ticket-categories/{ticket_id}', [TicketCategoryController::class, 'showDetail']);
        Route::put('/events/{event_id}/ticket-categories/{ticket_id}', [TicketCategoryController::class, 'put']);

        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
    });

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
});
