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

class FakeCompletePurchaseResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        return $this->data['success'] ?? false;
    }

    public function getTransactionReference(): ?string
    {
        return $this->data['transactionReference'] ?? null;
    }

    public function getMessage(): ?string
    {
        return $this->data['message'] ?? null;
    }
}
