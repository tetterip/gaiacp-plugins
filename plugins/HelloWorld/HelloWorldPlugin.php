<?php

namespace Plugins\HelloWorld;

use App\Plugins\Plugin;

class HelloWorldPlugin extends Plugin
{
    public function id(): string
    {
        return 'hello-world';
    }

    public function name(): string
    {
        return 'Hello World';
    }

    public function description(): string
    {
        return __('An example plugin from the plugin catalogue.');
    }

    public function category(): string
    {
        return 'Integrations';
    }
}
