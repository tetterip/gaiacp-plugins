<?php

namespace Tests\Plugins\Discord;

use App\Chat\ChatSender;
use App\Models\ActivityLog;
use App\Models\User;
use App\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Plugins\Discord\DiscordSettings;
use Tests\TestCase;

/** Discord: GaiaCP's events posted to a channel as embeds (App\Chat). */
class DiscordTest extends TestCase
{
    use RefreshDatabase;

    private const DISCORD = 'https://discord.com/api/webhooks/123/abc';

    private function enable(string $id): void
    {
        $plugins = app(PluginManager::class);
        $plugins->enable($plugins->find($id));
    }

    public function test_discord_gets_an_embed_without_mentions(): void
    {
        $this->enable('discord');
        config(['gaiacp.discord.webhook_url' => self::DISCORD, 'gaiacp.discord.events' => ['server.down']]);
        Http::fake([self::DISCORD => Http::response('', 204)]);

        ActivityLog::record('server.down', 'Server pl1 is down (Uptime Kuma)');
        app(ChatSender::class)->sendDue();

        Http::assertSent(fn (Request $request) => $request['allowed_mentions'] === ['parse' => []]
            && $request['embeds'][0]['title'] === '🔴 Server down'
            && $request['embeds'][0]['description'] === 'Server pl1 is down (Uptime Kuma)'
            && $request['embeds'][0]['color'] === 0xDC2626);
    }

    public function test_its_settings_page_is_closed_until_it_is_enabled(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(DiscordSettings::getUrl())->assertForbidden();

        $this->enable('discord');
        $this->get(DiscordSettings::getUrl())->assertOk();
    }
}
