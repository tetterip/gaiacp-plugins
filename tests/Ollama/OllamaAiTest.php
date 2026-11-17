<?php

namespace Tests\Plugins\Ollama;

use App\Ai\Assistant;
use App\Models\AiUsage;
use App\Models\User;
use App\Plugins\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Plugins\Ollama\OllamaPlugin;
use Plugins\Ollama\OllamaSettings;
use Tests\TestCase;

/** Ollama as the AI assistant's service: OpenAI-style Chat Completions on the admin's own server, without a key. */
class OllamaAiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $plugins = app(PluginManager::class);
        $plugins->enable($plugins->find('ollama'));

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

    public function test_ollama_runs_on_its_own_address_without_a_key(): void
    {
        config(['gaiacp.ai.provider' => 'ollama']);
        Http::fake([
            'gpu.example.lan:11434/v1/models' => Http::response(['object' => 'list', 'data' => [['id' => 'qwen3:8b', 'object' => 'model']]]),
            'gpu.example.lan:11434/v1/chat/completions' => Http::response($this->answer('Please try port 587.')),
        ]);

        $this->assertTrue((new OllamaPlugin)->aiConfigured(), 'the default address is enough');
        $this->assertFalse((new OllamaPlugin)->enabledByDefault());

        Livewire::test(OllamaSettings::class)
            ->assertSchemaStateSet(['ollama' => ['base_url' => 'http://localhost:11434/v1', 'api_key' => null, 'model' => null]])
            ->fillForm(['ollama' => ['base_url' => 'http://gpu.example.lan:11434/v1', 'model' => 'qwen3:8b']])
            ->call('save')
            ->assertHasNoFormErrors()
            ->callAction('testConnection')
            ->assertNotified('Connected: qwen3:8b');

        $this->assertSame('Please try port 587.', app(Assistant::class)->improve('pls try port 587', 'fix'));
        Http::assertSent(fn (Request $request) => $request->url() === 'http://gpu.example.lan:11434/v1/chat/completions'
            && ! $request->hasHeader('Authorization') && $request['model'] === 'qwen3:8b');
        $this->assertSame('ollama', AiUsage::query()->sole()->provider);
    }
}
