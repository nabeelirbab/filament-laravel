<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{

    public function rejectReview(Review $review): RedirectResponse
    {
        $review->update(['decision' => 'rejected']);
        return redirect('admin/reviews')->with('success', 'You have declined the review request.');
    }
}
