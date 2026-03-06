<?php

declare(strict_types=1);

namespace Neutrino\Service\Payment;

use Omnipay\Common\GatewayInterface;
use Omnipay\Omnipay;
use Psr\Container\ContainerInterface;

class PaymentServiceFactory
{
    public function __invoke(ContainerInterface $container): PaymentService
    {
        $config = $container->get('config');

        $gatewayName = $config['payment']['gateway'] ?? 'Dummy';

        /** @var GatewayInterface $gateway */
        $gateway = Omnipay::create($gatewayName);

        // Configure if needed
        if ($gatewayName === 'Dummy') {
            $gateway->initialize([]);
        }

        return new PaymentService($gateway);
    }
}
