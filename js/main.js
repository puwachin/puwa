


document.querySelectorAll('.chara').forEach(character => {
  // 初期表示で確実にフェードインするように
  character.classList.add('animated');

  character.addEventListener('animationend', (e) => {
    if (e.animationName === 'fadeInUp') {
      character.classList.add('animated');
    }
  });

  character.addEventListener('click', () => {
    character.classList.remove('jump');
    void character.offsetWidth;
    character.classList.add('jump');
  });
});


const style = document.createElement('style');
style.innerHTML = `
@keyframes jump {
  0% { transform: translateY(0); }
  30% { transform: translateY(-20px); }
  50% { transform: translateY(0); }
  100% { transform: translateY(0); }
}
.chara.jump {
  animation: jump 0.4s ease;
}`;


// ✅ ライトボックスを開く関数
function openSeries(images, title = "") {
  const viewer = document.getElementById("lightbox-viewer");
  const content = viewer.querySelector(".lightbox-content");

  content.innerHTML = "";

  images.forEach(src => {
    const img = document.createElement("img");
    img.src = src;
    img.alt = "comic image";
    content.appendChild(img);
  });

  viewer.classList.add("open");
}

// スピナー表示つきのロード処理
async function loadSection(folderId, sectionId) {
  const loaderId = sectionId.replace('-container', '-loader');
  const loader = document.getElementById(loaderId);
  loader.style.display = 'flex'; // 表示

  try {
    const response = await fetch(`https://script.google.com/macros/s/AKfycbzB8sPPLYMejKo_2NHNVrjVqONqHWmFsqOYooJ4xzaIMEgqTLskt12hMYBR-pwfpaYW/exec?id=${folderId}`);
    const data = await response.json();

    const section = document.getElementById(sectionId);
    section.innerHTML = "";

    data.folders.forEach(group => {
      const card = document.createElement("div");
      card.className = "comic-card";
      card.setAttribute("data-images", JSON.stringify(group.images));
      card.setAttribute("data-title", group.title || "");

      const caption = document.createElement("div");
      caption.className = "comic-caption";
      caption.textContent = group.title;
      card.appendChild(caption);

      const thumbnail = document.createElement("img");
      thumbnail.src = group.images[0];
      thumbnail.className = "thumbnail";
      card.appendChild(thumbnail);

      // ライトボックス開く
      card.addEventListener("click", () => {
        openSeries(group.images, group.title);
      });

      section.appendChild(card);
    });
  } catch (error) {
    console.error("読み込みエラー:", error);
  } finally {
    loader.style.display = 'none'; // 完了後に非表示
  }
}

// ライトボックス開く関数
function openSeries(images, title = "") {
  const viewer = document.getElementById("lightbox-viewer");
  const content = viewer.querySelector(".lightbox-content");

  content.innerHTML = "";

  images.forEach(src => {
    const img = document.createElement("img");
    img.src = src;
    img.alt = "comic image";
    content.appendChild(img);
  });

  viewer.classList.add("open");
}

// 画像クリックでライトボックス閉じる
document.addEventListener("DOMContentLoaded", () => {
  const viewer = document.getElementById("lightbox-viewer");
  const content = viewer.querySelector(".lightbox-content");

  content.addEventListener("click", (e) => {
    if (e.target.tagName === "IMG") {
      viewer.classList.remove("open");
    }
  });

  // セクションロード
  const rakugakiId = "1-1DLH8xvA1Mt6YZAGZluVpevqWmwjsJ0";
  const mangaId = "1-2_2G9hCjI65nr34Ys1KRoIcR47N7ln8";
  const etcId = "13k6aABhV2ooWFG6bQ_gnuwpFhCR6QHYO";

  loadSection(rakugakiId, "rakugaki-container");
  loadSection(mangaId, "manga-container");
  loadSection(etcId, "etc-container");
});
