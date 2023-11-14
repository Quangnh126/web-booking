<?php


namespace App\Services\Web;


use App\Models\Comment;

class ReviewService
{
    private $review;

    public function __construct(
        Comment $comment
    )
    {
        $this->review = $comment;
    }

    public function createReview($data)
    {
        $review = $this->review->create($data);

        return $review;
    }

    public function listReviewRoom($id)
    {
        $review = $this->review->with('user', 'room', 'image')
            ->where('room_id', $id)
            ->paginate(5);

        return $review;
    }

    public function checkReview($user_id, $room_id)
    {
        $review = $this->review->where('user_id', $user_id)
            ->where('room_id', $room_id)
            ->first();

        if (!$review) {
            return false;
        }
        return true;
    }

}
