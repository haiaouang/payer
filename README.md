# payer
[![Latest Stable Version](http://www.maiguoer.com/haiaouang/payer/stable.svg)](https://packagist.org/packages/haiaouang/payer)
[![License](http://www.maiguoer.com/haiaouang/payer/license.svg)](https://packagist.org/packages/haiaouang/payer)

支付管理laravel包开发，用于管理多个支付第三方驱动

## 安装
在你的终端运行以下命令

`composer require haiaouang/payer`

或者在composer.json中添加

`"haiaouang/payer": "1.0.*"`

然后在你的终端运行以下命令

`composer update`

在配置文件中添加 config/app.php

```php
    'providers' => [
        /**
         * 添加供应商
         */
        Hht\Payer\PayerServiceProvider::class,
    ],
```

## 依赖包

* haiaouang/support : https://github.com/haiaouang/support
* haiaouang/alipay : https://github.com/haiaouang/alipay
