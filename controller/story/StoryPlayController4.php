<?php
session_start();

$sessionStorageJS = "";
if (isset($_SESSION['nextPageAfterUpload'])) {
  $sessionStorageJS .= "sessionStorage.setItem('currentPage', '" . $_SESSION['nextPageAfterUpload'] . "');";
  unset($_SESSION['nextPageAfterUpload']);
}
if (isset($_SESSION['chapterAfterUpload'])) {
  $sessionStorageJS .= "sessionStorage.setItem('currentChapter', '" . $_SESSION['chapterAfterUpload'] . "');";
  unset($_SESSION['chapterAfterUpload']);
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>
    <?php echo $sessionStorageJS; ?>
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Kiwi+Maru&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../css/story/play4.css">
  <title>Kiwi Sisters</title>
  <style>
    body {
      background: black;
      margin: 0;
      font-family: 'Kiwi Maru', sans-serif;
      background-size: cover;
      background-position: center;
    }
  </style>
</head>

<body>
  <div id="loading" class="loading">
    ロード中です... <span id="progress">0%</span>
  </div>
  <iframe id="bgm-frame" src="/controller/story/bgm.html" allow="autoplay" style="display:none;"></iframe>

  <div class="full">
    <div id="charImagesContainer" class="char-stand-container"></div>
    <img id="charImage" class="char-stand" src="" alt="" style="display: none;">
    <div id="choiceArea" class="choices" style="display: none;"></div>
    <div class="kuuhaku">a</div>
    <div class="comment">
      <div class="hako">
        <div class="name" id="charName"></div>
        <div class="text">
          <div id="textArea"></div>
          <button id="nextButton" class="next">></button>
        </div>
        <div class="menu">
          <a href="#" class="save" id="saveButton">セーブ</a>
          <a href="/controller/StartMenu.php" class="title">タイトル</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    let currentPage = parseInt(sessionStorage.getItem("currentPage") || "2");
    const charImageMap = {
      '白鷺_通常': '/img/shirasagi_standard.png',
      '白鷺_恐怖': '/img/shirasagi_scared.png',
      '白鷺_笑顔': '/img/shirasagi_smile.png',
      '白鷺_驚き': '/img/shirasagi_surprise.png',
      '白鷺_考察': '/img/shirasagi_thinking.png',
      '白鷺_怒る': '/img/shirasagi_ungry.png',
      '雉真_通常': '/img/kijima_chotosmile.png',
      '雉真_怒る': '/img/kijima_angry.png',
      '雉真_焦り': '/img/kijima_aseri.png',
      '雉真_真顔': '/img/kijima_nomal.png',
      '雉真_笑顔': '/img/kijima_smile.png',
      '雉真_考察': '/img/kijima_thinking.png',
      '鷹森_通常': '/img/takamori_nomal.png',
      '鷹森_驚き': '/img/takamori_bikkuri.png',
      '鷹森_江永ピンチ': '/img/takamori_enagapinch.png',
      '鷹森_戦闘': '/img/takamori_kamae.png',
      '鷹森_落胆': '/img/takamori_syonbori.png',
      '江永_通常': '/img/enaga_nomal.png',
      '江永_どや': '/img/enaga_doya.png',
      '江永_笑顔': '/img/enaga_smile.png',
      '江永_おこ': '/img/enaga_ungry.png',
      'テケ': '/img/teketeke.png',
      '先生_通常': '/img/sensei_normal.png',
      '先生_激怒': '/img/sensei_angry.png',
      '志乃_通常': '/img/sino_normal.png',
      '志乃_笑顔': '/img/sino_smile.png',
      '志乃_未知': '/img/sino_kowai.png',
      '花子_通常': '/img/hanakosan_nomal.png',
      '花子_笑顔': '/img/hanakosan_smile.png',
      'テケ': '/img/teketeke.png',
      'キーウィ・キウイ': '/img/kiwi.png',
      'べと': '/img/beto.png',
      'もつ': '/img/motu.png',
      '女子生徒A': '/img/A.png',
      '女子生徒B': '/img/B.png',
    };

    const seMap = {
      '歩行': 'hokou.mp3',
      '走る': 'hasiru.mp3',
      '攻撃': 'kougeki.mp3',
      'ツッコミ': 'tukkomi.mp3',
      'チリン': 'chirin.mp3',
      'パキッ': 'paki.mp3',
      '打撃鷹森': 'takamori_panchi.mp3',
      '打撃白鷺': 'takamori_panchi.mp3',
      '花子帰還': 'hanako_house.mp3',
      '倒れる': 'batan.mp3',
      'どどん': 'dodon.mp3',
      'ドアオープン': 'openDoor.mp3',
      'ドアしまーる': 'kagi.mp3',
      '発見効果音': 'hakken.mp3',
      'ドアガチャ': 'doagacya.mp3',
      'ひゅーん': 'hyu-n.mp3',
      'ひざ': 'hiza.mp3',
      'ガラス': 'garasu.mp3',
      '投げる': 'nageru.mp3',
      '学校': 'gakkou.mp3',
      'チャイム開始': 'chimeStart.mp3',
      'チャイム終わり': 'chimeEnd.mp3',
      '敵': 'teki.mp3',
      'かまえ': 'kamae.mp3',
      '解放': 'kaihou.mp3',
      '風': 'kaze.mp3',
      'きーん': 'ki-n.mp3',
      'きゅぴん': 'kyupin.mp3',
      'ごごご': 'gogogo.mp3',
      'かぎ壊し': 'kagi_broken.mp3',
      'ナイフ': 'naifu.mp3',
      'キック': 'kikku.mp3',
      'ちーん': 'chi-n.mp3',
      'どーん': 'do-n.mp3',
      // 必要に応じて追加黒背景
    };

    let isInitialLoad = true;
    let lastSentBgm = null;
    let lastSentPage = null;
    let allowEnterKey = true;
    let currentData = null;
    let shouldRetryPlay = sessionStorage.getItem("bgmPlayFailed") === "true";

    async function loadPage(page) {
      currentPage = page;
      sessionStorage.setItem("currentPage", String(currentPage));
      sessionStorage.setItem("currentChapter", sessionStorage.getItem("currentChapter") || "4");


      const res = await fetch(`/controller/getPageData.php?chapter=${sessionStorage.getItem("currentChapter") || 4}&page=${page}`);
      const data = await res.json();
      console.log("🎯 fetch結果 =", data);
      currentData = data;

      if (data.end === "true") {
        allowEnterKey = false;
        triggerTrueEndSequence();
        return;
      }
      const bgmFrame = document.getElementById("bgm-frame");
      const bgmWindow = bgmFrame?.contentWindow;

      let lastTime = 0;
      if (bgmWindow) {
        let effectiveBgm = (data.bgm || "").trim();

        // 現在再生中の BGM を sessionStorage に保存する
        if (effectiveBgm) {
          sessionStorage.setItem("currentBgm", effectiveBgm);
        } else {
          sessionStorage.removeItem("currentBgm"); // BGM が空なら消す
        }

        // BGM が変わらなければ送信しない
        const isSameBgm = effectiveBgm === lastSentBgm;

        if (!isSameBgm) {
          console.log(`🎶 BGM送信: ${effectiveBgm}, 前回送信: ${lastSentBgm}`);

          bgmWindow.postMessage(
            { type: "setBgm", bgm: effectiveBgm, currentTime: 0 },
            "*"
          );

          lastSentBgm = effectiveBgm;
          lastSentPage = page;
        } else {
          console.log(`⏭️ 同じBGMなので送信省略: ${effectiveBgm}`);
        }
      }



      document.body.classList.remove("character-special");

      const specialCharacters = [
        "先生_通常",
        "先生_激怒",
        "志乃_通常",
        "志乃_笑顔",
        "志乃_未知",
        "べと",
        "もつ",
      ];

      const hasSpecialCharacter = [data.illustration, data.illustration2, data.illustration3, data.illustration4, data.illustration5]
        .filter(Boolean)
        .some(illust => specialCharacters.includes(illust.trim()));

      if (hasSpecialCharacter) {
        document.body.classList.add("character-special");
      }




      const charNameEl = document.getElementById("charName");
      const textAreaEl = document.getElementById("textArea");

      charNameEl.innerText = data.character;
      textAreaEl.innerText = data.text;

      const bgMap = {
        '廊下': '../../img/rouka.png',
        'トイレ': '../../img/toire.png',
        '学校': '../../img/school.png',
        '階段': '../../img/kaidan.png',
        '音楽室': '../../img/ongakusitu.png',
        '美術室': '../../img/bijyutu.png',
        '理科室': '../../img/rika.png',
        '放送室': '../../img/hoso.png',
        '教室': '../../img/kyousitu.png',
        '回想': '../../img/kaisou.png',
        '更衣室': '../../img/kouisitu.png',
        '白背景': '../../img/siro.png',
        '黒背景': '../../img/kuro.png',
        '倒壊した校舎': '../../img/hokai.png',
      };
      const bg = bgMap[data.background] || '';
      document.body.style.backgroundImage = `url('${bg}'), linear-gradient(180deg, rgba(98,9,20,0.97) 77.49%, rgba(200,19,40,0.97) 100%)`;

      const charImagesContainer = document.getElementById("charImagesContainer");
      charImagesContainer.innerHTML = "";  // 前のキャラを削除

      const illustrations = [
        data.illustration,
        data.illustration2,
        data.illustration3,
        data.illustration4,
        data.illustration5,
      ].filter(Boolean).map(s => s.trim());

      charImagesContainer.innerHTML = "";

      if (illustrations.length === 1) {
        charImagesContainer.style.justifyContent = "center";
      } else if (illustrations.length > 1) {
        charImagesContainer.style.justifyContent = "space-around";
      } else {
        charImagesContainer.style.justifyContent = "center";  // fallback
      }

      illustrations.forEach((illust, index) => {
        let imageSrc = charImageMap[illust] || "";
        if (!imageSrc && illust) {
          const base = illust.split('_')[0].trim();
          imageSrc = charImageMap[`${base}_通常`] || Object.entries(charImageMap).find(([key]) => key.startsWith(base))?.[1];
        }

        if (imageSrc) {
          const img = document.createElement("img");
          img.src = imageSrc;
          img.alt = illust;
          img.className = `char-stand`;
          charImagesContainer.appendChild(img);
        }
      });

      const choiceArea = document.getElementById("choiceArea");
      choiceArea.innerHTML = "";
      const nextButton = document.getElementById("nextButton");

      if (data.next_state == 2) {
        allowEnterKey = false;
        nextButton.disabled = true;
        nextButton.style.display = "none";
        choiceArea.innerHTML = "";

        [data.choice1, data.choice2, data.choice3].forEach(choice => {
          if (choice && /(.+?)\((\d+)\)/.test(choice)) {
            const [, label, pageNum] = choice.match(/(.+?)\((\d+)\)/);
            const btn = document.createElement("button");
            btn.textContent = label;
            btn.className = "choice-button";
            setupChoiceButtonSE(btn);
            btn.onclick = () => loadPage(parseInt(pageNum, 10));
            choiceArea.appendChild(btn);
          }
        });
        choiceArea.style.display = "flex";
      }
      else if (data.next_state == 4) {
        allowEnterKey = false;
        nextButton.disabled = true;
        nextButton.style.display = "none";

        choiceArea.innerHTML = "";
        choiceArea.style.display = "block";
        choiceArea.className = "program";

        const correct = data.correctjumpTarget || "1";
        const incorrect = data.incorrectjumpTarget || "1";

        const downloadForm = document.createElement("form");
        downloadForm.action = "/controller/story/download4.php";
        downloadForm.method = "get";
        downloadForm.className = "file-download";

        const downloadButton = document.createElement("button");
        downloadButton.type = "submit";
        downloadButton.textContent = "ファイルをダウンロード";
        downloadForm.appendChild(downloadButton);

        const uploadForm = document.createElement("form");
        uploadForm.action = "/controller/story/upload4.php";
        uploadForm.method = "post";
        uploadForm.enctype = "multipart/form-data";
        uploadForm.className = "file-upload";

        const fileInput = document.createElement("input");
        fileInput.type = "file";
        fileInput.name = "uploaded_file";
        fileInput.accept = ".php";
        fileInput.required = true;

        const hiddenCorrect = document.createElement("input");
        hiddenCorrect.type = "hidden";
        hiddenCorrect.name = "correctjumpTarget";
        hiddenCorrect.value = correct;

        const hiddenIncorrect = document.createElement("input");
        hiddenIncorrect.type = "hidden";
        hiddenIncorrect.name = "incorrectjumpTarget";
        hiddenIncorrect.value = incorrect;

        const hiddenChapter = document.createElement("input");
        hiddenChapter.type = "hidden";
        hiddenChapter.name = "chapter";
        hiddenChapter.value = sessionStorage.getItem("currentChapter") || "4";

        const uploadButton = document.createElement("button");
        uploadButton.type = "submit";
        uploadButton.textContent = "ファイルをアップロード";

        uploadForm.appendChild(fileInput);
        uploadForm.appendChild(hiddenCorrect);
        uploadForm.appendChild(hiddenIncorrect);
        uploadForm.appendChild(hiddenChapter);
        uploadForm.appendChild(uploadButton);

        choiceArea.appendChild(downloadForm);
        choiceArea.appendChild(uploadForm);
      }



      else {
        allowEnterKey = true;
        choiceArea.style.display = "none";
        nextButton.disabled = false;
        nextButton.style.display = "inline-block";
      }

      // SE があれば一回だけ再生
      if (data.se && data.se.trim() !== "") {
        const seKey = data.se.trim();
        const seFile = seMap[seKey];
        if (seFile) {
          const seAudio = new Audio(`/se/${seFile}`);
          seAudio.play().catch(e => console.warn("SE 再生失敗:", e));
        } else {
          console.warn(`未登録のSE: ${seKey}`);
        }
      }
    }

    document.getElementById("saveButton").onclick = () => {
      console.log("[StoryPlayController4.php] セーブボタン押下: currentPage=", currentPage);
      sessionStorage.setItem("currentPage", currentPage);
      sessionStorage.setItem("currentChapter", sessionStorage.getItem("currentChapter") || "4");

      const chapter = sessionStorage.getItem("currentChapter") || "4";
      const page = sessionStorage.getItem("currentPage") || "2";

      window.location.href = `/controller/SaveSelect.php?page=${page}&chapter=${chapter}`;
    };

    const hoverSound = new Audio("/se/hover.mp3");
    const sentakuSound = new Audio("/se/sentaku.mp3");

    function setupChoiceButtonSE(button) {
      button.addEventListener("mouseenter", () => {
        hoverSound.currentTime = 0;
        hoverSound.play().catch((e) => console.warn("hover.mp3 再生失敗", e));
      });

      button.addEventListener("click", () => {
        sentakuSound.currentTime = 0;
        sentakuSound.play().catch((e) => console.warn("sentaku.mp3 再生失敗", e));
      });
    }

    function triggerTrueEndSequence() {
      allowEnterKey = false;

      const overlay = document.createElement("div");
      overlay.className = "end-roll-overlay";
      document.body.appendChild(overlay);

      const credits = [
        "飛べない鳥 END",
        "　　　",
        "制作：1班",
        "イラスト：岡田京香、緒方釉、中村ひなた",
        "シナリオ：岡田京香、緒方釉、中村ひなた",
        "プログラム・演出：長山千穂、養父里穏",
        "　　　",
        "プレイしてくれてありがとう",
      ];

      credits.forEach((line, i) => {
        const el = document.createElement("div");
        el.className = "end-roll-line";
        el.style.animationDelay = `${i * 1.5}s`;
        el.textContent = line;
        overlay.appendChild(el);
      });

      setTimeout(() => {
        window.location.href = "/controller/StartMenu.php";
      }, credits.length * 1500 + 2000);  // 全部表示後2秒待って戻る
    }


    document.getElementById("nextButton").onclick = handleNext;

    window.addEventListener("DOMContentLoaded", async () => {
      const loadingText = document.getElementById('loading');
      const progressText = document.getElementById('progress');

      function preloadAssets() {
        return fetch('/preload.php')
          .then(res => res.json())
          .then(data => {
            const total = data.images.length + data.sounds.length + data.scenarios.length;
            let loaded = 0;

            const updateProgress = () => {
              const percent = Math.floor((loaded / total) * 100);
              progressText.textContent = `${percent}%`;
            };

            const checkDone = () => {
              loaded++;
              updateProgress();
              if (loaded >= total) {
                loadingText.style.display = 'none';
              }
            };

            data.images.forEach(path => {
              const img = new Image();
              img.onload = checkDone;
              img.onerror = checkDone;
              img.src = path;
            });

            data.sounds.forEach(path => {
              const audio = new Audio();
              audio.oncanplaythrough = checkDone;
              audio.onerror = checkDone;
              audio.src = path;
              audio.preload = 'auto';
            });

            Object.values(data.scenarios).forEach(chapter => {
              Object.values(chapter).forEach(() => {
                checkDone();
              });
            });


            return new Promise(resolve => {
              const interval = setInterval(() => {
                if (loaded >= total) {
                  clearInterval(interval);
                  resolve();
                }
              }, 100);
            });
          });
      }

      sessionStorage.removeItem("bgmPlayFailed");
      console.log("DOMContentLoaded START");

      const chapter = sessionStorage.getItem("currentChapter");
      const page = sessionStorage.getItem("currentPage");
      const bg = sessionStorage.getItem("currentBackground");
      console.log("[StoryPlay] currentBackground from sessionStorage =", bg);

      if (bg) {
        const bgMap = { '廊下': '../../img/rouka.png', 'トイレ': '../../img/toire.png', '学校': '../../img/school.png', '階段': '../../img/kaidan.png' };
        const bgUrl = bgMap[bg] || '';
        document.body.style.backgroundImage = `url('${bgUrl}'), linear-gradient(180deg, rgba(98,9,20,0.97) 77.49%, rgba(200,19,40,0.97) 100%)`;
        console.log(`[StoryPlay] 初期背景適用: ${bg} → ${bgUrl}`);
      }


      if (!chapter) {
        alert("章の選択情報（currentChapter）がありません。章選択画面からやり直してください。");
        return;
      }

      let initialPage = parseInt(page, 10);
      if (isNaN(initialPage) || initialPage < 2) {
        initialPage = 2;
        sessionStorage.setItem("currentPage", "2");
      }

      await preloadAssets();
      currentPage = initialPage;
      await loadPage(currentPage);

      const bgmFrame = document.getElementById("bgm-frame");
      const bgmWindow = bgmFrame?.contentWindow;

      const currentBgm = sessionStorage.getItem("currentBgm");

      const navEntries = performance.getEntriesByType("navigation");
      const navType = navEntries[0]?.type || "navigate";
      console.log(`🔎 Navigation type: ${navType}`);

      if (navType === "reload" && bgmWindow && currentBgm) {
        console.log("🔔 本当にリロード時だけ BGM 復元:", currentBgm);
        bgmWindow.postMessage(
          { type: "setBgm", bgm: currentBgm, currentTime: 0 },
          "*"
        );
        shouldRetryPlay = true;
      }

      console.log("[StoryPlayController4.php] loadPage 完了 - page:", currentPage);



      if (bgmWindow && lastSentBgm) {
        console.log("🔔 iframe 初期化直後の BGM 再送:", lastSentBgm);
        bgmWindow.postMessage(
          { type: "setBgm", bgm: lastSentBgm, currentTime: 0 },
          "*"
        );
      }
    });

    function handleNext() {
      const bgmFrame = document.getElementById("bgm-frame");
      const bgmWindow = bgmFrame?.contentWindow;

      if (bgmWindow && shouldRetryPlay) {
        console.log("🔁 retryPlay 送信（リロード後）");
        bgmWindow.postMessage({ type: "retryPlay" }, "*");
        shouldRetryPlay = false;
        sessionStorage.removeItem("bgmPlayFailed");
      }

      if (currentData.next_state == 6) {  // ⭐️ new: next_state == 6 があれば TrueEnd にする
        triggerTrueEndSequence();
        return;
      }

      if (currentData.next_state == 0) {
        window.location.href = "/controller/StartMenu.php";
      } else if (currentData.next_state == 3 && currentData.jumpTarget && /^\d+$/.test(currentData.jumpTarget)) {
        const targetPage = parseInt(currentData.jumpTarget, 10);
        loadPage(targetPage);
      } else if (currentData.next_state == 5) {
        allowEnterKey = false;
        // ⭐️ 暗転処理
        const overlay = document.createElement("div");
        overlay.style.position = "fixed";
        overlay.style.top = "0";
        overlay.style.left = "0";
        overlay.style.width = "100%";
        overlay.style.height = "100%";
        overlay.style.backgroundColor = "black";
        overlay.style.opacity = "0";
        overlay.style.transition = "opacity 0.5s";
        overlay.style.zIndex = "999";
        document.body.appendChild(overlay);

        // 暗転開始
        requestAnimationFrame(() => {
          overlay.style.opacity = "1";
        });

        // 500ms後に次のページに進んで暗転解除
        setTimeout(async () => {
          await loadPage(currentPage + 1);

          overlay.style.opacity = "0";
          setTimeout(() => {
            document.body.removeChild(overlay);
            allowEnterKey = true;
          }, 500);
        }, 500);

      } else {
        loadPage(currentPage + 1);
      }
    }

    let lastEnterTime = 0;
    const enterDelay = 500;
    let enterPressed = false;

    document.addEventListener("keydown", e => {
      if (e.key === "Enter") {
        if (enterPressed) return; // 長押しで連続発火するのを防ぐ
        enterPressed = true;
      }
    });

    document.addEventListener("keyup", e => {
      if (e.key === "Enter") {
        enterPressed = false;

        if (currentData && currentData.next_state == 2) {
          console.log("🔒 Enter 無効化: next_state == 2");
          return;
        }

        if (allowEnterKey) {
          handleNext();
        }
      }
    });

  </script>

</body>

</html>