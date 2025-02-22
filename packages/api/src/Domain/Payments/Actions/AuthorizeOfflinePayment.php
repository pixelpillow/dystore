<?php

namespace Dystore\Api\Domain\Payments\Actions;

use Dystore\Api\Domain\Orders\Events\OrderPaymentSuccessful;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Facades\Payments;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Contracts\Order as OrderContract;

class AuthorizeOfflinePayment
{
    /**
     * @param  array<string,mixed>  $meta
     */
    public function __invoke(OrderContract $order, CartContract $cart, string $paymentType = 'offline', ?array $meta = null): void
    {
        /** @var PaymentAuthorize $payment */
        $payment = Payments::driver('offline')
            ->order($order)
            ->cart($cart)
            ->withData([
                'meta' => array_merge(
                    ['payment_type' => $paymentType],
                    $meta ?? [],
                ),
            ])
            ->authorize($paymentType);

        if (! $payment->success) {
            report("Payment failed for order: {$order->id} with reason: {$payment->message}");

            return;
        }

        OrderPaymentSuccessful::dispatch($order);
    }
}
