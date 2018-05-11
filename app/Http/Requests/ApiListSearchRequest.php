<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;

class ApiListSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public $search;
    public $searchHash;
    public $page;

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
            'search' => 'required',
            'page' => 'integer'
        ];
    }

    public function checkCache(){
        $this->search = urlencode($this->input('search'));
        $this->page = $this->input('page');
        $this->searchHash = md5($this->search . $this->page);

        $response = array();
        if (Cache::has($this->searchHash)){
            $response = Cache::get($this->searchHash);
            $response = json_decode($response, true);
            return $response;
        } else {
            return false;
        }
    }
}
