<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'profile_image' => 'required|image|mimes:jpeg,png|max:2048',
            'category_id'   => 'required|array|min:1',
            'category_id.*' => 'exists:categories,id',
            'condition' => 'required|exists:conditions,id',
            'price' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => '商品名を入力してください。',
            'description.required' => '商品説明を入力してください。',
            'description.max' => '商品説明は255文字以内で入力してください。',
            'profile_image.required' => '商品画像をアップロードしてください。',
            'profile_image.image' => '有効な画像ファイルを選択してください。',
            'profile_image.mimes' => '画像はjpegまたはpng形式でアップロードしてください。',
            'category_id.required' => '商品のカテゴリーを選択してください。',
            'condition.required' => '商品の状態を選択してください。',
            'price.required' => '販売価格を入力してください。',
            'price.numeric' => '価格は数値で入力してください。',
            'price.min' => '価格は0円以上で入力してください。',
        ];
    }
}
