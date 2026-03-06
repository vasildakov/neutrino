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

namespace Neutrino\Handler\Checkout;

use Laminas\Filter\Callback as CallbackFilter;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Callback;
use Laminas\Validator\Digits;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\InArray;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;

use function is_string;
use function trim;

/**
 * @phpstan-type CheckoutData array{
 *     planId: string,
 *     billingPeriod: 'monthly'|'yearly',
 *     firstName: string,
 *     lastName: string,
 *     email: string,
 *     address: string,
 *     address2?: string|null,
 *     city: string,
 *     country: string,
 *     zip: string,
 *     sameAddress?: '0'|'1',
 *     saveInfo?: '0'|'1',
 *     paymentMethod: 'credit'|'debit'|'paypal',
 *     cardNumber?: string|null,
 *     cardName?: string|null,
 *     expiryDate?: string|null,
 *     cvv?: string|null,
 *     terms: '1'
 * }
 * @extends InputFilter<array{
 *     planId: string,
 *     billingPeriod: 'monthly'|'yearly',
 *     firstName: string,
 *     lastName: string,
 *     email: string,
 *     address: string,
 *     address2?: string|null,
 *     city: string,
 *     country: string,
 *     zip: string,
 *     sameAddress?: '0'|'1',
 *     saveInfo?: '0'|'1',
 *     paymentMethod: 'credit'|'debit'|'paypal',
 *     cardNumber?: string|null,
 *     cardName?: string|null,
 *     expiryDate?: string|null,
 *     cvv?: string|null,
 *     terms: '1'
 * }>
 */
