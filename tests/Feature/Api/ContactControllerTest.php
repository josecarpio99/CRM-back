<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Customer;
use App\Enums\ContactRelationEnum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class ContactControllerTest extends FeatureTestCase
{
    /**
     * test_contact_can_be_created
     */
    public function test_contact_can_be_created(): void
    {
        $this->asSuperAdmin();

        $customer = Customer::factory()->create();

        $data = [
            'name' => 'John Doe',
            'phone' => '123456789',
            'email' => '8xh3E@example.com',
            'email2' => 'email2@example.com',
            'phone2' => '12345678922',
            'id' => $customer->id,
            'contact_type' => ContactRelationEnum::Customer->value
        ];

        $response = $this->postJson(route('contact.store'), $data);

        $response->assertCreated();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'phone'
            ]
        ]);

        $contact = $customer->contacts()->first();

        $this->assertEquals($data['name'], $contact->name);
        $this->assertEquals($data['email'], $contact->email);
        $this->assertEquals($data['phone'], $contact->phone);
        $this->assertEquals($data['email2'], $contact->email2);
        $this->assertEquals($data['phone2'], $contact->phone2);
    }

    /**
     * test_contact_can_be_updated
     */
    public function test_contact_can_be_updated(): void
    {
        $this->asSuperAdmin();

        $customer = Customer::factory()->create();

        $contact = $customer->contacts()->create([
            'name' => 'John Doe',
            'phone' => '123456789',
            'email' => '8xh3E@example.com',
        ]);

        $data = [
            'name' => 'Jane Doe edited',
            'phone' => '1234567891',
            'email' => '8xh3E_edited@example.com',
        ];

        $response = $this->putJson(route('contact.update', $contact), $data);

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'phone'
            ]
        ]);

        $contact->refresh();

        $this->assertEquals($data['name'], $contact->name);
        $this->assertEquals($data['email'], $contact->email);
        $this->assertEquals($data['phone'], $contact->phone);
    }
}
