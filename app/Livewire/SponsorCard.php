<?php

namespace App\Livewire;

use App\Actions\Sponsors\PublishCard;
use App\Models\SponsorSlot;
use App\Support\SiteLogo;
use App\Support\Sponsorship;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The buyer's own page: the only thing they get after paying, and the only way in is the token in
 * their email. They fill in the card here, and it goes up the moment it is complete.
 */
#[Layout('components.layouts.app', ['band' => true])]
class SponsorCard extends Component
{
    public SponsorSlot $booking;

    public string $name = '';

    public string $url = '';

    public string $tagline = '';

    public string $logo_url = '';

    public string $tint = 'blue';

    public function mount(string $token): void
    {
        $this->booking = SponsorSlot::where('token', $token)->firstOrFail();

        $this->fill([
            'name' => $this->booking->name ?? '',
            'url' => $this->booking->url ?? '',
            'tagline' => $this->booking->tagline ?? '',
            'logo_url' => $this->booking->logo_url ?? '',
            'tint' => $this->booking->tint ?? 'blue',
        ]);
    }

    /** The logo follows the website: type one and the card has the other. */
    public function updatedUrl(): void
    {
        $this->logo_url = SiteLogo::forWebsite($this->url) ?? '';
    }

    public function save(PublishCard $publish): void
    {
        abort_if(in_array($this->booking->status, [SponsorSlot::CANCELLED, SponsorSlot::ENDED], true), 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'url' => ['required', 'url', 'max:2048'],
            'tagline' => ['required', 'string', 'min:4', 'max:120'],
            'tint' => ['required', Rule::in(array_keys(SponsorSlot::TINTS))],
        ]);

        $this->booking->fill($data)->forceFill(['logo_url' => SiteLogo::forWebsite($data['url'])])->save();

        $publish->handle($this->booking);
        $this->booking->refresh();

        session()->flash('success', $this->booking->isLive()
            ? 'Your card is up.'
            : 'Saved. We put it up as soon as your spot comes free.');
    }

    public function render(): View
    {
        return view('livewire.sponsor-card', [
            'days' => Sponsorship::days(),
            'tints' => array_keys(SponsorSlot::TINTS),
            'ahead' => $ahead = $this->booking->isQueued()
                ? SponsorSlot::queued()->where('paid_at', '<', $this->booking->paid_at)->count()
                : 0,
            // The card this booking replaces, and with it the day it can appear.
            'freedBy' => $this->booking->isQueued() ? Sponsorship::spotFreedBy($ahead) : null,
        ])->title('Your sponsor card');
    }
}
