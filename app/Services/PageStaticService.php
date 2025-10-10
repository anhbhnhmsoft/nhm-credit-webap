<?php

namespace App\Services;

use App\Models\PageStatic;
use App\Utils\Constants\CommonStatus;

class PageStaticService
{
    public function getActiveBySlug(string $slug): PageStatic
    {
        return PageStatic::query()
            ->where('slug', $slug)
            ->where('status', CommonStatus::ACTIVE->value)
            ->firstOrFail();
    }

    public function listActiveOrdered(): \Illuminate\Support\Collection
    {
        return PageStatic::query()
            ->where('status', CommonStatus::ACTIVE->value)
            ->orderBy('title')
            ->get();
    }
}


