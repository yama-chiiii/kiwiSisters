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
  <link rel="stylesheet" href="../../css/story/play1.css">
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
  <iframe id="bgm-frame" src="/kiwiSisters/controller/story/bgm.html" allow="autoplay" style="display:none;"></iframe>

  <div class="full">
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
          <a href="/kiwiSisters/controller/StartMenu.php" class="title">タイトル</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    let currentPage = parseInt(sessionStorage.getItem("currentPage") || "2");
    const charImageMap = {
      '白鷺_通常': '/kiwiSisters/img/shirasagi_standard.png',
      '白鷺_恐怖': '/kiwiSisters/img/shirasagi_scared.png',
      '白鷺_笑顔': '/kiwiSisters/img/shirasagi_smile.png',
      '白鷺_驚き': '/kiwiSisters/img/shirasagi_surprise.png',
      '白鷺_考察': '/kiwiSisters/img/shirasagi_thinking.png',
      '白鷺_怒る': '/kiwiSisters/img/shirasagi_ungry.png',
      '雉真_通常': '/kiwiSisters/img/kijima_chotosmile.png',
      '雉真_怒る': '/kiwiSisters/img/kijima_angry.png',
      '雉真_焦り': '/kiwiSisters/img/kijima_aseri.png',
      '雉真_真顔': '/kiwiSisters/img/kijima_nomal.png',
      '雉真_笑顔': '/kiwiSisters/img/kijima_smile.png',
      '雉真_考察': '/kiwiSisters/img/kijima_thinking.png',
      '鷹森_通常': '/kiwiSisters/img/takamori_nomal.png',
      '鷹森_驚き': '/kiwiSisters/img/takamori_bikkuri.png',
      '鷹森_江永ピンチ': '/kiwiSisters/img/takamori_enagapinch.png',
      '鷹森_戦闘': '/kiwiSisters/img/takamori_kamae.png',
      '鷹森_落胆': '/kiwiSisters/img/takamori_syonbori.png',
      '江永': '/kiwiSisters/img/enaga_standard.png',
      '花子': '/kiwiSisters/img/hanakosan_smile.png',
      'キーウィ・キウイ': '/kiwiSisters/img/kiwi.png',
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
      sessionStorage.setItem("currentChapter", sessionStorage.getItem("currentChapter") || "1");

      // if (!isInitialLoad) {
      //   sessionStorage.setItem("currentPage", page);
      // }
      // isInitialLoad = false;

      const res = await fetch(`/kiwiSisters/controller/getPageData.php?chapter=${sessionStorage.getItem("currentChapter") || 1}&page=${page}`);
      const data = await res.json();
      console.log("🎯 fetch結果 =", data);
      currentData = data;

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


      const charNameEl = document.getElementById("charName");
      const textAreaEl = document.getElementById("textArea");

      charNameEl.innerText = data.character;
      textAreaEl.innerText = data.text;

      const bgMap = { '廊下': '../../img/rouka.png', 'トイレ': '../../img/toire.png', '学校': '../../img/school.png' };
      const bg = bgMap[data.background] || '';
      document.body.style.backgroundImage = `url('${bg}'), linear-gradient(180deg, rgba(98,9,20,0.97) 77.49%, rgba(200,19,40,0.97) 100%)`;

      const charImg = document.getElementById("charImage");
      let imageSrc = charImageMap[data.illustration?.trim()] || "";
      if (!imageSrc && data.illustration) {
        const base = data.illustration.split('_')[0].trim();
        imageSrc = charImageMap[`${base}_通常`] || Object.entries(charImageMap).find(([key]) => key.startsWith(base))?.[1];
      }
      charImg.style.display = imageSrc ? "block" : "none";
      charImg.src = imageSrc || "";
      charImg.alt = data.illustration || "";

      const choiceArea = document.getElementById("choiceArea");
      choiceArea.innerHTML = "";
      const nextButton = document.getElementById("nextButton");

      if (data.next_state == 2) {
        allowEnterKey = false;
        nextButton.disabled = true;
        nextButton.style.display = "none";
        choiceArea.innerHTML = "";

        [data.choice1, data.choice2, data.jumpTarget].forEach(choice => {
          if (choice && /(.+?)\((\d+)\)/.test(choice)) {
            const [, label, pageNum] = choice.match(/(.+?)\((\d+)\)/);
            const btn = document.createElement("button");
            btn.textContent = label;
            btn.className = "choice-button";
            btn.onclick = () => loadPage(parseInt(pageNum, 10));
            choiceArea.appendChild(btn);
          }
        });
        choiceArea.style.display = "flex";
      } else if (data.next_state == 4) {
        allowEnterKey = false;
        nextButton.disabled = true;
        nextButton.style.display = "none";

        choiceArea.innerHTML = "";
        choiceArea.style.display = "block";
        choiceArea.className = "program";

        const correct = data.correctjumpTarget || "1";
        const incorrect = data.incorrectjumpTarget || "1";

        const downloadForm = document.createElement("form");
        downloadForm.action = "/kiwiSisters/controller/story/download1.php";
        downloadForm.method = "get";
        downloadForm.className = "file-download";

        const downloadButton = document.createElement("button");
        downloadButton.type = "submit";
        downloadButton.textContent = "ファイルをダウンロード";
        downloadForm.appendChild(downloadButton);

        const uploadForm = document.createElement("form");
        uploadForm.action = "/kiwiSisters/controller/story/upload1.php";
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

        // ⭐️ ここに hidden chapter input を追加
        const hiddenChapter = document.createElement("input");
        hiddenChapter.type = "hidden";
        hiddenChapter.name = "chapter";
        hiddenChapter.value = sessionStorage.getItem("currentChapter") || "1";

        const uploadButton = document.createElement("button");
        uploadButton.type = "submit";
        uploadButton.textContent = "ファイルをアップロード";

        uploadForm.appendChild(fileInput);
        uploadForm.appendChild(hiddenCorrect);
        uploadForm.appendChild(hiddenIncorrect);
        uploadForm.appendChild(hiddenChapter);  // ⭐️ ここ！
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
    }

    document.getElementById("saveButton").onclick = () => {
      console.log("[StoryPlayController1.php] セーブボタン押下: currentPage=", currentPage);
      sessionStorage.setItem("currentPage", currentPage);
      sessionStorage.setItem("currentChapter", sessionStorage.getItem("currentChapter") || "1");
      window.location.href = "/kiwiSisters/controller/SaveSelect.php";
    };


    document.getElementById("nextButton").onclick = handleNext;

    window.addEventListener("DOMContentLoaded", async () => {
      sessionStorage.removeItem("bgmPlayFailed");
      console.log("🌟 DOMContentLoaded START");

      const chapter = sessionStorage.getItem("currentChapter");
      const page = sessionStorage.getItem("currentPage");

      if (!chapter) {
        alert("章の選択情報（currentChapter）がありません。章選択画面からやり直してください。");
        return;
      }

      let initialPage = parseInt(page, 10);
      if (isNaN(initialPage) || initialPage < 2) {
        initialPage = 2;
        sessionStorage.setItem("currentPage", "2");
      }

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

      console.log("[StoryPlayController1.php] loadPage 完了 - page:", currentPage);



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

      if (currentData.next_state == 0) {
        window.location.href = "/kiwiSisters/controller/StartMenu.php";
      } else if (currentData.next_state == 3 && currentData.jumpTarget && /^\d+$/.test(currentData.jumpTarget)) {
        const targetPage = parseInt(currentData.jumpTarget, 10);
        loadPage(targetPage);
      } else {
        loadPage(currentPage + 1);
      }
    }



    document.addEventListener("keydown", e => {
      if (e.key === "Enter") {
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