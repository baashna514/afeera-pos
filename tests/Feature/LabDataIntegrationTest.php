<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('authenticated user can view laboratory payments page with mocked API data', function () {
    $user = User::factory()->create();

    Http::fake([
        'http://127.0.0.1:8000/fetch-payments*' => Http::response([
            'status' => 'success',
            'message' => 'Payments and tests fetched successfully.',
            'summary' => [
                'total_records' => 1,
                'total_collected' => 1500.0,
                'total_invoiced' => 2000.0,
                'total_tests_conducted' => 2,
            ],
            'data' => [
                [
                    'id' => 1,
                    'invoice_number' => 'INV-TEST-001',
                    'booking_date' => '2026-09-18 12:00:00',
                    'company' => ['id' => 1, 'name' => 'Siyal Hospital', 'phone' => '123456'],
                    'patient' => ['id' => 1, 'name' => 'John Doe', 'phone' => '03001234567', 'age' => 30, 'gender' => 'male'],
                    'payment' => [
                        'total_amount' => 2000.0,
                        'discount' => 500.0,
                        'net_amount' => 1500.0,
                        'paid_amount' => 1500.0,
                        'due_amount' => 0.0,
                        'payment_status' => 'paid',
                        'booking_status' => 'completed',
                    ],
                    'total_tests_count' => 2,
                    'tests' => [
                        [
                            'item_id' => 1,
                            'test_id' => 10,
                            'test_name' => 'CBC (Complete Blood Count)',
                            'category' => 'Hematology',
                            'price' => 1000.0,
                            'barcode' => 'BAR-001',
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($user)->get(route('lab.payments'));

    $response->assertStatus(200);
    $response->assertSee('Laboratory Payments');
    $response->assertSee('INV-TEST-001');
    $response->assertSee('John Doe');
    $response->assertSee('CBC (Complete Blood Count)');
});
