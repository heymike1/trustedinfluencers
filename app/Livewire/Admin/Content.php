<?php

namespace App\Livewire\Admin;

use App\Enums\ContentType;
use App\Livewire\Concerns\HasNotice;
use App\Models\CreatorSocialAccount;
use App\Models\SocialContent;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Imported content. These rows come from the platforms and are replaced on the next sync, so
 * they are shown as they are and can only be deleted, never edited by hand.
 */
#[Layout('components.layouts.admin')]
class Content extends Component
{
    use HasNotice;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: null)]
    public ?int $account = null;

    public ?int $inspecting = null;

    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function inspect(int $id): void
    {
        $this->inspecting = $this->inspecting === $id ? null : $id;
    }

    public function destroy(int $id): void
    {
        SocialContent::findOrFail($id)->delete();
        $this->notify('success', 'Content row deleted. It comes back on the next sync unless the account is disconnected.');
    }

    public function render(): View
    {
        $contents = SocialContent::query()
            ->with('socialAccount.creator')
            ->when($this->account, fn ($q) => $q->where('creator_social_account_id', $this->account))
            ->when($this->type !== '', fn ($q) => $q->where('content_type', $this->type))
            ->when($this->search !== '', fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('published_at')
            ->paginate(25);

        return view('livewire.admin.content', [
            'contents' => $contents,
            'types' => ContentType::cases(),
            'accountModel' => $this->account ? CreatorSocialAccount::with('creator')->find($this->account) : null,
            'open' => $this->inspecting ? SocialContent::find($this->inspecting) : null,
        ])->title('Content');
    }
}
