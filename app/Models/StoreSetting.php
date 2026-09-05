<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = ['fresh_label', 'fresh_title', 'fresh_image'];

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'fresh_label' => 'This week',
            'fresh_title' => 'Fresh arrivals',
            'fresh_image' => 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=1200&q=90',
        ]);
    }
}
