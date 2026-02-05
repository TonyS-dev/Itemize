<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    protected $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB max
        ], [
            'image.max' => 'The image size must be less than 5MB.',
        ]);

        try {
            $url = $this->cloudinary->upload($request->file('image'));
            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Cloudinary upload failed: ' . $e->getMessage()], 500);
        }
    }
}
