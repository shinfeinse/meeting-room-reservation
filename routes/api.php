<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\Authentication\AuthenticationController;
use App\Http\Controllers\API\Masterdata\MeetingRoomController;
use App\Http\Controllers\API\Reservation\RoomReservationController;

Route::controller(AuthenticationController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});
         
Route::middleware('auth:sanctum')->group( function () {
    Route::resource('meeting-room', MeetingRoomController::class);
    Route::resource('room-reservation', RoomReservationController::class);
});