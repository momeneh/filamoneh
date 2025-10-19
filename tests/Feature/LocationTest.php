<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Province;
use App\Models\City;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function country_can_be_created()
    {
        $country = Country::create(['title' => 'Iran']);

        $this->assertDatabaseHas('countries', [
            'title' => 'Iran'
        ]);

        $this->assertInstanceOf(Country::class, $country);
    }

    #[Test]
    public function country_can_have_multiple_provinces()
    {
        $country = Country::create(['title' => 'Iran']);
        
        $province1 = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);
        $province2 = Province::create(['title' => 'Isfahan', 'country_id' => $country->id]);

        $this->assertDatabaseHas('provinces', [
            'title' => 'Tehran',
            'country_id' => $country->id
        ]);

        $this->assertDatabaseHas('provinces', [
            'title' => 'Isfahan',
            'country_id' => $country->id
        ]);
    }

    #[Test]
    public function province_can_be_created()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);

        $this->assertDatabaseHas('provinces', [
            'title' => 'Tehran',
            'country_id' => $country->id
        ]);

        $this->assertInstanceOf(Province::class, $province);
    }

    #[Test]
    public function province_belongs_to_country()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);

        $this->assertInstanceOf(Country::class, $province->country);
        $this->assertEquals($country->id, $province->country->id);
        $this->assertEquals('Iran', $province->country->title);
    }

    #[Test]
    public function province_can_have_multiple_cities()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);
        
        $city1 = City::create(['title' => 'Tehran', 'province_id' => $province->id]);
        $city2 = City::create(['title' => 'Karaj', 'province_id' => $province->id]);

        $this->assertDatabaseHas('cities', [
            'title' => 'Tehran',
            'province_id' => $province->id
        ]);

        $this->assertDatabaseHas('cities', [
            'title' => 'Karaj',
            'province_id' => $province->id
        ]);
    }

    #[Test]
    public function city_can_be_created()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);
        $city = City::create(['title' => 'Tehran', 'province_id' => $province->id]);

        $this->assertDatabaseHas('cities', [
            'title' => 'Tehran',
            'province_id' => $province->id
        ]);

        $this->assertInstanceOf(City::class, $city);
    }

    #[Test]
    public function city_belongs_to_province()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);
        $city = City::create(['title' => 'Tehran', 'province_id' => $province->id]);

        $this->assertInstanceOf(Province::class, $city->province);
        $this->assertEquals($province->id, $city->province->id);
        $this->assertEquals('Tehran', $city->province->title);
    }

    #[Test]
    public function city_can_access_country_through_province()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);
        $city = City::create(['title' => 'Tehran', 'province_id' => $province->id]);

        $this->assertInstanceOf(Country::class, $city->province->country);
        $this->assertEquals($country->id, $city->province->country->id);
        $this->assertEquals('Iran', $city->province->country->title);
    }

    #[Test]
    public function person_can_be_associated_with_location_hierarchy()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);
        $city = City::create(['title' => 'Tehran', 'province_id' => $province->id]);

        $person = Person::create([
            'name' => 'John',
            'family' => 'Doe',
            'email' => 'john@example.com',
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
    public function location_cascade_relationships_work()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);
        $city = City::create(['title' => 'Tehran', 'province_id' => $province->id]);

        // Test that we can access city -> province -> country
        $this->assertEquals('Iran', $city->province->country->title);
        $this->assertEquals('Tehran', $city->province->title);
        $this->assertEquals('Tehran', $city->title);
    }

    #[Test]
    public function country_title_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Country::create([]);
    }

    #[Test]
    public function province_title_is_required()
    {
        $country = Country::create(['title' => 'Iran']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Province::create(['country_id' => $country->id]);
    }

    #[Test]
    public function province_country_id_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Province::create(['title' => 'Tehran']);
    }

    #[Test]
    public function city_title_is_required()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        City::create(['province_id' => $province->id]);
    }

    #[Test]
    public function city_province_id_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        City::create(['title' => 'Tehran']);
    }

    #[Test]
    public function location_models_can_be_updated()
    {
        $country = Country::create(['title' => 'Old Country']);
        $province = Province::create(['title' => 'Old Province', 'country_id' => $country->id]);
        $city = City::create(['title' => 'Old City', 'province_id' => $province->id]);

        $country->update(['title' => 'New Country']);
        $province->update(['title' => 'New Province']);
        $city->update(['title' => 'New City']);

        $this->assertEquals('New Country', $country->fresh()->title);
        $this->assertEquals('New Province', $province->fresh()->title);
        $this->assertEquals('New City', $city->fresh()->title);
    }

    #[Test]
    public function location_models_can_be_deleted()
    {
        $country = Country::create(['title' => 'Test Country']);
        $province = Province::create(['title' => 'Test Province', 'country_id' => $country->id]);
        $city = City::create(['title' => 'Test City', 'province_id' => $province->id]);

        $countryId = $country->id;
        $provinceId = $province->id;
        $cityId = $city->id;

        $city->delete();
        $province->delete();
        $country->delete();

        $this->assertDatabaseMissing('cities', ['id' => $cityId]);
        $this->assertDatabaseMissing('provinces', ['id' => $provinceId]);
        $this->assertDatabaseMissing('countries', ['id' => $countryId]);
    }

    #[Test]
    public function multiple_people_can_live_in_same_city()
    {
        $country = Country::create(['title' => 'Iran']);
        $province = Province::create(['title' => 'Tehran', 'country_id' => $country->id]);
        $city = City::create(['title' => 'Tehran', 'province_id' => $province->id]);

        $person1 = Person::create([
            'name' => 'John',
            'family' => 'Doe',
            'email' => 'john@example.com',
            'city_id' => $city->id,
            'province_id' => $province->id,
            'country_id' => $country->id
        ]);

        $person2 = Person::create([
            'name' => 'Jane',
            'family' => 'Smith',
            'email' => 'jane@example.com',
            'city_id' => $city->id,
            'province_id' => $province->id,
            'country_id' => $country->id
        ]);

        $this->assertEquals($city->id, $person1->city_id);
        $this->assertEquals($city->id, $person2->city_id);
        $this->assertEquals($person1->city_id, $person2->city_id);
    }
}
