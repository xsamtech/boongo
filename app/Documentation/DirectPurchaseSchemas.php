<?php

namespace App\Documentation;

final class DirectPurchaseSchemas
{
    public const SUCCESS = 'array{success: true, message: string, ad: null, data: array{'
        .'payment_id: int, cart_id: int, entity: string, entity_id: int, amount: string, currency: "USD", reference: string,'
        .'result_response: array{message: string, order_number: string, url: ?string}}}';

    public const ERROR = 'array{success: false, message: string}';

    public const INVALID = self::ERROR.'|array{message: string, errors: array<string, string[]>}';
}
