<?php

namespace App\Http\Controllers;

use Log;
use App\Http\Requests\ContactRequest;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    public function sendContactMessage(ContactRequest $request)
    {
        $data = $request->only('name', 'email');
        $data['message'] = str_replace('/\\n/g', '<br>', $data['message']);
        // $data['messageLines'] = explode("\n", $request->get('message'));

        Mail::send('emails.contact', $data, function ($message) use ($data) {
            $message->subject('Contact From: '.$data['name'])
                ->replyTo($data['email']);
        });

        return back()
            ->with("success","Thank you for your message. It has been sent.");
    }

}
