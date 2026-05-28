<?php

namespace Webkul\SubscriberTags\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Webkul\SubscriberTags\Repositories\SubscriberTagRepository;

class SubscriberTagController extends Controller
{
    public function __construct(protected SubscriberTagRepository $tagRepository) {}

    public function index(int $subscriberId): JsonResponse
    {
        return response()->json([
            'selected' => $this->tagRepository->getTagsForSubscriber($subscriberId)->pluck('id')->toArray(),
            'all'      => $this->tagRepository->all(['id', 'name']),
        ]);
    }

    public function update(int $subscriberId): JsonResponse
    {
        $this->tagRepository->syncSubscriberTags($subscriberId, array_filter((array) request('tag_ids')));

        return response()->json(['message' => 'Subscriber tags updated.']);
    }
}
