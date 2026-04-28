<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactUs;

class ContactUsController extends Controller
{
    public function store(Request $request)
    {
        // 1️⃣  Validate the form input
        $validated = $request->validate([
            'name'    => 'required|min:2|max:100',
            'email'   => 'required|email',
            'phone'   => 'required|digits_between:7,15',
            'message' => 'required|min:5|max:2000',
            'topic' => 'required',
        ]);

        // 2️⃣  Create the record
        ContactUs::create($validated);

        // 3️⃣  Redirect back with a success flash message
        return back()->with('success', 'Thank you! We\'ll get back to you shortly.');
    }

   public function updateStatus(Request $request)
{
    $contact = ContactUs::find($request->id);

    if ($contact) {
        $contact->status = $request->status;
        $contact->save();
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false], 404);
}

// public function index(Request $request)
// {
//   $contacts = ContactUs::get();

//     return view('admin.contactUsInfo')->with('contacts', $contacts);
// }

// public function ContactUs(Request $request){
//     return view('contactUs');
// }
}
