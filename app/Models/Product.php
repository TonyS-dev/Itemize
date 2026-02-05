<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    // protected $table = 'products';
    // protected $primaryKey = 'id';
    // public $timestamps = false; // Si no quieres created_at/updated_at
    // protected $guarded = []; // Alternativa a $fillable (todos menos estos)
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'user_id',
        'category_id',
        'image',
        'gallery',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'gallery' => 'array',
    ];

    /**
     * Owner relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Single category (legacy, for backwards compatibility)
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Multiple categories (new many-to-many)
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    // protected $hidden = []; // Hide JSON Fields (e.g: passwords)
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | CONSTANTS
    |--------------------------------------------------------------------------
    /

    /
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    /

    /
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    /

    /
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    /

    /
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    /

    /
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
