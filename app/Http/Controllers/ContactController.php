<?php

namespace App\Http\Controllers;

use Log;
use Mail;
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
        $data = $request->only('name', 'email', 'message');
        $data['text'] = str_replace('/\\n/g', '<br>', $data['message']);

        Mail::send('emails.contact', $data, function ($m) use ($data) {
            // $m->from($data['email'], $data['name']);
            $m->to('admin@contactmyreps.org');
            $m->subject('Contact From: '.$data['name']);
            $m->replyTo($data['email']);
        });

        return response()->json(["status" => "success"]);
    }

}
