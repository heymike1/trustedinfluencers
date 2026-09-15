<?php

namespace App\Livewire;

use App\Actions\Contact\SubmitContactRequest;
use App\Models\Creator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ContactCreatorForm extends Component
{
    public Creator $creator;

    public string $name = '';

    public string $email = '';

    public string $company = '';

    public string $subject = '';

    public string $message = '';

    public bool $sent = false;

    public function mount(Creator $creator): void
    {
        $this->creator = $creator;

        if ($user = auth()->user()) {
            $this->name = $user->name;
            $this->email = $user->email;
        }
    }

    public function submit(SubmitContactRequest $submit): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:20', 'max:3000'],
        ]);

        $key = 'contact:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('message', 'That\'s a lot of messages. Try again a bit later.');

            return;
        }

        RateLimiter::hit($key, 3600);

        $submit->handle($this->creator, $data, request()->ip());

        $this->reset('company', 'subject', 'message');
        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.contact-creator-form');
    }
}
