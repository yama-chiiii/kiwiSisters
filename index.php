<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>kiwi-sisters-login</title>
    <link rel="stylesheet" href="css/sign.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kiwi+Maru&display=swap" rel="stylesheet">
</head>

<body>
    <div id="loading" class="loading">
        ダウンロード中です... <span id="progress">0%</span>
    </div>

    <div class="water-place">
        <img src="./img/water.png" class="water" />
    </div>
    <div class="login-box-place">
        <div class="login-box">
            <form method="POST" action="./controller/StartController.php" class="form-container">
                <h1 class="sign-in">Sign In</h1>
                <div class="user">
                    <label for="name" class="label">ID</label>
                    <input type="text" id="name" name="name" required class="form-input" />
                </div>
                <div class="user">
                    <label for="password" class="label">パスワード</label>
                    <input type="password" id="password" name="password" required class="form-input" />
                </div>
                <button type="submit" class="button-login">
                    ログイン
                </button>
                <button type="button" class="button-signup" onclick="location.href='./controller/SignUpController.php'">
                    新規登録
                </button>
            </form>
        </div>
        <div class="flower-place">
            <img src="./img/flower.png" class="flower" />
        </div>
    </div>
    <script>
        const loadingText = document.getElementById('loading')
        const progressText = document.getElementById('progress')

        fetch('./preload.php')
            .then(res => res.json())
            .then(data => {
                const total =
                    data.images.length +
                    data.sounds.length +
                    data.controllers.length +
                    Object.values(data.scenarios).reduce(
                        (sum, chapter) => sum + Object.values(chapter).length,
                        0
                    );

                let loaded = 0

                sessionStorage.setItem('storyData', JSON.stringify(data.scenarios))

                const updateProgress = () => {
                    const percent = Math.floor((loaded / total) * 100)
                    progressText.textContent = `${percent}%`
                }

                const checkDone = () => {
                    loaded++
                    updateProgress()
                    if (loaded >= total) {
                        loadingText.style.display = 'none'
                    }
                }

                data.images.forEach((path) => {
                    const img = new Image()
                    img.onload = checkDone
                    img.onerror = checkDone
                    img.src = path
                })

                data.sounds.forEach((path) => {
                    const audio = new Audio()
                    audio.oncanplaythrough = checkDone
                    audio.onerror = checkDone
                    audio.src = path
                    audio.preload = 'auto'
                })

                data.controllers.forEach((path) => {
                    fetch(path)
                        .then(res => res.text())
                        .then(() => checkDone())
                        .catch(() => checkDone())
                })

                Object.values(data.scenarios).forEach((chapter) => {
                    Object.values(chapter).forEach(() => checkDone())
                })



            })
            .catch(err => {
                console.error('プリロード失敗:', err)
                loadingText.textContent = '読み込みに失敗しました'
            })
    </script>
</body>

</html>