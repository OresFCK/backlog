@php
    $component = data_get($page ?? [], 'component');
    $props = data_get($page ?? [], 'props', []);
@endphp

@if ($component === 'blog/index')
    <main id="seo-fallback" class="seo-fallback">
        <header>
            <p>Curator.gg</p>
            <h1>Community Blog</h1>
            <p>Gaming news, reviews, guides and opinions written by the Curator.gg community.</p>
        </header>

        <nav aria-label="Blog categories">
            <a href="{{ route('blog.index') }}">All articles</a>
@foreach (data_get($props, 'categories', []) as $value => $label)
            <a href="{{ route('blog.index', ['category' => $value]) }}">{{ $label }}</a>
@endforeach
        </nav>

        <section aria-label="Latest articles">
@forelse (data_get($props, 'posts.data', []) as $post)
            <article>
                <h2><a href="{{ data_get($post, 'url') }}">{{ data_get($post, 'title') }}</a></h2>
                <p>{{ data_get($post, 'category_label') }} · {{ data_get($post, 'published_at') }}</p>
                <p>{{ data_get($post, 'excerpt') }}</p>
@if (data_get($post, 'cover_url'))
                <img src="{{ data_get($post, 'cover_url') }}" alt="{{ data_get($post, 'title') }}" loading="lazy">
@endif
            </article>
@empty
            <p>No published articles yet.</p>
@endforelse
        </section>
    </main>
@elseif ($component === 'blog/show')
    @php
        $post = data_get($props, 'post', []);
        $plainBody = preg_replace('/\[\[image:\d+\]\]/', ' ', (string) data_get($post, 'body', ''));
    @endphp
    <main id="seo-fallback" class="seo-fallback">
        <article>
            <p><a href="{{ route('blog.index') }}">Curator.gg Community Blog</a></p>
            <header>
                <h1>{{ data_get($post, 'title') }}</h1>
                <p>{{ data_get($post, 'category_label') }} · {{ data_get($post, 'published_at') }}</p>
                <p>By {{ data_get($post, 'author.name', 'Curator.gg player') }}</p>
@if (data_get($post, 'excerpt'))
                <p>{{ data_get($post, 'excerpt') }}</p>
@endif
            </header>

@foreach (data_get($post, 'images', []) as $image)
            <img src="{{ $image }}" alt="{{ data_get($post, 'title') }}" loading="lazy">
@endforeach

            <div>{!! nl2br(e(strip_tags($plainBody))) !!}</div>
        </article>
    </main>
@endif
