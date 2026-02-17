<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Contact;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Validation, save, email send etc.
        $validated = $request->validate([
            'first_name'=>'required|string|max:255',
            'last_name' =>'required|string|max:255',
            'email'  =>'required|email|max:255',
            'phone' =>'nullable|string|max:20',
            'message' => 'required|string', // ✅ add this
        ]);

        // Save to database
        $contact = Contact::create($validated);

        $fullName = $validated['first_name'] . ' ' . $validated['last_name'];

        // Send mail
        Mail::raw(
            "New Contact Submission from {$fullName}\nEmail: {$validated['email']}\nPhone: {$validated['phone']}\nMessage: {$validated['message']}",
            function ($mail) use ($validated, $fullName) {
                $mail->to('rehanakabirmim@gmail.com') // client email
                     ->subject('New Contact Message from ' . $fullName)
                     ->from(config('mail.from.address'), config('mail.from.name'))
                     ->replyTo($validated['email'], $fullName);
            }
        );

        return response()->json([
            'message' => 'Your message has been sent and saved successfully!',
            'data' => $contact,
        ]);
    }






    /**
 * Display all contact messages
 */
public function index()
{
    try {
        $contacts = Contact::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'All contact messages retrieved successfully',
            'data' => $contacts,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve contacts: ' . $e->getMessage(),
        ], 500);
    }
}

}
