<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\TicketCategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function(){
        Route::post('/logout', [AuthController::class, 'logout']);
        
        Route::post('/events', [EventController::class, 'store']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);
        
        Route::post('/events/{event_id}/ticket-categories', [TicketCategoryController::class, 'store']);
        Route::get('/events/ticket-categories', [TicketCategoryController::class, 'index']);
        Route::get('/events/{event_id}/ticket-categories', [TicketCategoryController::class, 'show']);
        Route::get('/events/{event_id}/ticket-categories/{ticket_id}', [TicketCategoryController::class, 'showDetail']);

        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
    });

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
});
