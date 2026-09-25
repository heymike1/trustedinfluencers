<?php

namespace App\Livewire\Admin;

use App\Enums\Platform;
use App\Livewire\Concerns\HasNotice;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Everything that changes how the app behaves and is not a secret. Values are stored as config
 * overrides, so the .env only carries credentials.
 */
#[Layout('components.layouts.admin')]
class SettingsScreen extends Component
{
    use HasNotice;

    public array $form = [];

    public function mount(): void
    {
        $this->form = [
            'tagline' => (string) config('app.tagline'),
            'platforms' => config('social.enabled_platforms', []),
            'refresh_every_hours' => (int) config('social.sync.refresh_every_hours'),
            'manual_cooldown_minutes' => (int) config('social.sync.manual_cooldown_minutes'),
            'stale_after_days' => (int) config('social.sync.stale_after_days'),
            'content_limit' => (int) config('social.sync.content_limit'),
            'forward_to_public_email' => (bool) config('social.contact.forward_to_public_email'),
        ];
    }

    public function togglePlatform(string $platform): void
    {
        $this->form['platforms'] = in_array($platform, $this->form['platforms'], true)
            ? array_values(array_diff($this->form['platforms'], [$platform]))
            : [...$this->form['platforms'], $platform];
    }

    public function save(Settings $settings): void
    {
        $data = $this->validate([
            'form.tagline' => ['required', 'string', 'max:160'],
            'form.platforms' => ['array'],
            'form.platforms.*' => ['string', 'in:youtube,instagram,x'],
            'form.refresh_every_hours' => ['required', 'integer', 'min:1', 'max:168'],
            'form.manual_cooldown_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'form.stale_after_days' => ['required', 'integer', 'min:1', 'max:90'],
            'form.content_limit' => ['required', 'integer', 'min:5', 'max:200'],
            'form.forward_to_public_email' => ['boolean'],
        ])['form'];

        $settings->set('app.tagline', $data['tagline']);
        $settings->set('social.enabled_platforms', array_values($data['platforms']));
        $settings->set('social.sync.refresh_every_hours', $data['refresh_every_hours']);
        $settings->set('social.sync.manual_cooldown_minutes', $data['manual_cooldown_minutes']);
        $settings->set('social.sync.stale_after_days', $data['stale_after_days']);
        $settings->set('social.sync.content_limit', $data['content_limit']);
        $settings->set('social.contact.forward_to_public_email', $data['forward_to_public_email']);

        $this->notify('success', 'Settings saved.');
    }

    public function resetToFile(Settings $settings): void
    {
        foreach (array_keys(Settings::EDITABLE) as $key) {
            $settings->forget($key);
        }

        $this->notify('success', 'Back to the values in the config files and .env.');
        $this->redirectRoute('admin.settings');
    }

    public function render(): View
    {
        return view('livewire.admin.settings', [
            'platforms' => Platform::cases(),
            'fromEnv' => [
                'Connector driver' => config('social.driver'),
                'App URL' => config('app.url'),
                'Mail' => config('mail.default'),
                'Queue' => config('queue.default'),
                'Analytics' => config('services.datafast.website_id') ? 'on' : 'off',
                'Error tracking' => config('sentry.dsn') ? 'on' : 'off',
            ],
        ])->title('Settings');
    }
}
