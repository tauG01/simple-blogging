<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthorPostsController extends Controller
{
    public function index()
    {
        return view('author.posts.index', [
            //  'posts' => Post::latest()->get()
           'posts' => auth()->user()->posts()->latest()->get()
        ]);
    }

    public function create(){
        $categories = Category::all();
        return view ('author.posts.create', compact('categories'));
    }

    public function store()
    {
        $attributes = request()->validate([
            'title' => ['required'],
            'thumbnail' => ['required','image'],
            'slug' => ['required', Rule::unique('posts', 'slug')],
            'excerpt' => ['required'],
            'body' => ['required'],
            'category_id' => ['required', Rule::exists('categories', 'id')]
        ]);

        $attributes['user_id'] = auth() -> id();

        $path = null;
        if(request()->hasFile('thumbnail')){
            $path = (request()->file('thumbnail')->store('thumbnails', 'public'));
        }

        $attributes['thumbnail'] = $path;

        Post::create($attributes);

        return redirect('/');
    }

    public function edit(Post $post)
    {
        $categories = Category::all();
        return view('author.posts.edit', ['post' => $post], compact('categories'));
    }

    public function update(Post $post)
    {
        $attributes = request()->validate([
            'title' => ['required'],
            'thumbnail' => ['image'],
            'slug' => ['required', Rule::unique('posts', 'slug')->ignore($post->id)],
            'excerpt' => ['required'],
            'body' => ['required'],
            'category_id' => ['required', Rule::exists('categories', 'id')]
        ]);

        if(request()->hasFile('thumbnail')){
            $attributes['thumbnail'] = request()->file('thumbnail')->store('thumbnails');
        }

        $post->update($attributes);

        return redirect('author/posts')->with('success', 'Post Updated');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('success', 'Post Deleted');
    }
}
