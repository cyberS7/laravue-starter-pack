<?php

namespace Api\Requests;

use App\Http\Requests\Request;

class CommentRequest extends Request
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
	    	'text' => 'required|max:100',
    	];
	}
}