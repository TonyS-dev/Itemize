<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CloudinaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_image()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('product.jpg');

        $this->mock(CloudinaryService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')
                ->once()
                ->andReturn('https://res.cloudinary.com/demo/image/upload/v1/product.jpg');
        });

        $response = $this->actingAs($user)
            ->post('/api/images', [
                'image' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertSee('https://res.cloudinary.com/demo/image/upload/v1/product.jpg');
    }

    public function test_upload_requires_valid_image()
    {
        $user = User::factory()->create();

        // Mock the service to properly handle dependency injection even if validation fails
        $this->mock(CloudinaryService::class);

        $response = $this->actingAs($user)
            ->postJson('/api/images', [
                'image' => 'not-an-image',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    }
}
