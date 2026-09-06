<?php

namespace Webkul\Admin\Http\Controllers\Media;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;

class MediaLibraryController extends Controller
{
    /**
     * The storage directory (on the public disk) where media lives.
     *
     * @var string
     */
    const DIRECTORY = 'media';

    /**
     * Allowed file extensions.
     *
     * @var array
     */
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg', 'ico', 'mp4', 'webm', 'mov', 'm4v'];

    /**
     * Extensions rendered as videos instead of images.
     *
     * @var array
     */
    const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'm4v'];

    /**
     * Display the media library.
     *
     * @return View
     */
    public function index()
    {
        $files = collect(Storage::disk('public')->files(self::DIRECTORY))
            ->filter(fn ($path) => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::ALLOWED_EXTENSIONS))
            ->map(function ($path) {
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                return [
                    'path' => $path,
                    'name' => basename($path),
                    'url' => Storage::disk('public')->url($path),
                    'size' => Storage::disk('public')->size($path),
                    'type' => in_array($extension, self::VIDEO_EXTENSIONS) ? 'video' : 'image',
                    'modified_at' => Storage::disk('public')->lastModified($path),
                ];
            })
            ->sortByDesc('modified_at')
            ->values();

        return view('admin::media.index', compact('files'));
    }

    /**
     * Upload files into the media library.
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|mimes:'.implode(',', self::ALLOWED_EXTENSIONS).'|max:51200',
        ]);

        foreach ($request->file('files', []) as $file) {
            /**
             * Descriptive, unique names: original name slug + random suffix.
             */
            $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                .'-'.Str::lower(Str::random(6))
                .'.'.strtolower($file->getClientOriginalExtension() ?: 'png');

            Storage::disk('public')->putFileAs(self::DIRECTORY, $file, $name);
        }

        session()->flash('success', trans('admin::app.media.upload-success'));

        return redirect()->route('admin.media.index');
    }

    /**
     * Delete a media file.
     *
     * @return RedirectResponse
     */
    public function destroy(string $name)
    {
        /**
         * basename() keeps deletions inside the media directory.
         */
        $path = self::DIRECTORY.'/'.basename($name);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);

            session()->flash('success', trans('admin::app.media.delete-success'));
        } else {
            session()->flash('error', trans('admin::app.media.delete-failed'));
        }

        return redirect()->route('admin.media.index');
    }
}
