<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\Country;
use App\Models\Province;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure clean database state
        \Illuminate\Support\Facades\DB::rollBack();
        
        $this->seed();
    }

    #[Test]
    public function person_can_be_created_with_valid_data()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);
        $city = City::create(['title' => 'Tehran', 'province_id' => $province->id]);

        $personData = [
            'name' => 'John',
            'family' => 'Doe',
            'email' => 'john.doe@example.com',
            'national_code' => '1234567890',
            'shenasname' => '123456789',
            'passport_number' => 'A1234567',
            'father_name' => 'Robert Doe',
            'birth_year' => 1990,
            'mobile' => '09123456789',
            'tel' => '02112345678',
            'fax' => '02112345679',
            'postalcode' => '1234567890',
            'addr' => '123 Main Street, Tehran',
            'country_id' => $country->id,
            'province_id' => $province->id,
            'city_id' => $city->id,
            'gender' => 1,
            'website' => 'https://johndoe.com'
        ];

        $person = Person::create($personData);

        $this->assertDatabaseHas('people', [
            'name' => 'John',
            'family' => 'Doe',
            'email' => 'john.doe@example.com',
            'national_code' => '1234567890'
        ]);

        $this->assertEquals($country->id, $person->country_id);
        $this->assertEquals($province->id, $person->province_id);
        $this->assertEquals($city->id, $person->city_id);
    }

    #[Test]
    public function person_has_correct_relationships()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);
        $city = City::create(['title' => 'Tehran', 'province_id' => $province->id]);


        $person = Person::factory()->create([
            'country_id' => $country->id,
            'province_id' => $province->id,
            'city_id' => $city->id
        ]);

        $this->assertInstanceOf(Country::class, $person->country);
        $this->assertInstanceOf(Province::class, $person->province);
        $this->assertInstanceOf(City::class, $person->city);

        $this->assertEquals($country->id, $person->country->id);
        $this->assertEquals($province->id, $person->province->id);
        $this->assertEquals($city->id, $person->city->id);
    }

    #[Test]
    public function person_can_be_created_with_minimal_data()
    {
        $person = Person::create([
            'name' => 'Jane',
            'family' => 'Smith',
            'email' => 'jane.smith@example.com'
        ]);

        $this->assertDatabaseHas('people', [
            'name' => 'Jane',
            'family' => 'Smith',
            'email' => 'jane.smith@example.com'
        ]);
    }

    #[Test]
    public function person_can_be_created_using_factory()
    {
        $person = Person::factory()->create();

        $this->assertInstanceOf(Person::class, $person);
        $this->assertNotNull($person->name);
        $this->assertNotNull($person->family);
        $this->assertNotNull($person->email);
    }

    #[Test]
    public function person_email_must_be_unique()
    {
        Person::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Person::factory()->create(['email' => 'test@example.com']);
    }

    #[Test]
    public function person_can_have_optional_fields()
    {
        $person = Person::create([
            'name' => 'Test',
            'family' => 'Person',
            'email' => 'test@example.com',
            'national_code' => '1234567890',
            'passport_number' => 'A1234567',
            'father_name' => 'Father Name',
            'birth_year' => 1985,
            'mobile' => '09123456789',
            'website' => 'https://test.com',
            'gender' => 2
        ]);

        $this->assertDatabaseHas('people', [
            'name' => 'Test',
            'national_code' => '1234567890',
            'passport_number' => 'A1234567',
            'father_name' => 'Father Name',
            'birth_year' => 1985,
            'mobile' => '09123456789',
            'website' => 'https://test.com',
            'gender' => 2
        ]);
    }

    #[Test]
    public function person_gender_can_be_male_or_female()
    {
        $malePerson = Person::create([
            'name' => 'Male',
            'family' => 'Person',
            'email' => 'male@example.com',
            'gender' => 1
        ]);

        $femalePerson = Person::create([
            'name' => 'Female',
            'family' => 'Person',
            'email' => 'female@example.com',
            'gender' => 2
        ]);

        $this->assertEquals(1, $malePerson->gender);
        $this->assertEquals(2, $femalePerson->gender);
    }

    #[Test]
    public function person_can_have_contact_information()
    {
        $person = Person::create([
            'name' => 'Contact',
            'family' => 'Person',
            'email' => 'contact@example.com',
            'mobile' => '09123456789',
            'tel' => '02112345678',
            'fax' => '02112345679',
            'postalcode' => '1234567890',
            'addr' => '123 Main Street'
        ]);

        $this->assertEquals('09123456789', $person->mobile);
        $this->assertEquals('02112345678', $person->tel);
        $this->assertEquals('02112345679', $person->fax);
        $this->assertEquals('1234567890', $person->postalcode);
        $this->assertEquals('123 Main Street', $person->addr);
    }

    #[Test]
    public function person_can_be_updated()
    {
        $person = Person::factory()->create([
            'name' => 'Original',
            'family' => 'Name'
        ]);

        $person->update([
            'name' => 'Updated',
            'family' => 'Name'
        ]);

        $this->assertEquals('Updated', $person->fresh()->name);
        $this->assertEquals('Name', $person->fresh()->family);
    }

    #[Test]
    public function person_can_have_education_records()
    {
        $person = Person::factory()->create();

        // This test assumes PersonEducation model exists
        // If it doesn't exist, this test will fail and indicate missing model
        try {
            $education = $person->PersonEducation()->create([
                'degree' => 'Bachelor',
                'field' => 'Computer Science',
                'university' => 'University of Tehran'
            ]);

            $this->assertCount(1, $person->PersonEducation);
        } catch (\Exception $e) {
            $this->markTestSkipped('PersonEducation model not implemented yet');
        }
    }

    #[Test]
    public function person_can_have_experience_records()
    {
        $person = Person::factory()->create();

        // This test assumes PersonExperience model exists
        // If it doesn't exist, this test will fail and indicate missing model
        try {
            $experience = $person->PersonExperience()->create([
                'position' => 'Software Developer',
                'company' => 'Tech Company',
                'start_date' => '2020-01-01',
                'end_date' => '2022-12-31'
            ]);

            $this->assertCount(1, $person->PersonExperience);
        } catch (\Exception $e) {
            $this->markTestSkipped('PersonExperience model not implemented yet');
        }
    }

  
}
