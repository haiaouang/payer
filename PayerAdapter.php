<?php namespace Hht\Payer;

use Hht\AliPay\PayerInterface;
use Hht\Support\Contracts\Order;
use Hht\Support\Contracts\Payer as PayerContract;

class PayerAdapter implements PayerContract
{
	/**
     * The pusher instance.
     *
     * @var \Hht\AliPay\PayerInterface
     */
    protected $driver;

	/**
     * Create a new payer adapter instance.
     *
     * @param  \Hht\AliPay\PayerInterface  $driver
     * @return void
     */
	public function __construct(PayerInterface $driver)
    {
        $this->driver = $driver;
    }

	/**
     * Get a pusher instance.
     *
     * @return \Hht\AliPay\PayerInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

	/**
     * Make pay order sign.
     *
     * @param  \Hht\Support\Contracts\Order $order
     * @return string
     */
	public function makeSign(Order $order)
	{
		return $this->driver->makeSign($order);
	}
	
	/**
     * Create sdk order.
     *
     * @param  \Hht\Support\Contracts\Order $order
     * @return \Hht\Support\Contracts\Result
     */
	public function createSDKOrder(Order $order)
	{
		return $this->driver->createSDKOrder($order);
	}
	
	/**
     * Verify pay order sign.
     *
     * @param  array    $param
     * @return boolean
     */
	public function verifySign($param)
	{
		return $this->driver->verifySign($param);
	}
	
	/**
     * Pass dynamic methods call onto Payer.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->driver, $method], $parameters);
    }
}