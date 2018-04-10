<?php namespace Datlv\Meta\Tests\Stubs;
/**
 * Class TestCase
 * @package Datlv\Meta\Tests\Stubs
 * @author Minh Bang
 */
class TestCase extends \Datlv\Kit\Testing\TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/../migrations'),
        ]);
    }

    /**
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return array_merge(
            parent::getPackageProviders($app),
            [
                \Datlv\Meta\ServiceProvider::class,
            ]
        );
    }
}