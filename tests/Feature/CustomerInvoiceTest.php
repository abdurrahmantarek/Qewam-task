<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\InvoiceDetail;
use App\Models\InvoiceSummary;
use App\Models\Session;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerInvoiceTest extends TestCase
{
    use RefreshDatabase;


    public function test_invoice_with_activation_in_period()
    {
        // Create customer
        $customer = Customer::factory()->create([
            'email' => 'customerA@customer.com',
        ]);

        // Create user
        $user = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        // Create sessions
        Session::factory(1)->create([
            'user_id' => $user->id,
            'event_type' => 'registration',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2020-12-01'),
        ]);

        Session::factory(3)->create([
            'user_id' => $user->id,
            'event_type' => 'activation',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2021-01-15'),
        ]);

        $response = $this->json('POST', '/api/customers/invoices', [
            'customer_id' => $customer->id,
            'start_date' => '2021-01-01',
            'end_date' => '2021-02-01',
        ]);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'total_cost' => 50
        ]);
    }

    public function test_invoice_with_appointment_in_period()
    {
        // Create customer
        $customer = Customer::factory()->create([
            'email' => 'customerB@customer.com',
        ]);

        // Create user
        $user = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        // Create sessions
        Session::factory(1)->create([
            'user_id' => $user->id,
            'event_type' => 'registration',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2020-12-15'),
        ]);
        Session::factory(1)->create([
            'user_id' => $user->id,
            'event_type' => 'appointment',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2021-01-15'),
        ]);

        $response = $this->json('POST', '/api/customers/invoices', [
            'customer_id' => $customer->id,
            'start_date' => '2021-01-01',
            'end_date' => '2021-02-01',
        ]);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'total_cost' => 200
        ]);
    }

    public function test_invoice_with_multiple_events_in_period()
    {
        // Create customer
        $customer = Customer::factory()->create([
            'email' => 'customerC@customer.com',
        ]);

        // Create user
        $user = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        // Create sessions
        Session::factory(1)->create([
            'user_id' => $user->id,
            'event_type' => 'registration',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2021-01-01'),
        ]);
        Session::factory(3)->create([
            'user_id' => $user->id,
            'event_type' => 'activation',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2021-01-10'),
        ]);

        $response = $this->json('POST', '/api/customers/invoices', [
            'customer_id' => $customer->id,
            'start_date' => '2021-01-01',
            'end_date' => '2021-02-01',
        ]);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'total_cost' => 100
        ]);
    }

    public function test_no_invoice_for_events_before_period()
    {
        // Create customer
        $customer = Customer::factory()->create([
            'email' => 'customerD@customer.com',
        ]);

        // Create user
        $user = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        // Create sessions
        Session::factory(1)->create([
            'user_id' => $user->id,
            'event_type' => 'registration',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2020-09-01'),
        ]);
        Session::factory(3)->create([
            'user_id' => $user->id,
            'event_type' => 'activation',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2020-10-11'),
        ]);
        Session::factory(1)->create([
            'user_id' => $user->id,
            'event_type' => 'appointment',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2020-12-27'),
        ]);

        $response = $this->json('POST', '/api/customers/invoices', [
            'customer_id' => $customer->id,
            'start_date' => '2021-01-01',
            'end_date' => '2021-02-01',
        ]);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'total_cost' => 0
        ]);
    }

    public function test_invoice_with_overlapping_events()
    {
        // Create customer
        $customer = Customer::factory()->create([
            'email' => 'customerE@customer.com',
        ]);

        // Create user
        $user = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        // Create sessions with overlapping dates
        Session::factory(3)->create([
            'user_id' => $user->id,
            'event_type' => 'activation',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2021-01-15'),
        ]);
        Session::factory(1)->create([
            'user_id' => $user->id,
            'event_type' => 'appointment',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2021-01-15'),
        ]);

        $response = $this->json('POST', '/api/customers/invoices', [
            'customer_id' => $customer->id,
            'start_date' => '2021-01-01',
            'end_date' => '2021-02-01',
        ]);

        $response->assertStatus(201);

        $response->assertJsonFragment([
            'total_cost' => 200
        ]);
    }

    public function test_invoice_with_invalid_customer_id()
    {
        $response = $this->json('POST', '/api/customers/invoices', [
            'customer_id' => 'invalid_id',
            'start_date' => '2021-01-01',
            'end_date' => '2021-02-01',
        ]);

        $response->assertStatus(422);
    }

    public function test_invoice_generation_already_exists()
    {
        // Create customer
        $customer = Customer::factory()->create([
            'email' => 'customerB@customer.com',
        ]);

        // Create user
        $user = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        // Create sessions
        Session::factory(1)->create([
            'user_id' => $user->id,
            'event_type' => 'registration',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2020-12-15'),
        ]);
        Session::factory(1)->create([
            'user_id' => $user->id,
            'event_type' => 'appointment',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2021-01-15'),
        ]);

        // Create an existing invoice for the customer using POST request
        $response = $this->post('/api/customers/invoices', [
            'customer_id' => $customer->id,
            'start_date' => '2021-01-01',
            'end_date' => '2021-02-01',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'total_cost' => 200
        ]);

        // Make another request to create the same invoice again
        $response = $this->post('/api/customers/invoices', [
            'customer_id' => $customer->id,
            'start_date' => '2021-01-01',
            'end_date' => '2021-02-01',
        ]);

        $response->assertStatus(422);
        $response->assertSee( 'date range conflicts with an existing invoice');
    }


    public function test_invoice_generation_with_frequent_sessions()
    {
        // Create customer
        $customer = Customer::factory()->create([
            'email' => 'frequent@customer.com',
        ]);

        // Create user
        $user = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        for ($i = 0; $i < 100; $i++) {
            Session::factory()->create([
                'user_id' => $user->id,
                'event_type' => $this->getRandomEventType(),
                'event_date' => Carbon::now()->subDays(rand(1, 365)),
            ]);
        }

        $response = $this->post('/api/customers/invoices', [
            'customer_id' => $customer->id,
            'start_date' => Carbon::now()->subMonth(1)->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
        ]);

        $response->assertStatus(201);

        // Get the created invoice
        $invoiceId = json_decode($response->content(), true)['data']['id'];

        $getInvoiceResponse = $this->get("/api/customers/invoices/$invoiceId");

        $getInvoiceResponse->assertStatus(200);

    }

    public function test_invoice_generation_with_multiple_users_and_overlapping_sessions()
    {
        // Create customer
        $customer = Customer::factory()->create([
            'email' => 'customerE@customer.com',
        ]);

        $userOne = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        $userTwo = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        $userThree = User::factory()->create([
            'customer_id' => $customer->id
        ]);

        //200
        Session::factory(4)->create([
            'user_id' => $userOne->id,
            'event_type' => 'appointment',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2021-01-15'),
        ]);

        //100
        Session::factory(2)->create([
            'user_id' => $userTwo->id,
            'event_type' => 'activation',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2021-01-15'),
        ]);

        Session::factory(1)->create([
            'user_id' => $userTwo->id,
            'event_type' => 'registration',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2021-01-15'),
        ]);

        //50
        Session::factory(1)->create([
            'user_id' => $userThree->id,
            'event_type' => 'registration',
            'event_date' => Carbon::createFromFormat('Y-m-d', '2021-01-15'),
        ]);

        $response = $this->post('/api/customers/invoices', [
            'customer_id' => $customer->id,
            'start_date' => '2021-01-01',
            'end_date' => '2021-02-01',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['total_cost' => 350]);

        $invoiceId = json_decode($response->content(), true)['data']['id'];

        $getInvoiceResponse = $this->get("/api/customers/invoices/$invoiceId");

        $getInvoiceResponse->assertStatus(200);

        $getInvoiceResponse->assertJsonFragment(
            [
                'registration' => 2,
                'activation' => 2,
                'appointment' => 4
            ]
        );

        $getInvoiceResponse->assertJsonFragment(
            [
                'invoiced_events' => [
                    'registration',
                    'activation',
                    'appointment'
                ]
            ]
        );

        $getInvoiceResponse->assertSee(50);
        $getInvoiceResponse->assertSee(100);
        $getInvoiceResponse->assertSee(200);

        $getInvoiceResponse->assertJsonCount(3, 'data.users');
    }


    private function getRandomEventType()
    {
        $eventTypes = ['registration', 'activation', 'appointment', 'support'];
        return $eventTypes[array_rand($eventTypes)];
    }
}
