<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketBookingController;




Route::get('/', [TicketBookingController::class, 'loadHome'])
    ->name('home');


Route::post('/book-tickets', [TicketBookingController::class, 'BookTickets'])
    ->name('book-tickets');


Route::get('/ticket-information', [TicketBookingController::class, 'loadTicketInfo'])
    ->name('ticket-information');


Route::get('/reset-all', [TicketBookingController::class, 'resetDB'])
    ->name('reset-all');

