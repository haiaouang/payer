<?php namespace Hht\Payer;

use Illuminate\Support\ServiceProvider;

class PayerServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerPayer();
	}

	/**
	 * Register the driver based payer.
	 *
	 * @return void
	 */
	protected function registerPayer()
	{
		$this->registerManager();

		$this->app->singleton('payer.launcher', function () {
			return $this->app['payer']->launcher($this->getDefaultDriver());
		});
	}

	/**
	 * Register the payer manager.
	 *
	 * @return void
	 */
	protected function registerManager()
	{
		$this->app->singleton('payer', function () {
			return new PayerManager($this->app);
		});
	}

	/**
	 * Get the default pay driver.
	 *
	 * @return string
	 */
	protected function getDefaultDriver()
	{
		return $this->app['config']['payers.default'];
	}
}