<?php

namespace Tests\Plugins\Kimi;

use App\Ai\Assistant;
use App\Models\User;
use App\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Plugins\Kimi\KimiPlugin;
use Plugins\Kimi\KimiSettings;
use Tests\TestCase;

/** Kimi as the AI assistant's service: OpenAI-style Chat Completions with the admin's key. */
class KimiAiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $plugins = app(PluginManager::class);
        $plugins->enable($plugins->find('kimi'));
        config(['gaiacp.kimi.api_key' => 'sk-kimi']);

        $this->actingAs(User::factory()->create());
    }

    private function answer(string $text): array
    {
        return [
            'id' => 'chatcmpl-1',
            'object' => 'chat.completion',
            'model' => 'model',
            'choices' => [['index' => 0, 'message' => ['role' => 'assistant', 'content' => $text], 'finish_reason' => 'stop']],
            'usage' => ['prompt_tokens' => 200, 'completion_tokens' => 30, 'total_tokens' => 230],
        ];
    }

    public function test_kimi_uses_its_own_address_and_the_model_typed_in(): void
    {
        config(['gaiacp.ai.provider' => 'kimi', 'gaiacp.kimi.model' => 'kimi-k3']);
        Http::fake(['api.moonshot.ai/v1/chat/completions' => Http::response($this->answer('Γεια σας'))]);

        $this->assertSame('Γεια σας', app(Assistant::class)->translate('Hello', 'el'));

        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.moonshot.ai/v1/chat/completions'
            && $request->header('Authorization') === ['Bearer sk-kimi'] && $request['model'] === 'kimi-k3');
    }

    public function test_test_connection_lists_the_models_and_warns_about_a_missing_one(): void
    {
        Http::fake(['api.moonshot.ai/v1/models' => Http::response(['object' => 'list', 'data' => [['id' => 'kimi-k3', 'object' => 'model'], ['id' => 'kimi-k2.6', 'object' => 'model']]])]);

        Livewire::test(KimiSettings::class)
            ->fillForm(['kimi' => ['api_key' => 'sk-typed', 'model' => 'kimi-old']])
            ->callAction('testConnection')
            ->assertNotified('Connected, but this key has no model kimi-old');

        Http::assertSent(fn (Request $request) => $request->method() === 'GET' && $request->header('Authorization') === ['Bearer sk-typed']);
        $this->assertSame(['kimi-k2.6', 'kimi-k3'], (new KimiPlugin)->knownModels(), 'the model field offers what was read');

        Livewire::test(KimiSettings::class)
            ->fillForm(['kimi' => ['model' => 'kimi-k3']])
            ->callAction('testConnection')
            ->assertNotified('Connected: kimi-k3');
    }

    public function test_settings_are_saved_and_new_installs_start_with_it_off(): void
    {
        Livewire::test(KimiSettings::class)
            ->assertSchemaStateSet(['kimi' => ['api_key' => null, 'model' => null]])
            ->fillForm(['kimi' => ['api_key' => 'sk-new', 'model' => 'kimi-k3']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('sk-new', config('gaiacp.kimi.api_key'));
        $this->assertSame('kimi-k3', (new KimiPlugin)->model());
        $this->assertFalse((new KimiPlugin)->enabledByDefault());
    }
}
