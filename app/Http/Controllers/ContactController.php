<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactSubmitted;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact');
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Mail::to(config('services.contact.recipient'))
            ->send(new ContactSubmitted(
                name: $validated['name'],
                email: $validated['email'],
                contactSubject: $validated['subject'],
                body: $validated['message'],
            ));

        return redirect()
            ->route('contact')
            ->with('success', 'Thank you for reaching out! We will get back to you soon.');
    }
}
