<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>なんでも！ランキング！</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #f4ecff, #ffffff);
            color: #2f2540;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
            padding: 56px 20px;
            text-align: center;
        }

        .logo {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 12px;
            color: #6f45d9;
        }

        .subtitle {
            font-size: 18px;
            line-height: 1.8;
            color: #5f566e;
            margin-bottom: 36px;
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 24px;
            padding: 28px 22px;
            box-shadow: 0 12px 32px rgba(90, 60, 140, 0.14);
            margin-bottom: 28px;
        }

        .card h2 {
            margin-top: 0;
            font-size: 24px;
            color: #3a2c55;
        }

        .card p {
            line-height: 1.8;
            color: #625a70;
        }

        .links {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: 24px;
        }

        .link-button {
            display: block;
            padding: 15px 18px;
            border-radius: 999px;
            background: #7b4ef6;
            color: white;
            text-decoration: none;
            font-weight: 700;
        }

        .link-button.secondary {
            background: #ede6ff;
            color: #6f45d9;
        }

        footer {
            margin-top: 40px;
            font-size: 13px;
            color: #8a8296;
        }
    </style>
</head>

<body>
    <main class="container">
        <div class="logo">なんでも！ランキング！</div>

        <p class="subtitle">
            みんなの投票でランキングが決まる、参加型ランキングアプリです。<br>
            好きなテーマでランキングを作って、投票して、みんなで盛り上がろう！
        </p>

        <section class="card">
            <h2>公式サポートページ</h2>
            <p>
                「なんでも！ランキング！」に関する利用規約、プライバシーポリシー、
                お問い合わせ先などを確認できます。
            </p>

            <div class="links">
                <a class="link-button" href="/terms">利用規約</a>
                <a class="link-button secondary" href="/privacy">プライバシーポリシー</a>
            </div>
        </section>

        <footer>
            © 2026 Nandemo Ranking. All Rights Reserved.
        </footer>
    </main>
</body>

</html>