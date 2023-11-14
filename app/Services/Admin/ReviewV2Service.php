<?php


namespace App\Services\Admin;


use App\Enums\Constant;
use App\Models\Comment;
use App\Services\FileUploadServices\FileService;

class ReviewV2Service
{
    private $review;
    private $fileService;

    public function __construct(
        Comment $comment,
        FileService $fileService
    )
    {
        $this->review = $comment;
        $this->fileService = $fileService;
    }

    public function listReview($request)
    {
        $rate = $request->rate;
        $room_id = $request->room_id;
        $user_id = $request->user_id;
        $perPage = $request->perpage ?? Constant::ORDER_BY;

        $reviews = $this->review->with('user', 'room', 'image')
            ->when($rate, function ($query) use ($rate) {
                $query->whereIn('rate', $rate);
            })
            ->when($room_id, function ($query) use ($room_id) {
                $query->whereIn('room_id', $room_id);
            })
            ->when($user_id, function ($query) use ($user_id) {
                $query->whereIn('user_id', $user_id);
            })
            ->paginate($perPage);

        return $reviews;
    }

    public function detailReview($id)
    {
        $review = $this->review->ofId($id)
            ->with('user', 'room', 'image')
            ->first();

        return $review;
    }

    public function deleteMultipleReviews($ids)
    {
        $reviews = $this->review
            ->whereIn('id', $ids)
            ->get();

        foreach ($reviews as $review) {
            foreach ($review->image as $image) {
                $this->fileService->deleteImage($image['image_data']);
                $this->fileService->deleteData($image['id']);
            }
            $review->delete();
        }

        return $reviews;
    }

}
