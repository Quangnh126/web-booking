<?php


namespace App\Services\Web;


use App\Models\Category;

class CategoryService
{
    private $category;

    public function __construct(
        Category $category
    )
    {
        $this->category = $category;
    }

    public function listCategory()
    {
        $categories = $this->category->all();

        return $categories;
    }

}
