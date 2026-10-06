<?php

namespace App\Http\Controllers;

use App\Models\ViolationTicket;

class ViolationTicketController extends Controller
{
    public function receipt($id)
    {
        $ticket = ViolationTicket::with('issuedBy:user_id,full_name,role_id')->findOrFail($id);

        return view('violation-tickets.receipt', compact('ticket'));
    }
}
