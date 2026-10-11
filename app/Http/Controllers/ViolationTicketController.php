<?php

namespace App\Http\Controllers;

use App\Models\ViolationTicket;

class ViolationTicketController extends Controller
{
    public function receipt($id)
    {
        // withTrashed(): an archived ticket still has to be printable, which is
        // the point of keeping it. A ticket that never existed is still a 404.
        $ticket = ViolationTicket::withTrashed()
            ->with('issuedBy:user_id,full_name,role_id')
            ->findOrFail($id);

        return view('violation-tickets.receipt', compact('ticket'));
    }
}
