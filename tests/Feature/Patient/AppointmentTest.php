<?php

namespace Tests\Feature\Patient;

use App\Enums\AppointmentStatusEnum;
use App\Events\Appointment\PaymentConfirmedEvent;
use App\Listeners\Appointment\ConfirmAppointmentBookingListener;
use App\Listeners\Appointment\CreateAppointmentTransactionListener;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DoctorSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            DoctorSeeder::class,
        ]);
    }

    public function user(array $attributes = [])
    {
        return User::factory()->create($attributes);
    }

    public function doctor(array $attributes = [])
    {
        return Doctor::factory()->create($attributes);
    }

    public function test_appointment_booking_fields_validated_properly()
    {
        $user = $this->user([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->postJson(route('patients.appointments.store', ['patient' => $user]), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'doctor_id', 'datetime', 'reason', 'symptoms', 'address', 'payment_gateway',
            ])
            ->assertJsonStructure([
                'message', 'errors' => [
                    'doctor_id', 'datetime', 'reason', 'symptoms', 'address', 'payment_gateway',
                ],
            ]);
    }

    public function test_patient_can_book_appointment_with_paystack()
    {
        $user = $this->user([
            'email_verified_at' => now(),
        ]);

        $doctor = $this->doctor();

        $appointmentCount = Appointment::count();

        $data = [
            'doctor_id' => $doctor->id,
            'datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'reason' => $this->faker->sentence,
            'symptoms' => $this->faker->sentence,
            'address' => $this->faker->address,
            'payment_gateway' => 'paystack',
        ];

        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'message' => 'Authorization URL created',
                'data' => [
                    'authorization_url' => 'https://paystack.com/redirect',
                    'access_code' => 'access_code',
                    'reference' => 'reference',
                ],
            ], 200),
        ]);

        $this->actingAs($user)->postJson(route('patients.appointments.store', ['patient' => $user]), $data)
            ->assertCreated()
            ->assertJsonStructure([
                'status', 'message', 'data' => [
                    'payment' => [
                        'payment_url', 'reference', 'access_code',
                    ],
                    'appointment' => [
                        'id', 'reference', 'user', 'datetime', 'reason', 'symptoms', 'diagnosis', 'prescription', 'comment', 'address', 'price',
                        'status', 'created_at', 'updated_at',
                    ],
                ],
            ]);

        $this->assertEquals($appointmentCount + 1, Appointment::count());

        $this->assertDatabaseHas('appointments', [
            ['reference', '!=', null],
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
            'datetime' => $data['datetime'],
            'reason' => $data['reason'],
            'symptoms' => $data['symptoms'],
            'address' => $data['address'],
            'status' => AppointmentStatusEnum::PENDING,
        ]);
    }

    public function test_patient_can_book_appointment_with_flutterwave()
    {
        $user = $this->user([
            'email_verified_at' => now(),
        ]);

        $doctor = $this->doctor();

        $appointmentCount = Appointment::count();

        $data = [
            'doctor_id' => $doctor->id,
            'datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'reason' => $this->faker->sentence,
            'symptoms' => $this->faker->sentence,
            'address' => $this->faker->address,
            'payment_gateway' => 'flutterwave',
        ];

        Http::fake([
            'https://api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'message' => 'Authorization URL created',
                'data' => [
                    'link' => 'https://flutterwave.com/redirect',
                    'tx_ref' => 'tx_ref',
                    'flw_ref' => 'flw_ref',
                ],
            ], 200),
        ]);

        $this->actingAs($user)->postJson(route('patients.appointments.store', ['patient' => $user]), $data)
            ->assertCreated()
            ->assertJsonStructure([
                'status', 'message', 'data' => [
                    'payment' => [
                        'payment_url', 'reference', 'access_code',
                    ],
                    'appointment' => [
                        'id', 'reference', 'user', 'datetime', 'reason', 'symptoms', 'diagnosis', 'prescription', 'comment', 'address', 'price',
                        'status', 'created_at', 'updated_at',
                    ],
                ],
            ]);

        $this->assertEquals($appointmentCount + 1, Appointment::count());

        $this->assertDatabaseHas('appointments', [
            ['reference', '!=', null],
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
            'datetime' => $data['datetime'],
            'reason' => $data['reason'],
            'symptoms' => $data['symptoms'],
            'address' => $data['address'],
            'status' => AppointmentStatusEnum::PENDING,
        ]);
    }

    public function test_booked_order_with_verified_paystack_payment_can_be_confirmed_successfully()
    {
        $user = $this->user([
            'email_verified_at' => now(),
        ]);

        $doctor = $this->doctor();

        $transactionCount = Transaction::count();

        $data = [
            'doctor_id' => $doctor->id,
            'datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'reason' => $this->faker->sentence,
            'symptoms' => $this->faker->sentence,
            'address' => $this->faker->address,
            'payment_gateway' => 'paystack',
        ];

        $reference = Str::random(16);
        $accessCode = Str::random(16);

        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'message' => 'Authorization URL created',
                'data' => [
                    'authorization_url' => 'https://paystack.com/',
                    'access_code' => $accessCode,
                    'reference' => $reference,
                ],
            ], 200),
        ]);

        $this->actingAs($user)->postJson(route('patients.appointments.store', ['patient' => $user]), $data)
            ->assertCreated()
            ->assertJsonStructure([
                'status', 'message', 'data' => [
                    'payment' => [
                        'payment_url', 'reference', 'access_code',
                    ],
                    'appointment' => [
                        'id', 'reference', 'user', 'datetime', 'reason', 'symptoms', 'diagnosis', 'prescription', 'comment', 'address', 'price',
                        'status', 'created_at', 'updated_at',
                    ],
                ],
            ]);

        $appointment = Appointment::latest()->first();

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'message' => 'Verification successful',
                'data' => [
                    'status' => 'success',
                    'reference' => Str::random(16),
                    'amount' => ceil(config('settings.service-charge') + $doctor->consultation_fee) * 100,
                    'metadata' => [
                        'appointment_id' => $appointment->id,
                        'reference' => $appointment->reference,
                    ],
                ],
            ], 200),
        ]);

        $this->get(route('appointments.verify-payment', [
            'appointment_id' => $appointment,
            'gateway' => 'paystack',
            'status' => 'successful',
            'transaction_id' => mt_rand(100000, 999999),
            'reference' => $reference,
        ]))->assertOk()->assertViewIs('appointment.verify-payment')
            ->assertViewHas('status', 'success')
            ->assertViewHas('message', 'Your appointment booking has been successfully processed.')
            ->assertViewHas('redirect_url', route('home'));

        Event::fake();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => AppointmentStatusEnum::CONFIRMED,
        ]);

        Event::assertListening(PaymentConfirmedEvent::class, CreateAppointmentTransactionListener::class);
        Event::assertListening(PaymentConfirmedEvent::class, ConfirmAppointmentBookingListener::class);

        $this->assertEquals($transactionCount + 1, Transaction::count());

        $this->assertDatabaseHas('transactions', [
            'amount' => $appointment->price,
            'status' => 'success',
            'gateway' => 'paystack',
            'appointment_id' => $appointment->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_booked_order_with_verified_flutterwave_payment_can_be_confirmed_successfully()
    {
        $user = $this->user([
            'email_verified_at' => now(),
        ]);

        $doctor = $this->doctor();

        $transactionCount = Transaction::count();

        $data = [
            'doctor_id' => $doctor->id,
            'datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'reason' => $this->faker->sentence,
            'symptoms' => $this->faker->sentence,
            'address' => $this->faker->address,
            'payment_gateway' => 'flutterwave',
        ];

        $tx_ref = Str::random(16);

        Http::fake([
            'https://api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'message' => 'Authorization URL created',
                'data' => [
                    'link' => '"https://ravemodal-dev.herokuapp.com/v3/hosted/pay/'.Str::random(),
                    'tx_ref' => $tx_ref,
                    'flw_ref' => 'flw_ref',
                ],
            ], 200),
        ]);

        $this->actingAs($user)->postJson(route('patients.appointments.store', ['patient' => $user]), $data)
            ->assertCreated()
            ->assertJsonStructure([
                'status', 'message', 'data' => [
                    'payment' => [
                        'payment_url', 'reference', 'access_code',
                    ],
                    'appointment' => [
                        'id', 'reference', 'user', 'datetime', 'reason', 'symptoms', 'diagnosis', 'prescription', 'comment', 'address', 'price',
                        'status', 'created_at', 'updated_at',
                    ],
                ],
            ]);

        $appointment = Appointment::latest()->first();

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/*/verify' => Http::response([
                'status' => 'success',
                'message' => 'Transaction fetched successfully',
                'data' => [
                    'id' => mt_rand(100000, 999999),
                    'status' => 'successful',
                    'tx_ref' => $tx_ref,
                    'amount' => ceil(config('settings.service-charge') + $doctor->consultation_fee),
                    'meta' => [
                        'appointment_id' => $appointment->id,
                        'reference' => $appointment->reference,
                    ],
                ],
            ], 200),
        ]);

        $this->get(route('appointments.verify-payment', [
            'appointment_id' => $appointment,
            'gateway' => 'flutterwave',
            'status' => 'successful',
            'transaction_id' => mt_rand(100000, 999999),
            'reference' => $tx_ref,
        ]))->assertOk()->assertViewIs('appointment.verify-payment')
            ->assertViewHas('status', 'success')
            ->assertViewHas('message', 'Your appointment booking has been successfully processed.')
            ->assertViewHas('redirect_url', route('home'));

        Event::fake();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => AppointmentStatusEnum::CONFIRMED,
        ]);

        Event::assertListening(PaymentConfirmedEvent::class, CreateAppointmentTransactionListener::class);
        Event::assertListening(PaymentConfirmedEvent::class, ConfirmAppointmentBookingListener::class);

        $this->assertEquals($transactionCount + 1, Transaction::count());

        $this->assertDatabaseHas('transactions', [
            'amount' => $appointment->price,
            'status' => 'success',
            'gateway' => 'flutterwave',
            'appointment_id' => $appointment->id,
            'user_id' => $user->id,
        ]);
    }
}
