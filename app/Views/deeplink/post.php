<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($post->title) ?></title>

    <meta property="og:title" content="<?= esc($post->title) ?>">
    <meta property="og:description" content="<?= esc($post->summary) ?>">
    <meta property="og:image" content="<?= ($post->image_url) ?>">
    <meta property="og:url" content="https://onlinehunt.in/<?= $post->slug ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="Online Hunt">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($post->title) ?>">
    <meta name="twitter:description" content="<?= esc($post->summary) ?>">
    <meta name="twitter:image" content="<?= base_url($post->image_url) ?>">
    <style>
        body {
            font-family: system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
            background: #f9fafb;
            margin: 0;
            padding: 1rem;
        }

        button {
            margin-top: 1rem;
            background: #0d6efd;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
        }

        button:hover {
            background: #0b5ed7;
        }
    </style>
    <script>
        window.onload = () => {
            console.log('[DeepLink] Page loaded');

            const segments = window.location.pathname.split('/');
            // const postId = segments[segments.length - 1];
            // const schemeUrl = `onlinehunt://post/${postId}`;
            const postId = <?= (int)$post->id ?>;
            const desc = "<?=$post->summary ?>";

            const schemeUrl = `onlinehunt://post/${postId}`;
            console.log('[DeepLink] Extracted postId:', postId);
            console.log('[DeepLink] Scheme URL:', schemeUrl);
            console.log('[DeepLink] Scheme URL:', desc);

            // Try automatic deep link redirect
            try {
                window.location = schemeUrl;
            } catch (e) {
                console.error('[DeepLink] Automatic redirect failed:', e);
            }

            // Fallback: redirect to website if app doesn't open after 2 seconds
            setTimeout(() => {
                console.log('[DeepLink] App not opened, redirecting to fallback website');
                window.location.replace("https://onlinehunt.in/<?= $post->slug ?>");
                // window.location.replace("http://onlinehunt.in.local/<?= $post->slug ?>");
            }, 8000);

            // Hook up manual button fallback
            const btn = document.getElementById('openInAppBtn');
            btn.addEventListener('click', () => {
                console.log('[DeepLink] Manual button clicked');
                window.location = schemeUrl;
            });
        };
    </script>
</head>

<body>
    <h2>Opening Online Hunt…</h2>
    <p>If nothing happens, tap the button below to open the app.</p>
    <button id="openInAppBtn">Open in App</button>
</body>

</html>