final class CheckoutInputFilter extends InputFilter
{
    public function __construct()
    {
        // Helpers
        $trimStrip = [
            ['name' => StripTags::class],
            ['name' => StringTrim::class],
        ];

        $this->add([
            'name'       => 'planId',
            'required'   => true,
            'filters'    => $trimStrip,
            'validators' => [
                ['name' => NotEmpty::class],
                // If it’s UUID:
                ['name' => Regex::class, 'options' => ['pattern' => '/^[0-9a-fA-F-]{16,64}$/']],
            ],
        ]);

        $this->add([
            'name'       => 'billingPeriod',
            'required'   => true,
            'filters'    => $trimStrip,
            'validators' => [
                ['name' => NotEmpty::class],
                ['name' => InArray::class, 'options' => ['haystack' => ['monthly', 'yearly']]],
            ],
        ]);

        $this->add([
            'name'       => 'firstName',
            'required'   => true,
            'filters'    => $trimStrip,
            'validators' => [
                ['name' => NotEmpty::class],
                ['name' => StringLength::class, 'options' => ['min' => 2, 'max' => 60]],
            ],
        ]);

        $this->add([
            'name'       => 'lastName',
            'required'   => true,
            'filters'    => $trimStrip,
            'validators' => [
                ['name' => NotEmpty::class],
                ['name' => StringLength::class, 'options' => ['min' => 2, 'max' => 60]],
            ],
        ]);

        $this->add([
            'name'       => 'email',
            'required'   => true,
            'filters'    => $trimStrip,
            'validators' => [
                ['name' => NotEmpty::class],
                ['name' => EmailAddress::class],
            ],
        ]);

        $this->add([
            'name'       => 'address',
            'required'   => true,
            'filters'    => $trimStrip,
            'validators' => [
                ['name' => NotEmpty::class],
                ['name' => StringLength::class, 'options' => ['min' => 5, 'max' => 120]],
            ],
        ]);

        $this->add([
            'name'       => 'address2',
            'required'   => false,
            'filters'    => $trimStrip,
            'validators' => [
                ['name' => StringLength::class, 'options' => ['max' => 120]],
            ],
        ]);

        $this->add([
            'name'       => 'city',
            'required'   => true,
            'filters'    => $trimStrip,
            'validators' => [
                ['name' => NotEmpty::class],
                ['name' => StringLength::class, 'options' => ['min' => 2, 'max' => 80]],
            ],
        ]);

        $this->add([
            'name'       => 'country',
            'required'   => true,
            'filters'    => $trimStrip,
            'validators' => [
                ['name' => NotEmpty::class],
                [
                    'name'    => InArray::class,
                    'options' => [
                        'haystack' => ['BG'],
                    ],
                ],
            ],
        ]);

        $this->add([
            'name'       => 'zip',
            'required'   => true,
            'filters'    => $trimStrip,
            'validators' => [
                ['name' => NotEmpty::class],
                // Keep it simple: allow alnum + dash + space, 3..12
                ['name' => Regex::class, 'options' => ['pattern' => '/^[0-9A-Za-z -]{3,12}$/']],
            ],
        ]);

        $this->add([
            'name'              => 'sameAddress',
            'required'          => false,
            'allow_empty'       => true,
            'continue_if_empty' => true,
            'filters'           => [
                [
                    'name'    => CallbackFilter::class,
                    'options' => [
                        'callback' => static fn ($value): string => empty($value)
                            || $value === '0'
                            || $value === 0 ? '0' : '1',
                    ],
                ],
            ],
            'validators'        => [
                ['name' => InArray::class, 'options' => ['haystack' => ['0', '1']]],
            ],
        ]);

        $this->add([
            'name'              => 'saveInfo',
            'required'          => false,
            'allow_empty'       => true,
            'continue_if_empty' => true,
            'filters'           => [
                [
                    'name'    => CallbackFilter::class,
                    'options' => [
                        'callback' => static fn ($value): string => empty($value)
                            || $value === '0'
                            || $value === 0 ? '0' : '1',
                    ],
                ],
            ],
            'validators'        => [
                ['name' => InArray::class, 'options' => ['haystack' => ['0', '1']]],
            ],
        ]);

        $this->add([
            'name'       => 'paymentMethod',
            'required'   => true,
            'filters'    => $trimStrip,
            'validators' => [
                ['name' => NotEmpty::class],
                ['name' => InArray::class, 'options' => ['haystack' => ['credit', 'debit', 'paypal']]],
            ],
        ]);

        // Card fields: conditionally required only when payment_method != paypal
        $requiredIfCard = new Callback([
            'callback' => static function (mixed $value, array $context): bool {
                $pm = (string) ($context['paymentMethod'] ?? '');
                if ($pm === 'paypal') {
                    return true; // ignore card fields
                }
                return is_string($value) && trim($value) !== '';
            },
            'messages' => [
                Callback::INVALID_VALUE => 'This field is required for card payments.',
            ],
        ]);

        $this->add([
            'name'       => 'cardNumber',
            'required'   => false,
            'filters'    => $trimStrip,
            'validators' => [
                $requiredIfCard,
                // accept digits + spaces, 12..19 digits
                ['name' => Regex::class, 'options' => ['pattern' => '/^(?:\d[ -]*?){12,19}$/']],
            ],
        ]);

        $this->add([
            'name'       => 'cardName',
            'required'   => false,
            'filters'    => $trimStrip,
            'validators' => [
                $requiredIfCard,
                ['name' => StringLength::class, 'options' => ['min' => 2, 'max' => 80]],
            ],
        ]);

        $this->add([
            'name'       => 'expiryDate',
            'required'   => false,
            'filters'    => $trimStrip,
            'validators' => [
                $requiredIfCard,
                // MM/YY with basic month check
                ['name' => Regex::class, 'options' => ['pattern' => '/^(0[1-9]|1[0-2])\/\d{2}$/']],
            ],
        ]);

        $this->add([
            'name'       => 'cvv',
            'required'   => false,
            'filters'    => $trimStrip,
            'validators' => [
                $requiredIfCard,
                ['name' => Digits::class],
                ['name' => StringLength::class, 'options' => ['min' => 3, 'max' => 4]],
            ],
        ]);

        $this->add([
            'name'       => 'terms',
            'required'   => true,
            'filters'    => [
                [
                    'name'    => CallbackFilter::class,
                    'options' => [
                        'callback' => static fn ($value): string => empty($value)
                            || $value === '0'
                            || $value === 0
                            || $value === false ? '0' : '1',
                    ],
                ],
            ],
            'validators' => [
                ['name' => InArray::class, 'options' => ['haystack' => ['1']]],
            ],
        ]);
    }
}
