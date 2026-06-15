<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>World Archive</title>

    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #eef4ff, #ffffff);
            color: #243044;
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
            color: #3b82f6;
        }

        .subtitle {
            font-size: 18px;
            line-height: 1.8;
            color: #5a6575;
            margin-bottom: 36px;
        }

        .card {
            background: rgba(255, 255, 255, 0.92);
            border-radius: 24px;
            padding: 28px 22px;
            box-shadow: 0 12px 32px rgba(60, 90, 140, 0.12);
            margin-bottom: 28px;
        }

        .card h2 {
            margin-top: 0;
            font-size: 24px;
            color: #23324d;
        }

        .card p {
            line-height: 1.8;
            color: #5f6877;
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
            background: #3b82f6;
            color: white;
            text-decoration: none;
            font-weight: 700;
        }

        .link-button.secondary {
            background: #eaf2ff;
            color: #2563eb;
        }

        footer {
            margin-top: 40px;
            font-size: 13px;
            color: #8a94a3;
        }
    </style>
</head>

<body>
    <main class="container">

        <div class="logo">World Archive</div>

        <p class="subtitle">
            キャラクター、組織、アイテム、出来事、地図、相関図、時系列をひとつの世界にまとめて管理できる
            世界観資料作成アプリです。<br>
            小説、TRPG、ゲーム制作、創作設定の管理にご利用いただけます。
        </p>

        <section class="card">
            <h2>公式サポートページ</h2>

            <p>
                World Archive に関する利用規約、
                プライバシーポリシー、
                お問い合わせ先をご確認いただけます。
            </p>

            <div class="links">
                <a class="link-button" href="/worldArchive/terms">
                    利用規約
                </a>

                <a class="link-button secondary" href="/worldArchive/privacy">
                    プライバシーポリシー
                </a>
            </div>
        </section>

        <section class="card">
            <h2>World Archive について</h2>

            <p>
                World Archive は創作世界を整理するための資料管理アプリです。
                登場人物や国家、組織、アイテム、歴史、出来事を登録し、
                相関図や地図、時系列として視覚的に管理できます。
            </p>

            <p>
                作成したデータは端末内に保存され、
                外部サーバーへ送信されることはありません。
            </p>
        </section>

        <footer>
            © 2026 World Archive. All Rights Reserved.
        </footer>

    </main>
</body>

</html>