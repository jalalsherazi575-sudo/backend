<?php

namespace Laraspace\Observers;

use Laraspace\QuestionCommnent;

class CommentObserver
{
    public function deleting(QuestionCommnent $comment)
    {
        // Delete the children recursively
        $this->deleteChildren($comment);
    }

    protected function deleteChildren(QuestionCommnent $comment)
    {
        // Find and delete the children of the current comment
        $children = QuestionCommnent::where('parentId', $comment->id)->get();
        foreach ($children as $child) {
            $this->deleteChildren($child); // Recursively delete children
            $child->delete();
        }
    }
}
