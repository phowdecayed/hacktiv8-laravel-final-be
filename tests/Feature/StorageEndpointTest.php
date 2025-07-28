<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageEndpointTest extends TestCase
{
    /** @test */
    public function it_can_serve_existing_files_from_storage()
    {
        Storage::fake('local');
        
        // Buat file test
        $file = UploadedFile::fake()->image('test.jpg');
        Storage::disk('local')->put('public/test.jpg', $file->get());
        
        $response = $this->get('/storage/test.jpg');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    /** @test */
    public function it_returns_404_for_non_existing_files()
    {
        $response = $this->getJson('/storage/non-existing-file.jpg');
        
        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'File tidak ditemukan',
            'path' => 'non-existing-file.jpg',
            'status' => 'error'
        ]);
    }

    /** @test */
    public function it_returns_400_for_empty_path()
    {
        $response = $this->getJson('/storage/');
        
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Path tidak boleh kosong',
            'status' => 'error'
        ]);
    }

    /** @test */
    public function it_returns_400_for_directory_path()
    {
        Storage::fake('local');
        
        // Buat directory
        Storage::disk('local')->makeDirectory('public/test-directory');
        
        $response = $this->getJson('/storage/test-directory');
        
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Path yang diminta adalah directory, bukan file',
            'status' => 'error'
        ]);
    }

    /** @test */
    public function it_prevents_directory_traversal()
    {
        $response = $this->getJson('/storage/../../../etc/passwd');
        
        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_proper_headers_for_served_files()
    {
        Storage::fake('local');
        
        $file = UploadedFile::fake()->create('document.pdf', 100);
        Storage::disk('local')->put('public/document.pdf', $file->get());
        
        $response = $this->get('/storage/document.pdf');
        
        $response->assertStatus(200);
        $response->assertHeader('Cache-Control', 'public, max-age=31536000');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}