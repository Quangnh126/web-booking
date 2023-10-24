<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    static $room = 'room';
    static $tour = 'tour';
    static $about_us = 'about_us';
    static $file = 'file';

    protected $table = 'files';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'file_id',
        'image_data',
    ];

}
