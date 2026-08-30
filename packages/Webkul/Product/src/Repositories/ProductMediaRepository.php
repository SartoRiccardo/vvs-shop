<?php

namespace Webkul\Product\Repositories;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Core\Eloquent\Repository;
use Webkul\Product\Contracts\Product;

class ProductMediaRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return string
     */
    public function model()
    {
        /**
         * This repository is extended to `ProductImageRepository` and `ProductVideoRepository`
         * repository.
         *
         * And currently no model is assigned to this repo.
         */
    }

    /**
     * Get product directory.
     *
     * @param  Product  $product
     */
    public function getProductDirectory($product): string
    {
        return 'product/'.$product->id;
    }

    /**
     * Upload.
     *
     * @param  array  $data
     * @param  Product  $product
     */
    public function upload($data, $product, string $uploadFileType): void
    {
        /**
         * Previous model ids for filtering.
         */
        $previousIds = $this->resolveFileTypeQueryBuilder($product, $uploadFileType)->pluck('id');

        $position = 0;

        /**
         * SEO-friendly file names: derive the base from the product name
         * instead of a random hash, keeping a short random suffix so names
         * stay unique (cache headers rely on it).
         */
        $baseName = Str::slug($product->name) ?: 'product';

        $newFileIndex = 0;

        $newAlts = array_values($data[$uploadFileType]['alt_new'] ?? []);

        if (! empty($data[$uploadFileType]['files'])) {
            foreach ($data[$uploadFileType]['files'] as $indexOrModelId => $file) {
                if ($file instanceof UploadedFile) {
                    if (Str::contains($file->getMimeType(), 'image')) {
                        $encoded = image_manager()->read($file)->encodeByExtension('webp');

                        $path = $this->getProductDirectory($product)
                            .'/'.implode('-', array_filter([
                                $baseName,
                                ++$newFileIndex,
                                Str::lower(Str::random(6)),
                            ])).'.webp';

                        Storage::put($path, (string) $encoded);
                    } else {
                        $path = $file->store($this->getProductDirectory($product));
                    }

                    $attributes = [
                        'type' => $uploadFileType,
                        'path' => $path,
                        'product_id' => $product->id,
                        'position' => ++$position,
                    ];

                    if (array_key_exists($newFileIndex - 1, $newAlts)) {
                        $attributes['alt'] = trim((string) $newAlts[$newFileIndex - 1]) ?: null;
                    }

                    $this->create($attributes);
                } else {
                    if (is_numeric($index = $previousIds->search($indexOrModelId))) {
                        $previousIds->forget($index);
                    }

                    $attributes = ['position' => ++$position];

                    if (array_key_exists($indexOrModelId, $data[$uploadFileType]['alt'] ?? [])) {
                        $attributes['alt'] = trim((string) $data[$uploadFileType]['alt'][$indexOrModelId]) ?: null;
                    }

                    $submittedFilename = trim((string) ($data[$uploadFileType]['filename'][$indexOrModelId] ?? ''));

                    if ($submittedFilename !== '') {
                        $model = $this->find($indexOrModelId);

                        if ($model) {
                            $newBase = Str::slug($submittedFilename);

                            $currentBase = pathinfo($model->path, PATHINFO_FILENAME);

                            /**
                             * Rename only when the submitted name actually
                             * differs, so routine saves never churn image URLs.
                             * A random suffix stays appended: unique names keep
                             * the immutable cache headers safe.
                             */
                            if ($newBase !== '' && $newBase !== $currentBase && Storage::exists($model->path)) {
                                $newPath = pathinfo($model->path, PATHINFO_DIRNAME)
                                    .'/'.$newBase.'-'.Str::lower(Str::random(6)).'.'.pathinfo($model->path, PATHINFO_EXTENSION);

                                if (! Storage::exists($newPath)) {
                                    Storage::move($model->path, $newPath);

                                    $attributes['path'] = $newPath;
                                }
                            }
                        }
                    }

                    $this->update($attributes, $indexOrModelId);
                }
            }
        }

        foreach ($previousIds as $indexOrModelId) {
            if (! $model = $this->find($indexOrModelId)) {
                continue;
            }

            Storage::delete($model->path);

            $this->delete($indexOrModelId);
        }
    }

    /**
     * Resolve file type query builder.
     *
     * @param  Product  $product
     * @return mixed
     *
     * @throws Exception
     */
    private function resolveFileTypeQueryBuilder($product, string $uploadFileType)
    {
        if ($uploadFileType === 'images') {
            return $product->images();
        } elseif ($uploadFileType === 'videos') {
            return $product->videos();
        }

        throw new Exception('Unsupported file type.');
    }
}
