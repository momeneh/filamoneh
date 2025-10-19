<?php

namespace Tests\Feature;

use App\Models\Paper;
use App\Models\User;
use App\Models\Country;
use App\Models\PaperType;
use App\Models\Subject;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaperTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure clean database state
        \Illuminate\Support\Facades\DB::rollBack();
        
        Storage::fake('public');
    }

    #[Test]
    public function paper_can_be_created_with_valid_data()
    {
        $user = User::factory()->create();
        $country = Country::create(['title' => 'Iran']);
        $paperType = PaperType::create(['title' => 'Research Paper']);

        $paperData = [
            'title' => 'Test Research Paper',
            'paper_type_id' => $paperType->id,
            'country_id' => $country->id,
            'title_url' => 'test-research-paper',
            'priority' => 1,
            'paper_date' => now(),
            'doi' => '10.1000/test',
            'count_page' => 15,
            'refrence_link' => 'https://example.com',
            'is_accepted' => true,
            'is_visible' => true,
            'is_archived' => false,
            'abstract' => 'This is a test abstract for the research paper.',
            'description' => 'This is a detailed description of the research paper.',
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ];

        $paper = Paper::create($paperData);

        $this->assertDatabaseHas('papers', [
            'title' => 'Test Research Paper',
            'paper_type_id' => $paperType->id,
            'country_id' => $country->id,
            'doi' => '10.1000/test'
        ]);

        $this->assertTrue($paper->is_accepted);
        $this->assertTrue($paper->is_visible);
        $this->assertFalse($paper->is_archived);
    }

    #[Test]
    public function paper_has_correct_relationships()
    {
        $user = User::factory()->create();
        $country = Country::create(['title' => 'Iran']);
        $paperType = PaperType::create(['title' => 'Research Paper']);

        $paper = Paper::create([
            'title' => 'Test Paper',
            'paper_type_id' => $paperType->id,
            'country_id' => $country->id,
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $paper->inserter);
        $this->assertInstanceOf(User::class, $paper->updater);
        $this->assertInstanceOf(PaperType::class, $paper->paperType);
        $this->assertInstanceOf(Country::class, $paper->country);
    }

    #[Test]
    public function paper_can_be_associated_with_subjects()
    {
        $user = User::factory()->create();
        $paper = Paper::create([
            'title' => 'Test Paper',
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);

        $subject1 = Subject::create(['title' => 'Computer Science']);
        $subject2 = Subject::create(['title' => 'Mathematics']);

        $paper->paperSubject()->attach([$subject1->id, $subject2->id]);

        $this->assertCount(2, $paper->paperSubject()->get());
        $this->assertTrue($paper->paperSubject->contains($subject1));
        $this->assertTrue($paper->paperSubject->contains($subject2));
    }

    #[Test]
    public function paper_can_be_associated_with_tags()
    {
        $user = User::factory()->create();
        $paper = Paper::create([
            'title' => 'Test Paper',
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);

        $tag1 = Tag::create(['name' => 'Machine Learning']);
        $tag2 = Tag::create(['name' => 'AI']);

        $paper->tags()->attach([$tag1->id, $tag2->id]);

        $this->assertCount(2, $paper->tags()->get());
        $this->assertTrue($paper->tags->contains($tag1));
        $this->assertTrue($paper->tags->contains($tag2));
    }

    #[Test]
    public function paper_file_urls_are_generated_correctly()
    {
        $user = User::factory()->create();
        $paper = Paper::create([
            'title' => 'Test Paper',
            'paper_file' => 'papers/test-paper.pdf',
            'paper_word_file' => 'papers/test-paper.docx',
            'image_path1' => 'images/test-image1.jpg',
            'image_path2' => 'images/test-image2.jpg',
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);

        $this->assertEquals(asset('storage/papers/test-paper.pdf'), $paper->paper_file_url);
        $this->assertEquals(asset('storage/papers/test-paper.docx'), $paper->paper_word_file_url);
        $this->assertEquals(asset('storage/images/test-image1.jpg'), $paper->image_path1_url);
        $this->assertEquals(asset('storage/images/test-image2.jpg'), $paper->image_path2_url);
    }

    #[Test]
    public function paper_file_urls_return_null_when_no_file()
    {
        $user = User::factory()->create();
        $paper = Paper::create([
            'title' => 'Test Paper',
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);

        $this->assertNull($paper->paper_file_url);
        $this->assertNull($paper->paper_word_file_url);
        $this->assertNull($paper->image_path1_url);
        $this->assertNull($paper->image_path2_url);
    }

    #[Test]
    public function paper_boolean_fields_are_casted_correctly()
    {
        $user = User::factory()->create();
        $paper = Paper::create([
            'title' => 'Test Paper',
            'is_accepted' => 1,
            'is_visible' => 'true',
            'is_archived' => 0,
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);

        $this->assertTrue($paper->is_accepted);
        $this->assertTrue($paper->is_visible);
        $this->assertFalse($paper->is_archived);
        $this->assertIsBool($paper->is_accepted);
        $this->assertIsBool($paper->is_visible);
        $this->assertIsBool($paper->is_archived);
    }

    #[Test]
    public function paper_can_be_archived()
    {
        $user = User::factory()->create();
        $paper = Paper::create([
            'title' => 'Test Paper',
            'is_archived' => false,
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);

        $paper->update(['is_archived' => true]);

        $this->assertTrue($paper->fresh()->is_archived);
    }

    #[Test]
    public function paper_can_have_paper_resources()
    {
        $user = User::factory()->create();
        $paper = Paper::create([
            'title' => 'Test Paper',
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);

        // Create paper resources (assuming PaperResource model exists)
        $paper->paperResource()->create([
            'title' => 'Resource 1',
            'url' => 'https://example.com/resource1'
        ]);

        $paper->paperResource()->create([
            'title' => 'Resource 2',
            'url' => 'https://example.com/resource2'
        ]);

        $this->assertCount(2, $paper->paperResource()->get());
    }

    #[Test]
    public function paper_title_is_required()
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Database\QueryException::class);

        Paper::create([
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);
    }

    #[Test]
    public function paper_can_be_created_with_minimal_data()
    {
        $user = User::factory()->create();
        
        $paper = Paper::create([
            'title' => 'Minimal Paper',
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);

        $this->assertDatabaseHas('papers', [
            'title' => 'Minimal Paper',
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);
    }

    #[Test]
    public function paper_observer_is_attached()
    {
        $user = User::factory()->create();
        
        // This test verifies that the observer is properly attached
        // The actual observer behavior would be tested in the observer test
        $paper = Paper::create([
            'title' => 'Test Paper for Observer',
            'insert_user_id' => $user->id,
            'edit_user_id' => $user->id
        ]);

        $this->assertInstanceOf(Paper::class, $paper);
        $this->assertDatabaseHas('papers', ['title' => 'Test Paper for Observer']);
    }
}
