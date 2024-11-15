<?php

namespace Tests\Feature\Api;

use App\Models\Lead;
use App\Models\User;
use App\Models\Source;
use App\Enums\RoleEnum;
use App\Models\Category;
use Tests\FeatureTestCase;
use Database\Seeders\SourceSeeder;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LeadControllerTest extends FeatureTestCase
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
     * test lead can be created.
     */
    public function test_lead_can_be_created(): void
    {
        $this->asSuperAdmin();

        $owner = User::where('role', RoleEnum::Advisor->value)->first();
        $category = Category::first();
        $source = Source::first();

        $data = [
            'name' => 'John Doe',
            'email' => '8xh3E@example.com',
            'mobile' => '123456789',
            'company_name' => 'Company Name',
            'razon_social' => 'Razon Social',
            'city' => 'City',
            'source_id' => $source->id,
            'owner_id' => $owner->id,
            'category_id' => $category->id,
            'requirement' => 'Requirement',
        ];

        $response = $this->postJson(route('lead.store'), $data);

        $response->assertCreated();

        $lead = Lead::first();

        $this->assertEquals($data['company_name'], $lead->company_name);
        $this->assertEquals($data['requirement'], $lead->requirement);
        $this->assertEquals($data['razon_social'], $lead->razon_social);
        $this->assertEquals($data['city'], $lead->city);
        $this->assertEquals($data['source_id'], $lead->source_id);
        $this->assertEquals($data['owner_id'], $lead->owner_id);
        $this->assertEquals($data['category_id'], $lead->category_id);

        $contact = $lead->contacts()->first();

        $this->assertEquals($data['name'], $contact->name);
        $this->assertEquals($data['email'], $contact->email);
        $this->assertEquals($data['mobile'], $contact->phone);
    }
}
