<?php

use App\Mail\ContactSubmitted;
use Illuminate\Support\Facades\Mail;

test('contact page can be rendered', function () {
    $this->get(route('contact'))
        ->assertSuccessful()
        ->assertSee('Let')
        ->assertSee('Send Message');
});

test('contact form can be submitted', function () {
    Mail::fake();

    $this->post(route('contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Project inquiry',
        'message' => 'I would like to discuss a new website project.',
    ])
        ->assertRedirect(route('contact'))
        ->assertSessionHas('success');

    Mail::assertSent(ContactSubmitted::class, function (ContactSubmitted $mail) {
        return $mail->hasTo(config('services.contact.recipient'))
            && $mail->name === 'Jane Doe'
            && $mail->email === 'jane@example.com'
            && $mail->contactSubject === 'Project inquiry'
            && $mail->body === 'I would like to discuss a new website project.';
    });
});

test('contact form requires valid input', function () {
    $this->post(route('contact.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
});
