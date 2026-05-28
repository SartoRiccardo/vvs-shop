<?php

namespace Webkul\SubscriberTags\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Webkul\SubscriberTags\Repositories\SubscriberTagRepository;

class ProductTagController extends Controller
{
    public function __construct(protected SubscriberTagRepository $tagRepository) {}

    public function index(int $productId): JsonResponse
    {
        return response()->json([
            'selected' => $this->tagRepository->getTagsForProduct($productId)->pluck('id')->toArray(),
            'all'      => $this->tagRepository->all(['id', 'name']),
        ]);
    }

    public function update(int $productId): JsonResponse
    {
        $this->tagRepository->syncProductTags($productId, array_filter((array) request('tag_ids')));

        return response()->json(['message' => 'Product tags updated.']);
    }
}
