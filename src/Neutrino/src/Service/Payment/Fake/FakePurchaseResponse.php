<?php

declare(strict_types=1);

/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neutrino\Service\Payment\Fake;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class FakePurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    public function isSuccessful(): bool
    {
        return $this->data['success'] ?? false;
    }

    public function isRedirect(): bool
    {
        return $this->data['redirect'] ?? false;
    }

    public function getRedirectUrl(): string
    {
        return $this->data['redirectUrl'] ?? '';
    }

    public function getRedirectMethod(): string
    {
        return 'GET';
    }

    public function getRedirectData(): array
    {
        return [];
    }

    public function getTransactionReference(): ?string
    {
        return $this->data['token'] ?? null;
    }
}

