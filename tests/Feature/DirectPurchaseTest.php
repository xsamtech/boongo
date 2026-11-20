<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DirectPurchaseTest extends TestCase
{
    use \Tests\Support\BootsWithoutMysql;

    private User $payer;

    protected function setUp(): void
    {
        parent::setUp();

        // Never migrate or write the application's configured database.
        config(['database.connections.direct_purchase_tests' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::setDefaultConnection('direct_purchase_tests');
        $schema = Schema::connection('direct_purchase_tests');
        foreach (['currencies', 'groups', 'types', 'statuses', 'users', 'works', 'subscriptions', 'payments', 'carts', 'currencies_rates', 'cart_work', 'cart_subscription'] as $name) {
            $schema->create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $columns = match ($name) {
                    'currencies' => ['currency_acronym'],
                    'groups' => ['group_name'],
                    'types' => ['type_name', 'group_id'],
                    'statuses' => ['status_name', 'group_id'],
                    'users' => ['promo_code', 'is_promoted', 'currency_id'],
                    'works' => ['consultation_price', 'currency_id'],
                    'subscriptions' => ['price', 'currency_id'],
                    'payments' => ['reference', 'order_number', 'amount', 'currency', 'phone', 'channel', 'type_id', 'status_id', 'user_id'],
                    'carts' => ['payment_code', 'entity', 'status_id', 'payment_id', 'user_id'],
                    'currencies_rates' => ['rate', 'from_currency_id', 'to_currency_id'],
                    'cart_work' => ['cart_id', 'work_id', 'status_id'],
                    'cart_subscription' => ['cart_id', 'subscription_id', 'status_id'],
                };
                foreach ($columns as $column) {
                    if (str_ends_with($column, '_id')) {
                        $table->integer($column)->nullable();
                    } else {
                        $table->string($column)->nullable();
                    }
                }
                $table->timestamps();
            });
        }
        DB::table('currencies')->insert([['id' => 1, 'currency_acronym' => 'USD'], ['id' => 2, 'currency_acronym' => 'CDF']]);
        foreach ([1 => 'Type de paiement', 2 => 'Etat du paiement', 3 => 'Etat du panier', 4 => 'Etat de l\'abonnement'] as $id => $name) {
            DB::table('groups')->insert(['id' => $id, 'group_name' => $name]);
        }
        foreach ([11 => 'Mobile money', 12 => 'Carte bancaire', 13 => 'Autre'] as $id => $name) {
            DB::table('types')->insert(['id' => $id, 'group_id' => 1, 'type_name' => json_encode(['fr' => $name])]);
        }
        foreach ([21 => [2, 'En cours'], 22 => [3, 'Payé'], 23 => [4, 'En attente'], 24 => [2, 'Effectué']] as $id => [$group, $name]) {
            DB::table('statuses')->insert(['id' => $id, 'group_id' => $group, 'status_name' => json_encode(['fr' => $name])]);
        }
        $this->payer = User::create(['currency_id' => 2, 'promo_code' => 'PROMO', 'is_promoted' => 1]);
        config(['services.flexpay.gateway_mobile' => 'https://flexpay.test/payment', 'services.flexpay.api_token' => 'test-token', 'services.flexpay.merchant' => 'test-merchant']);
        Http::preventStrayRequests();
        Http::fake(['https://flexpay.test/*' => Http::response(['code' => '0', 'message' => 'Accepted', 'orderNumber' => 'ORDER-1', 'url' => 'https://flexpay.test/checkout'])]);
    }

    public static function entities(): array
    {
        return [['work'], ['subscription']];
    }

    private function item(string $entity, string $price = '8.75', ?int $currency = 1): Work|Subscription
    {
        return $entity === 'work'
            ? Work::create(['consultation_price' => $price, 'currency_id' => $currency])
            : Subscription::create(['price' => $price, 'currency_id' => $currency]);
    }

    private function url(string $entity, Work|Subscription $item): string
    {
        return '/api/'.$entity.'/purchase/'.$item->id;
    }

    private function mobile(): array
    {
        return ['transaction_type_id' => 11, 'other_phone' => '+243812345678', 'channel' => 'mobile'];
    }

    private function authenticate(): void
    {
        Sanctum::actingAs($this->payer);
    }

    private function rate(int $from, int $to, string $rate, string $date = '2026-01-01'): void
    {
        DB::table('currencies_rates')->insert(['from_currency_id' => $from, 'to_currency_id' => $to, 'rate' => $rate, 'created_at' => $date, 'updated_at' => $date]);
    }

    #[DataProvider('entities')]
    public function test_mobile_purchase_uses_item_usd_price_and_only_links_this_item(string $entity): void
    {
        $this->authenticate();
        $item = $this->item($entity);
        $this->item($entity, '100.00');
        $response = $this->postJson($this->url($entity, $item), $this->mobile() + ['amount' => 9999, 'currency' => 'CDF', 'user_id' => 999]);
        $response->assertOk()->assertJsonPath('data.amount', '8.75')->assertJsonPath('data.currency', 'USD')->assertJsonPath('data.entity_id', $item->id);
        Http::assertSent(fn (Request $request) => $request['amount'] === '8.75' && $request['currency'] === 'USD' && $request['type'] === '1' && $request['phone'] === '243812345678' && $request->hasHeader('Authorization', 'Bearer test-token') && str_ends_with($request['callback_url'], '/api/payment/store'));
        $payment = Payment::firstOrFail();
        $this->assertSame('8.75', $payment->amount);
        $this->assertSame('USD', $payment->currency);
        $this->assertSame(21, $payment->status_id);
        $this->assertSame($this->payer->id, $payment->user_id);
        $this->assertSame((string) $this->payer->id, explode('-', $payment->reference)[2]);
        $cart = Cart::firstOrFail();
        $relation = $entity === 'work' ? 'works' : 'subscriptions';
        $this->assertSame([$item->id], $cart->{$relation}->pluck('id')->all());
        $this->assertSame(23, $cart->{$relation}->first()->pivot->status_id);
        $this->assertSame($payment->id, $cart->payment_id);
        $this->assertSame('0', $this->payer->fresh()->is_promoted);
        Http::assertSentCount(1);
    }

    #[DataProvider('entities')]
    public function test_source_to_usd_uses_latest_rate_and_preserves_cents(string $entity): void
    {
        $this->authenticate();
        $this->rate(2, 1, '0.00030');
        $this->rate(2, 1, '0.00035', '2026-02-01');
        $this->rate(1, 2, '2500.00000', '2026-03-01'); // A direct rate takes precedence.
        $item = $this->item($entity, '25000.00', 2);
        $this->postJson($this->url($entity, $item), $this->mobile())->assertOk()->assertJsonPath('data.amount', '8.75');
        Http::assertSent(fn (Request $request) => $request['amount'] === '8.75' && $request['currency'] === 'USD');
    }

    #[DataProvider('entities')]
    public function test_inverse_rate_can_convert_source_price_to_usd(string $entity): void
    {
        $this->authenticate();
        $this->rate(1, 2, '2500.00000');
        $item = $this->item($entity, '1250.00', 2);
        $this->postJson($this->url($entity, $item), $this->mobile())->assertOk()->assertJsonPath('data.amount', '0.50');
        $this->assertSame('USD', Payment::firstOrFail()->currency);
    }

    #[DataProvider('entities')]
    public function test_card_purchase_uses_usd_for_flexpay_and_return_urls(string $entity): void
    {
        $this->authenticate();
        $item = $this->item($entity);
        $this->postJson($this->url($entity, $item), ['transaction_type_id' => 12, 'app_url' => 'https://app.test/'])
            ->assertOk()->assertJsonPath('data.result_response.url', 'https://flexpay.test/checkout');
        Http::assertSent(fn (Request $request) => $request['type'] === '2' && $request['amount'] === '8.75' && $request['currency'] === 'USD' && str_contains($request['approve_url'], '/8.75/USD/0/'.$this->payer->id) && ! str_contains($request['home_url'], 'api_token') && ! isset($request['phone']));
    }

    #[DataProvider('entities')]
    public function test_unauthenticated_purchase_is_blocked_without_calling_flexpay(string $entity): void
    {
        $this->postJson($this->url($entity, $this->item($entity)), $this->mobile())->assertUnauthorized();
        Http::assertNothingSent();
    }

    #[DataProvider('entities')]
    public function test_missing_rate_or_currency_never_calls_flexpay(string $entity): void
    {
        $this->authenticate();
        $item = $this->item($entity, '100.00', 2);
        $this->postJson($this->url($entity, $item), $this->mobile())->assertNotFound();
        $item->update(['currency_id' => null]);
        $this->postJson($this->url($entity, $item), $this->mobile())->assertNotFound();
        Http::assertNothingSent();
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, Cart::count());
    }

    #[DataProvider('entities')]
    public function test_invalid_price_rate_or_rounded_zero_never_calls_flexpay(string $entity): void
    {
        $this->authenticate();
        $item = $this->item($entity, '0.00');
        $this->postJson($this->url($entity, $item), $this->mobile())->assertUnprocessable();
        $item->update([$entity === 'work' ? 'consultation_price' : 'price' => '0.01', 'currency_id' => 2]);
        $this->rate(2, 1, '0.00001');
        $this->postJson($this->url($entity, $item), $this->mobile())->assertUnprocessable();
        DB::table('currencies_rates')->update(['rate' => 0]);
        $this->postJson($this->url($entity, $item), $this->mobile())->assertUnprocessable();
        Http::assertNothingSent();
    }

    #[DataProvider('entities')]
    public function test_invalid_payment_inputs_never_call_flexpay(string $entity): void
    {
        $this->authenticate();
        $item = $this->item($entity);
        foreach ([['transaction_type_id' => 13], ['transaction_type_id' => 11], ['transaction_type_id' => 12], ['transaction_type_id' => 11, 'other_phone' => 'invalid']] as $payload) {
            $this->postJson($this->url($entity, $item), $payload)->assertUnprocessable();
        }
        Http::assertNothingSent();
    }

    #[DataProvider('entities')]
    public function test_provider_failures_do_not_create_payments_or_carts(string $entity): void
    {
        $this->authenticate();
        $item = $this->item($entity);
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::preventStrayRequests();
        Http::fakeSequence()->push(['code' => '1'], 200)->push(['code' => '0'], 200)->push('not json', 503);
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->postJson($this->url($entity, $item), $this->mobile())->assertStatus(502);
        }
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, Cart::count());
        $this->assertSame('1', $this->payer->fresh()->is_promoted);
    }

    public function test_half_cent_conversion_rounds_up_without_integer_dollar_rounding(): void
    {
        $this->authenticate();
        $this->rate(2, 1, '1.50000');
        $item = $this->item('subscription', '0.01', 2);
        $this->postJson($this->url('subscription', $item), $this->mobile())->assertOk()->assertJsonPath('data.amount', '0.02');
    }

    public function test_early_callback_status_is_preserved(): void
    {
        $this->authenticate();
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::preventStrayRequests();
        Http::fake(function (Request $request) {
            Payment::create(['order_number' => 'EARLY', 'reference' => $request['reference'], 'amount' => $request['amount'], 'currency' => 'USD', 'user_id' => $this->payer->id, 'status_id' => 24]);

            return Http::response(['code' => 0, 'orderNumber' => 'EARLY']);
        });
        $this->postJson($this->url('work', $this->item('work')), $this->mobile())->assertOk();
        $this->assertSame(24, Payment::firstOrFail()->status_id);
        $this->assertSame(1, Payment::count());
    }

    #[DataProvider('entities')]
    public function test_missing_item_and_configuration_never_call_flexpay(string $entity): void
    {
        $this->authenticate();
        $this->postJson('/api/'.$entity.'/purchase/9999', $this->mobile())->assertNotFound();
        $item = $this->item($entity);
        config(['services.flexpay.api_token' => null]);
        $this->postJson($this->url($entity, $item), $this->mobile())->assertStatus(503);
        Http::assertNothingSent();
    }

    #[DataProvider('entities')]
    public function test_connection_failure_is_reported_without_creating_local_payment(string $entity): void
    {
        $this->authenticate();
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::preventStrayRequests();
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('Simulated timeout'));
        $this->postJson($this->url($entity, $this->item($entity)), $this->mobile())->assertStatus(502);
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, Cart::count());
    }

    public function test_conversion_capacity_is_exact_and_excess_is_rejected(): void
    {
        $this->authenticate();
        $converter = app(\App\Services\UsdPriceConverter::class);
        $this->assertSame('9999999.99', $converter->convert('9999999.99', Currency::findOrFail(1)));
        $this->rate(2, 1, '9999.99999');
        $item = $this->item('subscription', '9999999.99', 2);
        $this->postJson($this->url('subscription', $item), $this->mobile())->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_another_payments_order_number_cannot_be_reused(): void
    {
        $this->authenticate();
        Payment::create(['order_number' => 'ORDER-1', 'reference' => 'REF-other-999', 'user_id' => 999, 'status_id' => 24]);
        $this->postJson($this->url('work', $this->item('work')), $this->mobile())->assertStatus(502);
        $this->assertSame(0, Cart::count());
        $this->assertSame(24, Payment::firstOrFail()->status_id);
    }
}
