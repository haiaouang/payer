<?php namespace Hht\Payer;

use Closure;
use InvalidArgumentException;

use Hht\AliPay\Payer as AliPayer;
use Hht\AliPay\PayerInterface;
use Hht\AliPay\AdapterInterface;
use Hht\AliPay\PayerAdapter as AliPayerAdapter;
use Hht\Support\Contracts\Payer;
use Hht\Support\Contracts\Factory as FactoryContract;

class PayerManager implements FactoryContract
{
	/**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved payer drivers.
     *
     * @var array
     */
    protected $launchers = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new payer manager instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

	/**
     * Get a payer instance.
     *
     * @param  string  $name
     * @return \Hht\Support\Contracts\Payer
     */
    public function drive($name = null)
    {
        return $this->launcher($name);
    }

	/**
     * Get a payer instance.
     *
     * @param  string  $name
     * @return \Hht\Support\Contracts\Payer
     */
    public function launcher($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->launchers[$name] = $this->get($name);
    }


	/**
     * Attempt to get the launcher.
     *
     * @param  string  $name
     * @return \Hht\Support\Contracts\Payer
     */
    protected function get($name)
    {
        return isset($this->launchers[$name]) ? $this->launchers[$name] : $this->resolve($name);
    }

	/**
     * Resolve the given launcher.
     *
     * @param  string  $name
     * @return \Hht\Support\Contracts\Payer
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }

        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }

	/**
     * Call a custom driver creator.
     *
     * @param  array  $config
     * @return \Hht\Support\Contracts\Payer
     */
    protected function callCustomCreator(array $config)
    {
        $driver = $this->customCreators[$config['driver']]($this->app, $config);

        if ($driver instanceof Payer) {
            return $this->adapt($driver);
        }

        return $driver;
    }
	
	/**
     * Create an instance of the alipay driver.
     *
     * @param  array  $config
     * @return \Hht\Support\Contracts\Payer
     */
	public function createAlipayDriver(array $config)
	{
		$alipayConfig = $this->formatAlipayConfig($config);

        return $this->adapt($this->createPayer(
            new AliPayerAdapter($alipayConfig), $config
        ));
	}

	/**
     * Format the given alipay configuration with the default options.
     *
     * @param  array  $config
     * @return array
     */
	protected function formatAlipayConfig(array $config)
	{
		return $config;
	}

	/**
     * Create a Payer instance with the given adapter.
     *
     * @param  \Hht\AliPay\AdapterInterface  $adapter
     * @param  array  $config
     * @return \Hht\AliPay\PayerInterface
     */
    protected function createPayer(AdapterInterface $adapter, array $config)
    {
        return new AliPayer($adapter, count($config) > 0 ? $config : null);
    }

	/**
     * Adapt the payer implementation.
     *
     * @param  \Hht\AliPay\PayerInterface  $payer
     * @return \Hht\Support\Contracts\Payer
     */
    protected function adapt(PayerInterface $payer)
    {
        return new PayerAdapter($payer);
    }

	/**
     * Set the given launcher instance.
     *
     * @param  string  $name
     * @param  mixed  $launcher
     * @return void
     */
    public function set($name, $launcher)
    {
        $this->launchers[$name] = $launcher;
    }

	/**
     * Get the payer connection configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["payers.launchers.{$name}"];
    }

	/**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['payers.default'];
    }

	/**
     * Register a custom driver creator Closure.
     *
     * @param  string    $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

	/**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->launcher()->$method(...$parameters);
    }
}
