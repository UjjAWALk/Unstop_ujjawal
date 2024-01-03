<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TicketsBooking;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;

class TicketBookingController extends Controller
{
    public $currently_booked = [];

    
    function areSeatsAvailable($row, $numSeats)
    {
        $consecutiveCount = 0;
        foreach ($row as $seat) {
            if ($seat == '0') { // Check if seat is available
                $consecutiveCount++;
                if ($consecutiveCount == $numSeats) {
                    return true;
                }
            } else {
                $consecutiveCount = 0;
            }
        }
        return false;
    }

   
    function reserveSeats(&$coach, $numSeats)
    {

        $rows = count($coach);

        
        for ($i = 0; $i < $rows; $i++) {
            if ($this->areSeatsAvailable($coach[$i], $numSeats)) {
                // Reserve seats in the row

                for ($j = 0; $j < count($coach[$i]); $j++) {
                    $temp = [];
                    if ($coach[$i][$j] == '0' && $numSeats > 0) {
                        $coach[$i][$j] = '1';
                        $temp[] = $i;
                        $temp[] = $j;
                        $this->currently_booked[] = $temp;
                        $numSeats--;
                    }
                }
                return;
            }


        }

        
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < count($coach[$i]); $j++) {
                $temp = [];
                if ($coach[$i][$j] == '0' && $numSeats > 0) {
                    $coach[$i][$j] = '1';
                    $temp[] = $i;
                    $temp[] = $j;
                    $this->currently_booked[] = $temp;
                    $numSeats--;
                }
            }
        }
        ;
    }


   
    function loadHome(Request $request)
    {
        try {
            return view('welcome');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $request->session()->flash('error', 'Failed to load homepage.');
        }
    }

   
    function BookTickets(Request $request)
    {
        try {

            
            $ticketData = $request->validate([
                'tickets-input' => 'required|numeric|min:1|max:7',
            ]);

            $numSeats = $ticketData['tickets-input'];

            $coach = [];
            $cols = 7;
            $seat_index = 0;
            
           
            $tickets = TicketsBooking::first();

            
            $available_seats = substr_count($tickets->all_seats, '0');

            
            if ($available_seats < $numSeats) {
                if ($available_seats == 0) {
                    $request->session()->flash('error', 'Sorry, Seats are not available at moment.');
                } else
                    $request->session()->flash('error', 'Sorry, ' . $numSeats . ' seats are not currently available. Only ' . $available_seats . ' are available.');
                return back();
            }


          
            for ($i = 0; $i < 12; $i++) {
                if ($i == 11) $cols = 3;
                for ($j = 0; $j < $cols; $j++) {
                    $coach[$i][$j] = $tickets->all_seats[$seat_index];
                    $seat_index++;
                }
            }



            $final_seats = '';

           
            $this->reserveSeats($coach, $numSeats);

            
            foreach ($coach as $row) {
                foreach ($row as $seat) {
                    $final_seats = $final_seats . $seat;
                }
            }
            
            
            $tickets->all_seats = $final_seats;
            $tickets->save();




           
            $data['currently_booked'] = $this->currently_booked;


            $seat_numbers = [];
            $column_count = 7;

            
            foreach ($data['currently_booked'] as $seat_indexes) {
                $final_position = ($seat_indexes[0] * $column_count) + $seat_indexes[1];
                $seat_numbers[] = $final_position;
            }

            
            Session::put('seat_numbers', $seat_numbers);
            Session::put('total_tickets', $numSeats);

            return redirect()->route('ticket-information');

        } catch (Exception $e) {
            Log::error($e->getMessage());
            $request->session()->flash('error', 'Failed to reserve seats.');
        }
    }
    
    function loadTicketInfo(Request $request)
    {
        try {
         
            $data['currently_booked'] = Session::get('seat_numbers', []);
            $data['total_tickets'] = Session::get('total_tickets');
            
           
            $tickets = TicketsBooking::first();
            $data['already_booked'] = $tickets;

            $final_output = [];
            $seat_string = $tickets->all_seats;

            for ($i = 0; $i < strlen($seat_string); $i++) {
                $temp = [];
                $temp[] = $seat_string[$i];
               
                if (in_array($i, $data['currently_booked'])) {
                    $temp[] = true;
                } else {
                    $temp[] = false;
                }
                
                $final_output[] = $temp;
            }

           
            $data['booked_seats'] = $final_output;

            return view('ticket-info', $data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $request->session()->flash('error', 'Failed to load ticket information.');
        }
    }

    
    
    function resetDB(Request $request)
    {
        try {
            TicketsBooking::truncate();

           
            Artisan::call('db:seed');
            
            Session::flush();

            $request->session()->flash('success', 'All progress erased successfully.');

            return back();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $request->session()->flash('error', 'Failed to reset Database.');
        }
    }



}