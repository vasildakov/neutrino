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

use Laminas\Form\Element;
use Laminas\Form\Exception\ExceptionInterface;
use Laminas\Form\Form;

/**
 * @phpstan-import-type CheckoutData from CheckoutInputFilter
 * @extends Form<CheckoutData>
 */
final class CheckoutForm extends Form
{
    public const NAME = 'checkout-form';

    /**
     * @throws ExceptionInterface
     */
    public function __construct()
    {
        parent::__construct(self::NAME);

        $this->setAttribute('method', 'POST');
        $this->setAttribute('id', 'checkout-form');
        $this->setAttribute('action', '/checkout/process');
        $this->setAttribute('novalidate', true); // keep browser from fighting server-side validation

        // Hidden (from cart/plan)
        $this->add([
            'name' => 'planId',
            'type' => Element\Hidden::class,
        ]);

        $this->add([
            'name' => 'billingPeriod',
            'type' => Element\Hidden::class,
        ]);

        // Billing
        $this->add([
            'name'       => 'firstName',
            'type'       => Element\Text::class,
            'options'    => [
                'label' => 'First name',
            ],
            'attributes' => [
                'id'          => 'firstName',
                'class'       => 'form-control',
                'placeholder' => 'First name',
                'value'       => 'Vasil',
            ],
        ]);

        $this->add([
            'name'       => 'lastName',
            'type'       => Element\Text::class,
            'options'    => ['label' => 'Last name'],
            'attributes' => [
                'id'          => 'lastName',
                'class'       => 'form-control',
                'placeholder' => 'Last name',
                'value'       => 'Dakov',
            ],
        ]);

        $this->add([
            'name'       => 'email',
            'type'       => Element\Email::class,
            'options'    => ['label' => 'Email'],
            'attributes' => [
                'id'          => 'email',
                'class'       => 'form-control',
                'placeholder' => 'Email',
                'value'       => 'vasildakov@gmail.com',
            ],
        ]);

        $this->add([
            'name'    => 'address',
            'type'    => Element\Text::class,
            'options' => ['label' => 'Address'],
        ]);

        $this->add([
            'name'    => 'address2',
            'type'    => Element\Text::class,
            'options' => ['label' => 'Address 2'],
        ]);

        $this->add([
            'name'    => 'city',
            'type'    => Element\Text::class,
            'options' => ['label' => 'City'],
        ]);

        $this->add([
            'name'    => 'country',
            'type'    => Element\Select::class,
            'options' => [
                'label'         => 'Country',
                'value_options' => [
                    'BG' => 'Bulgaria',
                ],
                'empty_option'  => 'Country',
            ],
        ]);

        $this->add([
            'name'    => 'zip',
            'type'    => Element\Text::class,
            'options' => ['label' => 'Zip Code'],
        ]);

        // Checkboxes
        $this->add([
            'name'    => 'sameAddress',
            'type'    => Element\Checkbox::class,
            'options' => [
                'label'              => 'Shipping address is the same as my billing address',
                'use_hidden_element' => true,
                'checked_value'      => '1',
                'unchecked_value'    => '0',
            ],
        ]);

        $this->add([
            'name'    => 'saveInfo',
            'type'    => Element\Checkbox::class,
            'options' => [
                'label'              => 'Save this information for next time',
                'use_hidden_element' => true,
                'checked_value'      => '1',
                'unchecked_value'    => '0',
            ],
        ]);

        // Payment method
        $this->add([
            'name'    => 'paymentMethod',
            'type'    => Element\Radio::class,
            'options' => [
                'label'         => 'Payment',
                'value_options' => [
                    'credit' => 'Credit card',
                    'debit'  => 'Debit card',
                    'paypal' => 'PayPal',
                ],
            ],
        ]);

        // Card fields (validated conditionally)
        $this->add([
            'name'    => 'cardNumber',
            'type'    => Element\Text::class,
            'options' => ['label' => 'Credit card number'],
        ]);

        $this->add([
            'name'    => 'cardName',
            'type'    => Element\Text::class,
            'options' => ['label' => 'Name on card'],
        ]);

        $this->add([
            'name'    => 'expiryDate',
            'type'    => Element\Text::class,
            'options' => ['label' => 'Expiration (MM/YY)'],
        ]);

        $this->add([
            'name'    => 'cvv',
            'type'    => Element\Text::class,
            'options' => ['label' => 'CVV'],
        ]);

        // Terms
        $this->add([
            'name'    => 'terms',
            'type'    => Element\Checkbox::class,
            'options' => [
                'label'              => 'I agree to the Terms and Conditions',
                'use_hidden_element' => true,
                'checked_value'      => '1',
                'unchecked_value'    => '0',
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => Element\Submit::class,
            'attributes' => [
                'value' => 'Place Order',
                'class' => 'btn btn-primary rounded w-100 mt-4',
            ],
        ]);
    }
}
