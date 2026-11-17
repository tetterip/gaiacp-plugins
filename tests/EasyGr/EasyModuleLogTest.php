<?php

namespace Tests\Plugins\EasyGr;

use App\Models\ModuleLog;
use App\Plugins\PluginManager;
use App\Provisioning\RegistrarFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Plugins\EasyGr\EasyRegistrar;
use Tests\TestCase;

/** The Module log of easy.gr calls: the password and the EPP code never show. */
class EasyModuleLogTest extends TestCase
{
    use FakesEasy, RefreshDatabase;

    public function test_easy_password_and_epp_code_are_masked(): void
    {
        $plugins = app(PluginManager::class);
        $plugins->enable($plugins->find('easygr'));
        config(['gaiacp.debug.module_log' => true]);
        $this->fakeEasy();
        $this->easyDomains['acme.gr'] = ['expires' => '2027-03-15', 'epp' => 'Epp-Secret-42'];
        $registrar = app(RegistrarFactory::class)->make('easygr', ['username' => 'reseller', 'password' => 'EasyPass1']);

        $this->assertSame('Epp-Secret-42', $registrar->eppCode('acme.gr'));

        $log = ModuleLog::sole();
        $this->assertSame('easy', $log->module);
        $this->assertSame(EasyRegistrar::EPP_CODE.' EPP code', $log->action);
        $this->assertStringContainsString('username=reseller', $log->request);
        $this->assertStringNotContainsString('EasyPass1', $log->request);
        $this->assertStringNotContainsString('Epp-Secret-42', $log->response);
    }
}
