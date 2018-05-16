<!DOCTYPE html>
<html>
  <head class="sg">
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/github.min.css" />
    <link rel="stylesheet" type="text/css" href="/assets/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="{{ $asset_path }}/styles/main.css">

    <title>Styleguide</title>
  </head>
  <body class="sg">

    <div class="sg-frame">
        <div class="sg-header">
            <a class="sg-header__link" href="/">Styleguide</a>
        </div>

        <div class="sg-frame__body">
            <div class="sg-sidebar">
                <nav>
                    <ul class="sg-nav">
                    @foreach($nav as $section => $val)
                        <li>
                            <span class="sg-nav__link">{{ $section }}</span>

                            <ul class="sg-nav">
                            @foreach($nav[$section] as $subsection => $val)
                            <li class="sg-nav__item">
                                <a class="sg-nav__link" href="/{{ $section }}/{{ $subsection }}">{{ $subsection }}</a>

                                @if($current == $subsection)
                                <ul class="sg-nav__varients">
                                @foreach($nav[$section][$subsection] as $index => $variant)
                                <li>
                                    <a class="sg-nav__link" href="/{{ $section }}/{{ $subsection }}#{{ $variant }}">{{ $variant }}</a>
                                </li>
                                @endforeach
                                </ul>
                                @endif
                            </li>
                            @endforeach
                            </ul>
                        </li>
                    @endforeach
                    </ul>
                </nav>
            </div>

            <div class="sg-frame__panel">
        <h1 class="sg-group__title">{{ $pageTitle }}</h1>
        @foreach ($page as $component)
            <h2 id="{{ $component['title'] }}" class="sg-component__title">{{ $component['title'] }}</h2>
            {!! $component['markup'] !!}

            <div class="sg-example">
            <pre><code class="html">{{ $component['markup'] }}</code></pre>
            </div>
            <hr>
        @endforeach
        </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
    <script type="text/javascript" src="{{ $asset_path }}/scripts/main.js"></script>
  </body>
</html>
