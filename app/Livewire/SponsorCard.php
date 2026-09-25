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

    public ?string $logoNotice = null;

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

    /** Reads the logo straight off the website they gave us, so nobody has to upload anything. */
    public function findLogo(SiteLogo $logos): void
    {
        $this->validate(['url' => ['required', 'url', 'max:2048']]);

        $found = $logos->fetch($this->url);

        if ($found) {
            $this->logo_url = $found;
            $this->logoNotice = 'Found it. Not the right one? Paste a link to your own image.';
        } else {
            $this->logoNotice = 'Nothing usable on that site. Paste a link to an image, or leave it empty and we show your initials.';
        }
    }

    public function save(PublishCard $publish): void
    {
        abort_if(in_array($this->booking->status, [SponsorSlot::CANCELLED, SponsorSlot::ENDED], true), 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'url' => ['required', 'url', 'max:2048'],
            'tagline' => ['required', 'string', 'min:4', 'max:120'],
            'logo_url' => ['nullable', 'url', 'max:2048'],
            'tint' => ['required', Rule::in(array_keys(SponsorSlot::TINTS))],
        ]);

        $this->booking->fill($data + ['logo_url' => $data['logo_url'] ?: null])->save();

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
            'ahead' => $this->booking->isQueued()
                ? SponsorSlot::queued()->where('paid_at', '<', $this->booking->paid_at)->count()
                : 0,
            'nextFree' => Sponsorship::nextFreeAt(),
        ])->title('Your sponsor card');
    }
}
