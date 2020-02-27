<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use \App\Helper\Helper;

use App\Models\Slides;
use Auth;


class SlideController extends Controller
{   
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    //
    public function index(Request $request)
    {   
        $slides = Slides::all();
        return view('admin.slides.index', ['slides' => $slides,  'page_name' => "Trang quản lý Slide"]);

    }

    public function edit($id='0',Request $request){

        $slide = Slides::where('id', $id)->first();
        $page_name = is_null($slide)? "Tạo Slide": 'Chỉnh sửa Slide';

                // Active
        if ($request->has('public') && !is_null($slide)) {
            $slide->fill(['is_public' => true])->save();
            session()->flash('message', ['text' => 'Đã xuất bản bài viết: '.$slide->title, 'type' => 'success']);
            return redirect()->back();
        }

        if ($request->has('unpublic') && !is_null($slide)) {
            $slide->fill(['is_public' => false])->save();
            session()->flash('message', ['text' => 'Đã xét trạng thái chưa xuất bản cho bài viết: '.$slide->title, 'type' => 'warning']);
            return redirect()->back();
        }

        return view('admin.slides.edit', ['slide'=>$slide,'page_name' => $page_name]);
    }

    public function store($id='0',Request $request){
        $slide = Slides::where('id', $id)->first();
        $unique = is_null($slide)? null: ','.$slide->id;
        $user = Auth::user();

        $rules = [
            'title' => 'required|string|max:255|min:2|unique:slides,title'.$unique,
            // 'slug' => 'string|max:512|min:2|unique:articles,slug'.$unique,
            'description' =>  'required|string|max:255',
        ];

        $data = $request->only(['title', 'slug', 'is_public', 'description', 'seo','fb_link']);
        $validated = $request->validate($rules,[
            'required' => 'Không để trống',
            'string' => 'Không dùng ký tự lạ',
            'max' => 'Quá nhiều ký tự',
            'min' => 'Không ngắn hơn 2 ký tự',
            'unique' => 'Đã có người sử dụng',
            'confirmed' => 'Nhập lại mật khẩu không đúng',
        ]);

        if(!isset($data['slug'])){
            $data['slug'] = str_slug($data['title']);
        }else{
            $data['slug'] = str_slug($data['slug']);
        }

                // $data['auth'] = $user->id;
        if(isset($data['is_public'])){
            $data['is_public'] = true;
        }else{
            $data['is_public'] = false;
        }

        if (!is_null($slide)) {
            $slide->fill($data);
            \File::deleteDirectory(public_path('slides/slide-'.$slide->id));
            $slide->save();
            $user->slides()->save($slide);
        }else{
            $data['user_id'] = $user->id;
            $slide = Slides::create($data);
            $user->slides()->save($slide);
        }

        $slides_data = $request->only('slides');
        foreach ($slides_data['slides'] as $key => $slide_data) {
            if($slide_data['type']==config("config.slides.types.1")){
                $width = config("config.slides.default.image_default_width");
                $height = config("config.slides.default.image_default_height");

                $imageUrl = Helper::upload_picture($width,$height,
                config('lfm.base_directory').(Str::after($slide_data['originUrl'],config('lfm.url_prefix'))),
                'slides/slide-'.$slide->id.'/',
                'slide-'.$key.'.PNG'
                );
                $slides_data['slides'][$key]['imageUrl'] = asset($imageUrl);
            }
        
        }

        $slide->data = $slides_data;

        Helper::update_time_public($slide);


        $text = is_null($slide)? "Đã tạo thành công bài viết:". $slide->title: "Đã cập nhật thành công bài viết:".$slide->name;

        session()->flash('message', ['text' => $text, 'type' => 'success']);

        return redirect()->route('admin_slides');
    }

    public function delete($id, Request $request) {
        $slide = Slides::where('id', '=', $id)->firstOrFail ();
        return view('admin.slides.delete', ['slide'=> $slide, 'page_name' => 'Xóa Slide']);
    }

    public function delete_comfirm($id, Request $request) {
        $slide = Slides::where('id', '=', $id)->firstOrFail ();
        $slide->delete();
        \File::deleteDirectory(public_path('slides/slide-'.$slide->id));

        session()->flash('message', ['text'=>'deleted!','type'=>'success' ]);

        return redirect()->route('admin_slides');
    }

}
