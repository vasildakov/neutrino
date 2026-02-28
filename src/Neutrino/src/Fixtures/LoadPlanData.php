<?php

declare(strict_types=1);

namespace Neutrino\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Neutrino\Domain\Billing\Plan;

class LoadPlanData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $repo = $manager->getRepository(Plan::class);

        foreach ($this->getData() as $planData) {
            // Check if plan already exists
            $existingPlan = $repo->findOneBy(['key' => $planData['key']]);

            if (! $existingPlan) {
                $plan = new Plan(
                    $planData['key'],
                    $planData['name'],
                    $planData['priceAmount'],
                    $planData['maxStores'],
                    $planData['maxUsers'],
                    $planData['description'],
                    $planData['icon'],
                    $planData['features']
                );
                $manager->persist($plan);
                $this->setReference('plan-' . $planData['key'], $plan);
            } else {
                $this->setReference('plan-' . $planData['key'], $existingPlan);
            }
        }

        $manager->flush();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getData(): array
    {
        return [
            [
                'key'         => 'starter',
                'name'        => 'Starter',
                'description' => 'For freelancers and solo entrepreneurs.',
                'priceAmount' => 1900, // €19.00 in cents
                'maxStores'   => 1,
                'maxUsers'    => 1,
                'icon'        => 'uil-shopping-bag',
                'features'    => [
                    ['available' => true,  'text' => '<strong>1</strong> Store'],
                    ['available' => true,  'text' => 'Up to <strong>500</strong> products'],
                    ['available' => true,  'text' => '<strong>1</strong> user'],
                    ['available' => true,  'text' => '<strong>Orders</strong>'],
                    ['available' => true,  'text' => '<strong>Invoices</strong>'],
                    ['available' => false, 'text' => '<strong>ACL</strong> (roles &amp; permissions)'],
                    ['available' => true,  'text' => '<strong>Backup</strong>'],

                    // Payments
                    ['available' => true,  'text' => '<strong>PayPal</strong>'],
                    ['available' => false, 'text' => '<strong>Stripe</strong>'],
                    ['available' => false, 'text' => '<strong>Revolut</strong>'],
                    ['available' => false, 'text' => '<strong>Fibank</strong>'],

                    // Shipping
                    ['available' => true,  'text' => '<strong>Econt</strong>'],
                    ['available' => false, 'text' => '<strong>Speedy</strong>'],

                    // Support (unknown in your table)
                    ['available' => true,  'text' => '<strong>Support</strong>'],
                ],
            ],
            [
                'key'         => 'growth',
                'name'        => 'Growth',
                'description' => 'For small growing businesses and teams.',
                'priceAmount' => 4900, // €49.00 in cents
                'maxStores'   => 3,
                'maxUsers'    => 5,
                'icon'        => 'uil-shopping-cart-alt',
                'features'    => [
                    ['available' => true,  'text' => '<strong>3</strong> Stores'],
                    ['available' => true,  'text' => 'Up to <strong>5,000</strong> products'],
                    ['available' => true,  'text' => 'Up to <strong>5</strong> users'],
                    ['available' => true,  'text' => '<strong>Orders</strong>'],
                    ['available' => true,  'text' => '<strong>Invoices</strong>'],
                    ['available' => true,  'text' => '<strong>ACL</strong> (roles &amp; permissions)'],
                    ['available' => true,  'text' => '<strong>Backup</strong>'],

                    // Payments
                    ['available' => true,  'text' => '<strong>PayPal</strong>'],
                    ['available' => true,  'text' => '<strong>Stripe</strong>'],
                    ['available' => false, 'text' => '<strong>Revolut</strong>'],
                    ['available' => false, 'text' => '<strong>Fibank</strong>'],

                    // Shipping
                    ['available' => true,  'text' => '<strong>Econt</strong>'],
                    ['available' => true,  'text' => '<strong>Speedy</strong>'],

                    // Support (unknown in your table)
                    ['available' => true,  'text' => '<strong>Support</strong>'],
                ],
            ],
            [
                'key'         => 'pro',
                'name'        => 'Pro',
                'description' => 'For serious operations and established SMBs.',
                'priceAmount' => 9900, // €99.00 in cents
                'maxStores'   => 15,
                'maxUsers'    => 15,
                'icon'        => 'uil-store',
                'features'    => [
                    ['available' => true,  'text' => '<strong>15</strong> Stores'],
                    ['available' => true,  'text' => 'Up to <strong>15,000</strong> products'],
                    ['available' => true,  'text' => 'Up to <strong>15</strong> users'],
                    ['available' => true,  'text' => '<strong>Orders</strong>'],
                    ['available' => true,  'text' => '<strong>Invoices</strong>'],
                    ['available' => true,  'text' => '<strong>ACL</strong> (roles &amp; permissions)'],
                    ['available' => true,  'text' => '<strong>Backup</strong>'],

                    // Payments
                    ['available' => true,  'text' => '<strong>PayPal</strong>'],
                    ['available' => true,  'text' => '<strong>Stripe</strong>'],
                    ['available' => true,  'text' => '<strong>Revolut</strong>'],
                    ['available' => true,  'text' => '<strong>Fibank</strong>'],

                    // Shipping
                    ['available' => true,  'text' => '<strong>Econt</strong>'],
                    ['available' => true,  'text' => '<strong>Speedy</strong>'],

                    // Support (unknown in your table)
                    ['available' => true,  'text' => '<strong>Support</strong>'],
                ],
            ],
            [
                'key'         => 'enterprise',
                'name'        => 'Enterprise',
                'description' => 'For larger operations. Dedicated infrastructure and custom integrations.',
                'priceAmount' => 24900, // ~€249.00 in cents (starting)
                'maxStores'   => 999,  // Unlimited
                'maxUsers'    => 9999, // Unlimited
                'icon'        => 'uil-store-alt',
                'features'    => [
                    ['available' => true,  'text' => '<strong>Unlimited</strong> Stores'],
                    ['available' => true,  'text' => '<strong>Unlimited</strong> products'],
                    ['available' => true,  'text' => '<strong>Unlimited</strong> users'],
                    ['available' => true,  'text' => '<strong>Orders</strong>'],
                    ['available' => true,  'text' => '<strong>Invoices</strong>'],
                    ['available' => true,  'text' => '<strong>ACL</strong> (roles &amp; permissions)'],
                    ['available' => true,  'text' => '<strong>Backup</strong>'],

                    // Payments
                    ['available' => true,  'text' => '<strong>PayPal</strong>'],
                    ['available' => true,  'text' => '<strong>Stripe</strong>'],
                    ['available' => true,  'text' => '<strong>Revolut</strong>'],
                    ['available' => true,  'text' => '<strong>Fibank</strong>'],

                    // Shipping
                    ['available' => true,  'text' => '<strong>Econt</strong>'],
                    ['available' => true,  'text' => '<strong>Speedy</strong>'],

                    // Support (unknown in your table)
                    ['available' => true,  'text' => '<strong>Support</strong>'],
                ],
            ],
        ];
    }

    public function getOrder(): int
    {
        return 1;
    }
}
