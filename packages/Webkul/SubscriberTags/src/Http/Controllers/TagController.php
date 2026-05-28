<?php

namespace Webkul\SubscriberTags\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Webkul\SubscriberTags\Repositories\SubscriberTagRepository;

class TagController extends Controller
{
    public function __construct(protected SubscriberTagRepository $tagRepository) {}

    public function index()
    {
        if (request()->ajax()) {
            return $this->tagRepository->all();
        }

        return view('subscriber_tags::tags.index');
    }

    public function store(): JsonResponse
    {
        request()->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:subscriber_tags,slug',
        ]);

        $tag = $this->tagRepository->create([
            'name'                    => request('name'),
            'slug'                    => request('slug') ?: Str::slug(request('name')),
            'auto_assign_on_purchase' => (bool) request('auto_assign_on_purchase'),
        ]);

        return response()->json(['message' => 'Tag created.', 'tag' => $tag]);
    }

    public function edit(int $id): JsonResponse
    {
        return response()->json($this->tagRepository->findOrFail($id));
    }

    public function update(int $id): JsonResponse
    {
        $tag = $this->tagRepository->findOrFail($id);

        request()->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:subscriber_tags,slug,'.$id,
        ]);

        $tag->update([
            'name'                    => request('name'),
            'slug'                    => request('slug') ?: Str::slug(request('name')),
            'auto_assign_on_purchase' => (bool) request('auto_assign_on_purchase'),
        ]);

        return response()->json(['message' => 'Tag updated.', 'tag' => $tag]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->tagRepository->findOrFail($id)->delete();

        return response()->json(['message' => 'Tag deleted.']);
    }

    public function all(): JsonResponse
    {
        return response()->json($this->tagRepository->all(['id', 'name', 'slug', 'auto_assign_on_purchase']));
    }
}
