<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\HasNotice;
use App\Models\SponsorSlot;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Books and edits the sponsor cards in the rails beside the page.
 */
#[Layout('components.layouts.admin')]
class Sponsors extends Component
{
    use HasNotice;

    public ?int $editing = null;

    public array $form = [];

    public function mount(): void
    {
        $this->resetForm();
        $this->mountSettings();
    }

    private function resetForm(): void
    {
        $this->editing = null;
        $this->form = [
            'name' => '',
            'tagline' => '',
            'url' => '',
            'logo_url' => '',
            'tint' => 'blue',
            'side' => 'left',
            'sort_order' => 0,
            'is_active' => true,
            'starts_at' => '',
            'ends_at' => '',
        ];
    }

    public function edit(int $id): void
    {
        $slot = SponsorSlot::findOrFail($id);

        $this->editing = $slot->id;
        $this->form = [
            'name' => $slot->name,
            'tagline' => $slot->tagline,
            'url' => $slot->url,
            'logo_url' => $slot->logo_url ?? '',
            'tint' => $slot->tint,
            'side' => $slot->side,
            'sort_order' => $slot->sort_order,
            'is_active' => $slot->is_active,
            'starts_at' => $slot->starts_at?->format('Y-m-d') ?? '',
            'ends_at' => $slot->ends_at?->format('Y-m-d') ?? '',
        ];
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->resetValidation();
    }

    public function save(): void
    {
        $data = $this->validate([
            'form.name' => ['required', 'string', 'max:60'],
            'form.tagline' => ['required', 'string', 'max:120'],
            'form.url' => ['required', 'url', 'max:2048'],
            'form.logo_url' => ['nullable', 'url', 'max:2048'],
            'form.tint' => ['required', Rule::in(array_keys(SponsorSlot::TINTS))],
            'form.side' => ['required', Rule::in(['left', 'right'])],
            'form.sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'form.is_active' => ['boolean'],
            'form.starts_at' => ['nullable', 'date'],
            'form.ends_at' => ['nullable', 'date', 'after:form.starts_at'],
        ])['form'];

        $data['logo_url'] = $data['logo_url'] ?: null;
        $data['starts_at'] = $data['starts_at'] ?: null;
        $data['ends_at'] = $data['ends_at'] ?: null;

        if ($this->editing) {
            SponsorSlot::findOrFail($this->editing)->update($data);
            $this->notify('success', 'Sponsor updated.');
        } else {
            SponsorSlot::create($data);
            $this->notify('success', 'Sponsor added.');
        }

        $this->resetForm();
    }

    public function toggle(int $id): void
    {
        $slot = SponsorSlot::findOrFail($id);
        $slot->update(['is_active' => ! $slot->is_active]);
    }

    public function destroy(int $id): void
    {
        SponsorSlot::findOrFail($id)->delete();

        if ($this->editing === $id) {
            $this->resetForm();
        }

        $this->notify('success', 'Sponsor removed.');
    }

    /** The rail settings, saved as config overrides so the public pages pick them up at once. */
    public array $settings = [];

    public function mountSettings(): void
    {
        $this->settings = [
            'slots_per_rail' => (int) config('social.sponsors.slots_per_rail'),
            'price' => (string) config('social.sponsors.price'),
            'period' => (string) config('social.sponsors.period'),
            'contact' => (string) config('social.sponsors.contact'),
        ];
    }

    public function saveSettings(Settings $settings): void
    {
        $data = $this->validate([
            'settings.slots_per_rail' => ['required', 'integer', 'min:0', 'max:12'],
            'settings.price' => ['nullable', 'string', 'max:40'],
            'settings.period' => ['required', 'string', 'max:40'],
            'settings.contact' => ['required', 'email', 'max:255'],
        ])['settings'];

        $settings->set('social.sponsors.slots_per_rail', $data['slots_per_rail']);
        $settings->set('social.sponsors.price', $data['price'] ?: null);
        $settings->set('social.sponsors.period', $data['period']);
        $settings->set('social.sponsors.contact', $data['contact']);

        $this->notify('success', 'Rail settings saved. The public pages use them right away.');
    }

    public function render(): View
    {
        return view('livewire.admin.sponsors', [
            'slots' => SponsorSlot::orderBy('side')->orderBy('sort_order')->orderBy('id')->get(),
            'tints' => array_keys(SponsorSlot::TINTS),
            'perRail' => (int) config('social.sponsors.slots_per_rail'),
        ])->title('Sponsors');
    }
}
