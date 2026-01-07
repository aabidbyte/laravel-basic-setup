use App\Livewire\Bases\BasePageComponent;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Users\ActivationService;
use Illuminate\Validation\Rules\Password;

new class extends BasePageComponent {
public ?string $token = null;
public ?User $user = null;
public bool $tokenValid = false;
public bool $activated = false;

public string $password = '';
public string $password_confirmation = '';

/**
* Mount the component.
*/
public function mount(string $token): void
{
$this->token = $token;

$activationService = app(ActivationService::class);
$this->user = $activationService->findUserByToken($token);

$this->tokenValid = $this->user !== null;
}

/**
* Get the validation rules.
*
* @return array<string, mixed>
    */
    protected function rules(): array
    {
    return [
    'password' => ['required', 'string', Password::defaults(), 'confirmed'],
    ];
    }

    /**
    * Activate the account.
    */
    public function activateAccount(): void
    {
    if (!$this->tokenValid || !$this->user) {
    NotificationBuilder::make()->title(__('ui.auth.activation.invalid_token'))->error()->send();
    return;
    }

    $this->validate();

    try {
    $activationService = app(ActivationService::class);
    $activationService->activateWithPassword($this->user, $this->password, $this->token);

    $this->activated = true;

    NotificationBuilder::make()->title(__('ui.auth.activation.success'))->success()->send();
    } catch (\Exception $e) {
    NotificationBuilder::make()->title(__('ui.auth.activation.error'))->content($e->getMessage())->error()->send();
    }
    }

    /**
    * Render the component.
    */
    public function render()
    {
    return view('pages.auth.activate-content')->layout('layouts.auth', [
    'title' => __('ui.auth.activation.title'),
    ]);
    }
    }; ?> ?> ?> ?>
