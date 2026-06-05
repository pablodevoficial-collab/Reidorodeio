<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\FantasyLeagueOpeningReminderService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FantasyLeagueOpeningReminderServiceTest extends TestCase
{
    private string $fallbackPath;

    private ?string $fallbackBackup = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fallbackPath = storage_path('app/fantasy_league_opening_reminders.json');

        if (File::exists($this->fallbackPath)) {
            $this->fallbackBackup = File::get($this->fallbackPath);
        } else {
            $this->fallbackBackup = null;
        }

        File::delete($this->fallbackPath);
    }

    protected function tearDown(): void
    {
        if ($this->fallbackBackup !== null) {
            File::put($this->fallbackPath, $this->fallbackBackup);
        } else {
            File::delete($this->fallbackPath);
        }

        parent::tearDown();
    }

    public function test_resubscribe_clears_previous_sent_marker_in_fallback(): void
    {
        $service = new FantasyLeagueOpeningReminderService();
        $user = new User();
        $user->id = 366;
        $user->email = 'reidorodeio.host@gmail.com';
        $user->username = 'reidorodeio.host';

        $service->subscribe('20', $user->email, $user);

        File::put($this->fallbackPath, json_encode([
            [
                'slot_key' => '20',
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->username,
                'opened_notification_sent_at' => now()->toISOString(),
                'created_at' => now()->subMinute()->toISOString(),
                'updated_at' => now()->toISOString(),
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $service->subscribe('20', $user->email, $user);

        $items = json_decode((string) File::get($this->fallbackPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(1, $items);
        $this->assertNull($items[0]['opened_notification_sent_at']);
    }
}