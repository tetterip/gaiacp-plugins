<?php

namespace Tests\Plugins\Aade;

use App\Models\ModuleLog;
use App\Plugins\PluginManager;
use App\Support\ModuleLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Plugins\Aade\AadeRegistry;
use Tests\TestCase;

/** The Module log of ΑΑΔΕ lookups: the password never shows. */
class AadeModuleLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_aade_password_is_masked(): void
    {
        $plugins = app(PluginManager::class);
        $plugins->enable($plugins->find('aade'));
        config(['gaiacp.debug.module_log' => true, 'gaiacp.aade' => ['enabled' => true, 'username' => 'ACMECODE', 'password' => 'p&ss<word>', 'afm_called_by' => null]]);
        Http::fake([AadeRegistry::ENDPOINT => Http::response('<env:Envelope/>', 500)]);

        rescue(fn () => AadeRegistry::lookup('094014298'), report: false);

        $log = ModuleLog::sole();
        $this->assertSame('aade', $log->module);
        $this->assertStringContainsString('ACMECODE', $log->request);
        $this->assertStringNotContainsString('p&amp;ss&lt;word&gt;', $log->request);
        $this->assertStringContainsString('<ns1:Password>'.ModuleLogger::MASK.'</ns1:Password>', $log->request);
    }
}
