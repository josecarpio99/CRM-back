<?php

namespace Tests\Feature\Api;

use App\Models\Deal;
use App\Models\Source;
use App\Models\Category;
use App\Models\Customer;
use Tests\FeatureTestCase;
use Database\Seeders\SourceSeeder;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\DealEstimatedCloseDateRangeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DealControllerTest extends FeatureTestCase
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
     * test deal with new customer can be created
     */
    public function test_deal_with_new_customer_can_be_created(): void
    {
        $this->asSuperAdmin();

        $source = Source::first();
        $category = Category::first();

        $data = [
            'name' => 'Project name',
            'city' => 'City',
            'requirement' => 'Requirement',
            'value' => 10000,
            'customer' => [
                'name' => 'John Doe',
                'company_name' => 'Company Name',
                'category_id' => $category->id,
                'email' => '8xh3E@example.com',
                'mobile' => '123456789'
            ],
            'owner_id' => $this->getUser()->id,
            'source_id' => $source->id,
            'estimated_close_date_range' => DealEstimatedCloseDateRangeEnum::fromZeroToThreeMonths->value
        ];

        $response = $this->postJson(route('deal.store'), $data);

        $response->assertCreated();

        $this->assertDatabaseCount('deals', 1);

        $deal = Deal::first();

        $this->assertEquals($data['name'], $deal->name);
        $this->assertEquals($data['city'], $deal->city);
        $this->assertEquals($data['requirement'], $deal->requirement);
        $this->assertEquals($data['value'], $deal->value);
        $this->assertEquals($data['owner_id'], $deal->owner_id);

        $customer = $deal->customer;

        $this->assertEquals($data['customer']['company_name'], $customer->company_name);
        $this->assertEquals($data['customer']['category_id'], $customer->category_id);

        $contact = $deal->contacts()->first();

        $this->assertEquals($data['customer']['name'], $contact->name);
        $this->assertEquals($data['customer']['email'], $contact->email);
        $this->assertEquals($data['customer']['mobile'], $contact->phone);
    }

    /**
     * test deal with existing customer can be created
     */
    public function test_deal_with_existing_customer_can_be_created(): void
    {
        $this->withoutExceptionHandling();

        $this->asSuperAdmin();

        $source = Source::first();
        $category = Category::first();
        $customer = Customer::factory()->create();

        $contact = $customer->contacts()->create([
            'name' => 'John Doe',
            'email' => '8xh3E@example.com',
            'phone' => '123456789'
        ]);

        $data = [
            'name' => 'Project name',
            'city' => 'City',
            'requirement' => 'Requirement',
            'value' => 10000,
            'customer_id' => $customer->id,
            'owner_id' => $this->getUser()->id,
            'source_id' => $source->id,
            'estimated_close_date_range' => DealEstimatedCloseDateRangeEnum::fromZeroToThreeMonths->value,
            'contact_id' => $contact->id
        ];

        $response = $this->postJson(route('deal.store'), $data);

        $response->assertCreated();

        $this->assertDatabaseCount('deals', 1);

        $deal = Deal::first();

        $this->assertEquals($data['customer_id'], $deal->customer_id);

        $dealContact = $deal->contacts()->first();

        $this->assertEquals($contact->id, $dealContact->id);
    }
}
