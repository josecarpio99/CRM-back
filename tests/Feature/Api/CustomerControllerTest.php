<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Source;
use App\Enums\RoleEnum;
use App\Models\Category;
use App\Models\Customer;
use Tests\FeatureTestCase;
use Database\Seeders\SourceSeeder;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerControllerTest extends FeatureTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            SourceSeeder::class,
            CategorySeeder::class
        ]);
    }

    /**
     * test_customer_can_be_created
     */
    public function test_customer_can_be_created(): void
    {
        $this->asSuperAdmin();

        $owner = User::where('role', RoleEnum::Advisor->value)->first();
        $category = Category::first();
        $source = Source::first();

        $data = [
            'star' => false,
            'name' => 'John Doe',
            'email' => '8xh3E@example.com',
            'mobile' => '123456789',
            'company_name' => 'Company Name',
            'razon_social' => 'Razon Social',
            'city' => 'City',
            'source_id' => $source->id,
            'owner_id' => $owner->id,
            'category_id' => $category->id,
        ];

        $response = $this->postJson(route('customer.store'), $data);

        $response->assertCreated();

        $customer = Customer::first();

        $this->assertEquals($data['company_name'], $customer->company_name);
        $this->assertEquals($data['razon_social'], $customer->razon_social);
        $this->assertEquals($data['city'], $customer->city);
        $this->assertEquals($data['source_id'], $customer->source_id);
        $this->assertEquals($data['owner_id'], $customer->owner_id);
        $this->assertEquals($data['category_id'], $customer->category_id);

        $contact = $customer->contacts()->first();

        $this->assertEquals($data['name'], $contact->name);
        $this->assertEquals($data['email'], $contact->email);
        $this->assertEquals($data['mobile'], $contact->phone);
    }
}
