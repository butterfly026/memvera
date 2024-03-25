<?php

namespace App\Notifications\User;

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

class Create extends Mailable
{
    /**
     * @param  object  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the mail representation of the notification.
     */
    public function build()
    {
        return $this
            ->to($this->user->email)
            ->subject(trans('app.mail.user.create-subject'))
            ->view('emails.users.create', [
                'user_name' => $this->user->name,
            ]);
    }
}
