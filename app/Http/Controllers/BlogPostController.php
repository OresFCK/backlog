<?php

namespace App\Http\Controllers;

use App\Helpers\PayloadHelper as Payload;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogPostController extends Controller
{
    private const CATEGORIES = [
        'news' => 'News',
        'reviews' => 'Reviews',
        'guides' => 'Guides',
        'opinion' => 'Opinion',
        'hardware' => 'Hardware',
        'industry' => 'Industry',
        'other' => 'Other',
    ];

    public function index(Request $request): Response
    {
        $category = $request->string('category')->toString();
        $search = Str::limit(trim($request->string('q')->toString()), 100, '');
        $escapedSearch = addcslashes($search, '%_\\');

        return Inertia::render('blog/index', [
            'posts' => BlogPost::query()
                ->where('is_published', true)
                ->when(
                    array_key_exists($category, self::CATEGORIES),
                    fn ($query) => $query->where('category', $category)
                )
                ->when(filled($search), fn ($query) => $query->where(
                    fn ($query) => $query
                        ->where('title', 'like', "%{$escapedSearch}%")
                        ->orWhere('excerpt', 'like', "%{$escapedSearch}%")
                        ->orWhere('body', 'like', "%{$escapedSearch}%")
                ))
                ->with('user')
                ->withSum('votes', 'value')
                ->latest('published_at')
                ->paginate(12)
                ->withQueryString()
                ->through(fn (BlogPost $post) => $this->summary($post)),
            'categories' => self::CATEGORIES,
            'activeCategory' => array_key_exists($category, self::CATEGORIES)
                ? $category
                : null,
            'search' => $search,
        ]);
    }

    public function mine(): Response
    {
        return Inertia::render('blog/mine', [
            'user' => Payload::currentUser(),
            'posts' => BlogPost::query()
                ->where('user_id', Auth::id())
                ->withSum('votes', 'value')
                ->latest()
                ->paginate(12)
                ->withQueryString()
                ->through(fn (BlogPost $post) => $this->summary($post)),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('blog/editor', [
            'user' => Payload::currentUser(),
            'post' => null,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $imagePaths = $this->storeImages($request);
        unset($data['images'], $data['remove_images'], $data['retained_images']);

        $post = BlogPost::query()->create([
            ...$data,
            'user_id' => Auth::id(),
            'slug' => $this->uniqueSlug($data['title']),
            'image_paths' => $imagePaths,
            'published_at' => $data['is_published'] ? now() : null,
        ]);

        return redirect()
            ->route('blog.mine')
            ->with('success', 'Post saved.');
    }

    public function show(BlogPost $post): Response
    {
        abort_unless(
            $post->is_published || $post->user_id === Auth::id(),
            404
        );

        $post->loadMissing(['user', 'votes']);
        $description = filled($post->excerpt)
            ? $post->excerpt
            : Str::limit($this->plainBody($post), 155);

        return Inertia::render('blog/show', [
            'post' => $this->payload($post),
            'categories' => self::CATEGORIES,
            'isOwner' => $post->user_id === Auth::id(),
            'seo' => [
                'title' => $post->title.' | Curator.gg Blog',
                'description' => $description,
                'url' => route('blog.show', $post),
                'image' => $this->imageUrls($post)[0]
                    ?? asset('og-image.jpg'),
            ],
        ]);
    }

    public function edit(BlogPost $post): Response
    {
        $this->authorizeOwner($post);

        return Inertia::render('blog/editor', [
            'user' => Payload::currentUser(),
            'post' => $this->payload($post),
            'categories' => self::CATEGORIES,
        ]);
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $this->authorizeOwner($post);
        $data = $this->validated($request, $post);
        $imagePaths = $post->image_paths ?? [];

        if ($request->boolean('remove_images') || $request->hasFile('images')) {
            $this->deleteImages($imagePaths);
            $imagePaths = $this->storeImages($request);
        } elseif (array_key_exists('retained_images', $data)) {
            $retained = array_map(fn ($index) => $imagePaths[$index], $data['retained_images'] ?? []);
            $this->deleteImages(array_values(array_diff($imagePaths, $retained)));
            $imagePaths = $retained;
        }

        unset($data['images'], $data['remove_images'], $data['retained_images']);

        $post->update([
            ...$data,
            'image_paths' => $imagePaths,
            'published_at' => $data['is_published']
                ? ($post->published_at ?? now())
                : null,
        ]);

        return redirect()
            ->route('blog.mine')
            ->with('success', 'Post updated.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $this->authorizeOwner($post);
        $this->deleteImages($post->image_paths ?? []);
        $post->delete();

        return redirect()
            ->route('blog.mine')
            ->with('success', 'Post deleted.');
    }

    private function validated(Request $request, ?BlogPost $post = null): array
    {
        $request->merge([
            'category' => $request->input('category', 'other'),
            'image_layout' => $request->input('image_layout', 'grid'),
        ]);

        return $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:160'],
            'excerpt' => ['nullable', 'string', 'max:320'],
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(self::CATEGORIES))],
            'body' => ['required', 'string', 'min:20', 'max:50000'],
            'youtube_url' => [
                'nullable',
                'url',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value && ! $this->youtubeEmbedUrl($value)) {
                        $fail('Enter a valid YouTube URL.');
                    }
                },
            ],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:5120',
            ],
            'remove_images' => ['nullable', 'boolean'],
            'retained_images' => ['sometimes', 'nullable', 'array', 'max:10'],
            'retained_images.*' => ['required', 'integer', 'distinct', \Illuminate\Validation\Rule::in(array_keys($post?->image_paths ?? []))],
            'image_layout' => ['required', 'in:grid,carousel,full'],
            'is_published' => ['required', 'boolean'],
        ]);
    }

    private function summary(BlogPost $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt
                ?: Str::limit($this->plainBody($post), 180),
            'category' => $post->category ?: 'other',
            'category_label' => self::CATEGORIES[$post->category ?: 'other'] ?? self::CATEGORIES['other'],
            'is_published' => $post->is_published,
            'published_at' => $post->published_at?->diffForHumans(),
            'updated_at' => $post->updated_at?->diffForHumans(),
            'score' => (int) ($post->votes_sum_value ?? 0),
            'cover_url' => $this->imageUrls($post)[0] ?? null,
            'url' => route('blog.show', $post),
            'author' => [
                'name' => $post->user?->visible_name,
                'avatar' => $post->user?->steam_avatar_url,
            ],
        ];
    }

    private function plainBody(BlogPost $post): string
    {
        return Str::squish(strip_tags(preg_replace('/\[\[image:\d+\]\]/', ' ', $post->body)));
    }

    private function payload(BlogPost $post): array
    {
        $userVote = Auth::check()
            ? $post->votes->firstWhere('user_id', Auth::id())?->value
            : null;

        return [
            ...$this->summary($post),
            'body' => $post->body,
            'images' => $this->imageUrls($post),
            'image_layout' => $post->image_layout ?: 'grid',
            'youtube_url' => $post->youtube_url,
            'youtube_embed_url' => $this->youtubeEmbedUrl($post->youtube_url),
            'score' => (int) $post->votes->sum('value'),
            'user_vote' => $userVote,
            'share_url' => route('blog.show', $post),
            'author' => [
                'name' => $post->user?->visible_name,
                'avatar' => $post->user?->steam_avatar_url,
                'profile_url' => filled($post->user?->steam_id)
                    ? route('profile.public', [
                        'user' => $post->user->steam_id,
                    ])
                    : null,
            ],
        ];
    }

    private function authorizeOwner(BlogPost $post): void
    {
        abort_unless($post->user_id === Auth::id(), 403);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'post';

        do {
            $slug = $base.'-'.Str::lower(Str::random(6));
        } while (BlogPost::query()->where('slug', $slug)->exists());

        return $slug;
    }

    private function storeImages(Request $request): array
    {
        return collect($request->file('images', []))
            ->map(fn ($image) => $image->store('blog', 'public'))
            ->values()
            ->all();
    }

    private function deleteImages(array $paths): void
    {
        Storage::disk('public')->delete($paths);
    }

    private function imageUrls(BlogPost $post): array
    {
        return collect($post->image_paths ?? [])
            ->map(fn (string $path) => asset(Storage::url($path)))
            ->values()
            ->all();
    }

    private function youtubeEmbedUrl(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        $parts = parse_url($url);
        $host = Str::lower($parts['host'] ?? '');
        $videoId = null;

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $videoId = trim($parts['path'] ?? '', '/');
        } elseif (
            in_array(
                $host,
                ['youtube.com', 'www.youtube.com', 'm.youtube.com'],
                true
            )
        ) {
            parse_str($parts['query'] ?? '', $query);
            $videoId = $query['v'] ?? null;

            if (! $videoId) {
                $segments = explode('/', trim($parts['path'] ?? '', '/'));
                if (in_array($segments[0] ?? '', ['embed', 'shorts'], true)) {
                    $videoId = $segments[1] ?? null;
                }
            }
        }

        if (! is_string($videoId)
            || preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId) !== 1) {
            return null;
        }

        return "https://www.youtube-nocookie.com/embed/{$videoId}";
    }
}
