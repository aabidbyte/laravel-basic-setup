<?php

use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Models\EmailTemplate\EmailTemplate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

beforeEach(function () {
    Mail::fake();
    $this->withoutMiddleware(VerifyCsrfToken::class);

    // Manually ensure mandatory template exists in central DB
    $layout = EmailTemplate::updateOrCreate(
        ['name' => 'default'],
        [
            'is_layout' => true,
            'status' => EmailTemplateStatus::PUBLISHED,
            'is_system' => true,
            'is_default' => true,
            'all_teams' => true,
            'type' => EmailTemplateType::MARKETING,
        ],
    );

    $layout->translations()->updateOrCreate(
        ['locale' => 'en_US'],
        [
            'subject' => 'Default Layout',
            'html_content' => '{!! $slot !!}',
            'text_content' => '{{ $slot }}',
        ],
    );

    $template = EmailTemplate::updateOrCreate(
        ['name' => 'Verify Email'],
        [
            'is_layout' => false,
            'status' => EmailTemplateStatus::PUBLISHED,
            'is_system' => true,
            'all_teams' => true,
            'layout_id' => $layout->id,
            'type' => EmailTemplateType::TRANSACTIONAL,
        ],
    );

    $template->translations()->updateOrCreate(
        ['locale' => 'en_US'],
        [
            'subject' => 'Verify Email',
            'html_content' => '<p>Verify your email.</p>',
            'text_content' => 'Verify your email.',
        ],
    );
});

test('new users can register', function () {
    $email = 'test-' . Str::random(8) . '@example.com';
    // Run on central domain
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => $email,
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasNoErrors();

    // It should redirect
    $response->assertRedirect();

    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();

    // Identify the tenant created for this user
    $tenant = $user->tenants()->first();
    expect($tenant)->not->toBeNull();

    $this->assertAuthenticated('web');
});

test('creates team and attaches user on registration', function () {
    $email = 'jane-' . Str::random(8) . '@example.com';
    // Run on central domain
    $response = $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => $email,
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasNoErrors();

    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();

    // Identify and initialize the tenant created for this user
    $tenant = $user->tenants()->first();
    expect($tenant)->not->toBeNull();

    asTenant($tenant);

    // User should have a team in this tenant
    $teams = Team::all();
    expect($teams)->toHaveCount(1);

    // Team should exist and have the user
    $team = $teams->first();
    expect($team)->toBeInstanceOf(Team::class);

    // Check membership using default connection (now switched to tenant)
    $userInTeam = DB::table('team_user')
        ->where('user_id', $user->id)
        ->where('team_id', $team->id)
        ->exists();
    expect($userInTeam)->toBeTrue();
});
