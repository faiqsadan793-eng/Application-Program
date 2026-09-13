<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Pisahkan hasil kompilasi Blade pengujian dari server lokal yang mungkin
        // sedang berjalan, sehingga keduanya tidak berebut mengganti file di Windows.
        $compiledPath = storage_path('framework/testing/views');
        if (! is_dir($compiledPath)) {
            mkdir($compiledPath, 0777, true);
        }
        config(['view.compiled' => $compiledPath]);
    }
}
