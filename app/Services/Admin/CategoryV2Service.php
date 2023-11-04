<?php


namespace App\Services\Admin;


use App\Models\Category;
use App\Enums\Constant;

class CategoryV2Service
{
    private $category;

    public function __construct(
        Category $category
    )
    {
        $this->category = $category;
    }

    /**
     * list all category
     * @return mixed
     */
    public function listCategory($request)
    {
        $search = $request->search;
        $perPage = $request->perpage ?? Constant::ORDER_BY;

        $category = $this->category
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })->paginate($perPage);

        return $category;
    }

    /**
     * create category
     */
    public function createCategory(array $req)
    {
        return $this->category->create($req);
    }

    /**
     * update category
     */
    public function updateCategory(array $req, int $id)
    {
        $category = $this->category->where('id', $id)->first();
        $category->update($req);
        return $category;
    }

    /**
     * @param array $ids_delete
     */
    public function deleteMultipleCategory(array $ids_delete): void
    {
        $this->category->whereIn('id', $ids_delete)->delete();
    }

    /**
     * @param array $req
     * @return string
     */
    public function checkNameExist(array $req)
    {
        $category = $this->category->where('name', 'like', $req['name'])->first();
        if ($category) {
            return false;
        }


        return true;
    }

}
