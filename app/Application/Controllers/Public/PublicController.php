<?php

namespace App\Application\Controllers\Public;

use App\Application\Controllers\AppController;
use App\Domain\Services\Email\PostmarkService;;
use Illuminate\Http\Request;

class PublicController extends AppController
{
    public function viewStart()
    {
        return view('public.start');
    }

    public function viewPricing()
    {
        return view('public.pricing');
    }

    public function submitContactUs(Request $request)
    {
        try {
            \Log::info('submitContactUs: Request received', ['request' => $request->all()]);

            $validatedData = $request->validate([
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'message' => 'required|string|max:1000',
            ]);

            \Log::info('submitContactUs: Request validated', ['validatedData' => $validatedData]);

            // Send email
            $postMarkEmailService = new PostmarkService("75620aa0-bc1a-4a80-b1d1-4a4b8a8183e4");
            $postMarkEmailService->sendEmailWithTemplate(
                "hello@sparkfore.com",
                39001700, // not using an new template for this, using an existing template id as requested by client
                [
                    'url' => "<p><strong>New Contact Us Request from Tooling</strong></p>
                            <p><strong>First Name:</strong> {$validatedData['firstname']}</p>
                            <p><strong>Last Name:</strong> {$validatedData['lastname']}</p>
                            <p><strong>Email:</strong> {$validatedData['email']}</p>
                            <p><strong>Message:</strong> {$validatedData['message']}</p>"
                ]
            );

            \Log::info('submitContactUs: Email sent successfully');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('submitContactUs: Error occurred', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'errors' => ['message' => $e->getMessage()]], 500);
        }
    }

}
