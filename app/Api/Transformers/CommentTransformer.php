<?php

namespace Api\Transformers;

use App\Comment;
use League\Fractal\TransformerAbstract;

class CommentTransformer extends TransformerAbstract
{
	public function transform(Comment $comment)
	{
		return [
			'id' 	=> (int) $comment->id,
			'text'  => $comment->text,
		];
	}
}